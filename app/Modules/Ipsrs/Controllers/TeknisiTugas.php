<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\TeknisiModel;
use Exception; // Pastikan ini ada
use Illuminate\Http\Request;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;
use App\Modules\Master\Models\AssetModel;


class TeknisiTugas extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TeknisiModel();
        $this->template = 'ipsrs::teknisi.tugas.';
    }

    public function index()
    {
        $teknisi_id = session('pegawai_id');
        $d['list_tugas_baru'] = $this->model->getListTugasByStatus($teknisi_id, 'ditugaskan');
        $d['list_tugas_dikerjakan'] = $this->model->getListTugasByStatus($teknisi_id, 'sedang_dikerjakan');
        $d['list_tugas_ditolak'] = $this->model->getListTugasByStatus($teknisi_id, 'dibatalkan');
        $d['list_tugas_selesai'] = $this->model->getListTugasByStatus($teknisi_id, 'selesai');
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_detail_modal($penugasan_id)
    {
        $d['tugas'] = $this->model->getDetailTugas($penugasan_id);
        if (!$d['tugas']) {
            return '<h5>Tugas tidak ditemukan.</h5>';
        }
        return $this->renderView($this->template . 'detail_modal', $d);
    }

    public function terima()
    {
        try {
            $penugasan_id = request('penugasan_id');
            if (!$penugasan_id) throw new Exception("ID Penugasan tidak ditemukan.");

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'sedang_dikerjakan');
            if ($result['success']) {
                return response()->json(['code' => '02', 'message' => 'Tugas berhasil diterima.', 'redirect_url' => url('ipsrs/teknisitugas')]);
            }
            return response()->json(['code' => '12', 'message' => $result['msg']]);
        } catch (Exception $e) {
            return response()->json(['code' => '12', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function batal_terima()
    {
        try {
            $penugasan_id = request('penugasan_id');
            if (!$penugasan_id) throw new Exception("ID Penugasan tidak ditemukan.");

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'ditugaskan');
            if ($result['success']) {
                return response()->json(['code' => '02', 'message' => 'Penerimaan tugas dibatalkan.', 'redirect_url' => url('ipsrs/teknisitugas')]);
            }
            return response()->json(['code' => '12', 'message' => $result['msg']]);
        } catch (Exception $e) {
            return response()->json(['code' => '12', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function form_tolak_modal($penugasan_id)
    {
        $d['penugasan_id'] = $penugasan_id;
        $d['form_act'] = url('ipsrs/teknisitugas/tolak');
        return $this->renderView($this->template . 'form_tolak_modal', $d);
    }

    public function tolak()
    {
        try {
            $penugasan_id = request('penugasan_id');
            $alasan = request('alasan');
            if (!$penugasan_id || !$alasan) throw new Exception("ID Penugasan atau alasan tidak boleh kosong.");

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'dibatalkan', $alasan);
            if ($result['success']) {
                return response()->json(['code' => '02', 'message' => 'Tugas berhasil ditolak.', 'redirect_url' => url('ipsrs/teknisitugas')]);
            }
            return response()->json(['code' => '12', 'message' => $result['msg']]);
        } catch (Exception $e) {
            return response()->json(['code' => '12', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // GANTI DENGAN VERSI BARU INI
    public function form_scan_modal(Request $request, $order_kerja_id = null)
    {
        if (!$order_kerja_id) {
            return '<h5>Error: Konteks Order Kerja tidak ditemukan. Harap coba lagi.</h5>';
        }
        $d['order_kerja_id'] = $order_kerja_id;

        // AMBIL PARAMETER 'n' DARI URL DAN KIRIM KE VIEW
        $d['n_param'] = $request->input('n');

        return $this->renderView($this->template . 'form_scan_modal', $d);
    }


    public function form_log_kerja_modal($order_kerja_id = null)
    {
        if (!$order_kerja_id) return '<h5>Error: Order Kerja ID tidak valid.</h5>';

        $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
        if (!$order_kerja) return '<h5>Error: Data Order Kerja tidak ditemukan.</h5>';

        $d['order_kerja'] = $order_kerja;

        // Ambil asset_id untuk keperluan redirect
        $asset_id = null;
        if (!empty($order_kerja['permintaan_id'])) {
            $sumber = DbModel::getData('permintaan_komplain', ['permintaan_id' => $order_kerja['permintaan_id']]);
            $asset_id = $sumber['asset_id'] ?? null;
        } else if (!empty($order_kerja['jadwal_pm_id'])) {
            $sumber = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $order_kerja['jadwal_pm_id']]);
            $asset_id = $sumber['asset_id'] ?? null;
        }
        $d['asset_id'] = $asset_id;

        $d['log_kerja'] = DbModel::getData('log_kerja', ['order_kerja_id' => $order_kerja_id, 'deleted_st' => 0]);
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);

        // Arahkan form action ke metode save_log_kerja di controller ini
        $d['form_act'] = url('ipsrs/teknisitugas/save_log_kerja/' . $order_kerja_id);

        // Render view modal baru yang sudah kita buat di folder teknisi
        return $this->renderView($this->template . 'form_log_kerja_modal', $d);
    }


    /**
     * LANGKAH 2.B: TAMBAHKAN METODE UNTUK MENYIMPAN LOG KERJA
     * Metode ini mengambil logika penyimpanan dari AdminOrderKerja.php
     */
    public function save_log_kerja(Request $request, $order_kerja_id = null)
    {
        if (!$order_kerja_id) {
            return response()->json([
                'status' => false,
                'message' => 'Error: Order Kerja ID tidak ditemukan.'
            ]);
        }

        $d = $request->all();
        $logKerjaModel = new LogKerjaModel();

        // Panggil logika simpan dari model
        $result = $logKerjaModel->saveData($order_kerja_id, $d);

        if ($result['status']) {
            // TAMBAHKAN KODE INI: Update status penugasan menjadi 'selesai'
            try {
                // Dapatkan penugasan_id dari order_kerja
                $order = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
                if ($order) {
                    $penugasan = DbModel::getData('penugasan_teknisi', [
                        'order_kerja_id' => $order_kerja_id,
                        'status' => 'sedang_dikerjakan'
                    ]);
                    
                    if ($penugasan) {
                        $this->model->updateStatusPenugasan($penugasan['penugasan_id'], 'selesai');
                    }
                }
            } catch (Exception $e) {
                // Log error tapi tetap lanjutkan proses
                \Log::error('Error saat update status penugasan: ' . $e->getMessage());
            }

            // Jika BERHASIL, siapkan URL redirect
            $asset_id = $request->input('asset_id');
            $redirectUrl = url('master/asset/detail/' . $asset_id . '?n=' . $request->input('n'));

            // Simpan pesan sukses ke dalam session (flash message)
            session()->flash('flash_success', 'Laporan kerja berhasil disimpan dan tugas telah diselesaikan!');

            // Kembalikan respons JSON dengan URL redirect
            return response()->json([
                'status' => true,
                'message' => 'Laporan kerja berhasil disimpan dan tugas telah diselesaikan!',
                'redirect_url' => $redirectUrl
            ]);
        } else {
            // Jika GAGAL, kembalikan respons JSON dengan pesan error
            return response()->json([
                'status' => false,
                'message' => $result['message'] ?? 'Terjadi kesalahan saat menyimpan data.'
            ]);
        }
    }
}
