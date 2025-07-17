<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PermintaanKomplainModel;
use Illuminate\Support\Facades\Log;

class AdminPermintaanKomplain extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PermintaanKomplainModel();
        $this->template = 'ipsrs::admin.pekerjaan.permintaan_komplain.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));

        // Data untuk dropdown filter di halaman utama
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = $id ? $this->model->getById($id) : null;

        // Mengirim semua data yang dibutuhkan untuk logika JavaScript di sisi klien
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);

        $d['form_act'] = $id ? url('ipsrs/adminpermintaankomplain/save/' . $id) : url('ipsrs/adminpermintaankomplain/save');
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // Validasi dasar yang berlaku untuk semua
        if (empty($d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Aset wajib dipilih!']));
        }
        if (empty($d['deskripsi'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Deskripsi wajib diisi.']));
        }

        try {
            \DB::beginTransaction();

            // PERBAIKAN: Hapus lokasi_id dari data yang akan disimpan
            // karena tabel permintaan_komplain tidak memiliki kolom ini
            if (isset($d['lokasi_id'])) {
                unset($d['lokasi_id']);
            }

            if ($id == null) { // Mode INSERT (Laporan baru dari Pelapor atau Admin)
                // Siapkan data yang diisi otomatis oleh sistem
                $d['permintaan_id'] = DbModel::getId('permintaan_komplain', 2, 12);
                $d['pegawai_id'] = $d['pegawai_id'] ?? session('pegawai_id');
                $d['tgl'] = date('Y-m-d');
                $d['status'] = 'baru'; // Setiap laporan baru statusnya PASTI "baru"

                $result = DbModel::insertData('permintaan_komplain', $d);
                $response_code = '01';
            } else { // Mode UPDATE (Hanya bisa dilakukan oleh Admin)
                // Validasi tambahan khusus untuk admin
                if (empty($d['status'])) {
                    return response()->json(_response('11', $this->uri, ['message' => 'Status Komplain wajib dipilih saat mengedit.']));
                }
                if (empty($d['pegawai_id'])) {
                    return response()->json(_response('11', $this->uri, ['message' => 'Pembuat Komplain wajib dipilih saat mengedit.']));
                }

                $result = DbModel::updateData('permintaan_komplain', $d, ['permintaan_id' => $id]);
                $response_code = '02';
            }

            if (!$result) {
                throw new \Exception("Gagal menyimpan data ke database.");
            }

            \DB::commit();
            return response()->json(_response($response_code, $this->uri, $d));
        } catch (\Throwable $th) {
            \DB::rollBack();
            Log::error('Error saving komplain: ' . $th->getMessage());
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan laporan.']));
        }
    }

    public function delete($id)
    {
        $result = $this->model->deleteData($id);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return PermintaanKomplainModel::loadDatatables();
    }
}
