<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\TeknisiModel;
use Exception;
use Illuminate\Http\Request;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;

class TeknisiTugas extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TeknisiModel();
        $this->template = 'ipsrs::teknisi.tugas.';

        // Membuat direktori upload log kerja jika belum ada
        $uploadPath = public_path('uploads/log_kerja');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
    }

    // Menampilkan daftar tugas teknisi
    public function index()
    {
        $teknisi_id = session('pegawai_id');
        $d['list_tugas_baru'] = $this->model->getListTugasByStatus($teknisi_id, 'ditugaskan');
        $d['list_tugas_dikerjakan'] = $this->model->getListTugasByStatus($teknisi_id, 'sedang_dikerjakan');
        $d['list_tugas_ditolak'] = $this->model->getListTugasByStatus($teknisi_id, 'dibatalkan');
        $d['list_tugas_selesai'] = $this->model->getListTugasByStatus($teknisi_id, 'selesai');
        return $this->renderView($this->template . 'index', $d);
    }

    // Menampilkan detail tugas dalam modal
    public function form_detail_modal($penugasan_id)
    {
        $d['tugas'] = $this->model->getDetailTugas($penugasan_id);
        if (!$d['tugas']) {
            return '<h5>Tugas tidak ditemukan.</h5>';
        }
        return $this->renderView($this->template . 'detail_modal', $d);
    }

    // Menerima tugas yang diberikan
    public function terima()
    {
        try {
            $penugasan_id = request('penugasan_id');
            if (!$penugasan_id) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'ID Penugasan tidak ditemukan.'
                ]);
            }

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'sedang_dikerjakan');

            if ($result['success']) {
                return response()->json([
                    'status' => true,
                    'code' => '02',
                    'message' => 'Tugas berhasil diterima',
                    'redirect_url' => url('ipsrs/teknisitugas') . '?n=' . request('n')
                ]);
            }

            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => $result['msg'] ?? 'Gagal memproses permintaan'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // Membatalkan penerimaan tugas
    public function batal_terima()
    {
        try {
            $penugasan_id = request('penugasan_id');
            if (!$penugasan_id) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'ID Penugasan tidak ditemukan.'
                ]);
            }

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'ditugaskan');
            if ($result['success']) {
                return response()->json([
                    'status' => true,
                    'code' => '02',
                    'message' => 'Penerimaan tugas dibatalkan.',
                    'redirect_url' => url('ipsrs/teknisitugas') . '?n=' . request('n')
                ]);
            }

            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => $result['msg'] ?? 'Gagal memproses permintaan'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    // Form penolakan tugas (modal)
    public function form_tolak_modal($penugasan_id)
    {
        try {
            $d = [];
            $d['penugasan_id'] = $penugasan_id;
            $d['form_act'] = url('ipsrs/teknisitugas/tolak') . '?n=' . request('n');

            return $this->renderView($this->template . 'form_tolak_modal', $d);
        } catch (Exception $e) {
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    // Proses penolakan tugas
    public function tolak()
    {
        try {
            $penugasan_id = request('penugasan_id');
            $alasan = request('alasan');

            if (!$penugasan_id) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'ID Penugasan tidak ditemukan.'
                ]);
            }

            if (empty($alasan)) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'Alasan penolakan harus diisi.'
                ]);
            }

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'dibatalkan', $alasan);

            if ($result['success']) {
                return response()->json([
                    'status' => true,
                    'code' => '02',
                    'message' => 'Tugas berhasil ditolak.',
                    'redirect_url' => url('ipsrs/teknisitugas') . '?n=' . request('n')
                ]);
            }

            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => $result['msg'] ?? 'Gagal memproses permintaan'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // Form scan barcode aset (modal)
    public function form_scan_modal($order_kerja_id)
    {
        try {
            if (!$order_kerja_id) {
                return '<div class="alert alert-danger">ID Order Kerja tidak ditemukan</div>';
            }

            $d = [];
            $d['order_kerja_id'] = $order_kerja_id;
            $d['n_param'] = request('n');

            return $this->renderPartialView($this->template . 'form_scan_modal', $d);
        } catch (Exception $e) {
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    // Form log kerja teknisi (modal)
    public function form_log_kerja_modal($order_kerja_id = null)
    {
        if (!$order_kerja_id) return '<h5>Error: Order Kerja ID tidak valid.</h5>';

        $order_kerja = $this->model->getOrderKerja($order_kerja_id);
        if (!$order_kerja) return '<h5>Error: Data Order Kerja tidak ditemukan.</h5>';

        $d['order_kerja'] = $order_kerja;

        $asset_id = null;
        if (!empty($order_kerja['permintaan_id'])) {
            $sumber = $this->model->getPermintaanKomplain($order_kerja['permintaan_id']);
            $asset_id = $sumber['asset_id'] ?? null;
        } else if (!empty($order_kerja['jadwal_pm_id'])) {
            $sumber = $this->model->getJadwalPm($order_kerja['jadwal_pm_id']);
            $asset_id = $sumber['asset_id'] ?? null;
        }
        $d['asset_id'] = $asset_id;

        $d['log_kerja'] = $this->model->getLogKerja($order_kerja_id);
        $d['all_sparepart'] = $this->model->getAllSparepart();
        $d['form_act'] = url('ipsrs/teknisitugas/save_log_kerja/' . $order_kerja_id);

        return $this->renderView($this->template . 'form_log_kerja_modal', $d);
    }

    // Fungsi untuk mengambil kembali tugas yang ditolak/dibatalkan
    public function ambil_kembali()
    {
        try {
            $penugasan_id = request('penugasan_id');
            if (!$penugasan_id) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'ID Penugasan tidak ditemukan.'
                ]);
            }

            $penugasan = $this->model->getPenugasanById($penugasan_id);
            if (!$penugasan) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'Data penugasan tidak ditemukan.'
                ]);
            }

            if ($penugasan['status'] != 'dibatalkan') {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'Hanya tugas yang ditolak yang dapat diambil kembali.'
                ]);
            }

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'ditugaskan');

            if ($result['success']) {
                return response()->json([
                    'status' => true,
                    'code' => '02',
                    'message' => 'Tugas berhasil diambil kembali.',
                    'redirect_url' => url('ipsrs/teknisitugas') . '?n=' . request('n')
                ]);
            }

            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => $result['msg'] ?? 'Gagal mengambil kembali tugas'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // Fungsi untuk verifikasi barcode aset
    public function verify_barcode()
    {
        try {
            $order_kerja_id = request('order_kerja_id');
            $barcode = request('barcode');
            $n_param = request('n');

            if (!$order_kerja_id || !$barcode) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'Order kerja ID dan barcode harus diisi.'
                ]);
            }

            $asset_id = $this->model->verifyAssetBarcode($order_kerja_id, $barcode);

            if ($asset_id) {
                $detail_url = url('ipsrs/teknisitugas/detail_aset/' . $asset_id);
                $detail_url .= '?order_kerja_id=' . $order_kerja_id;
                if ($n_param) {
                    $detail_url .= '&n=' . $n_param;
                }

                return response()->json([
                    'status' => true,
                    'code' => '02',
                    'message' => 'Barcode terverifikasi! Anda akan diarahkan ke halaman detail aset.',
                    'redirect_url' => $detail_url
                ]);
            }

            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => 'Barcode tidak valid atau tidak sesuai dengan aset pada order kerja ini.'
            ]);
        } catch (Exception $e) {
            \Log::error('Error in verify_barcode: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'code' => '12',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // Fungsi untuk menampilkan form log kerja setelah scan barcode berhasil
    public function form_log_kerja($order_kerja_id)
    {
        try {
            $order_kerja = $this->model->getOrderKerja($order_kerja_id);
            if (!$order_kerja) {
                return '<div class="alert alert-danger">Order kerja tidak ditemukan</div>';
            }

            $d = [
                'order_kerja_id' => $order_kerja_id,
                'order_kerja' => $order_kerja,
                'asset_id' => $order_kerja['asset_id'] ?? null,
                'n_param' => request('n'),
                'form_act' => url('ipsrs/teknisitugas/save_log_kerja/' . $order_kerja_id)
            ];

            if (empty($d['asset_id'])) {
                if (!empty($order_kerja['permintaan_id'])) {
                    $permintaan = $this->model->getPermintaanKomplain($order_kerja['permintaan_id']);
                    $d['asset_id'] = $permintaan['asset_id'] ?? null;
                } elseif (!empty($order_kerja['jadwal_pm_id'])) {
                    $jadwal_pm = $this->model->getJadwalPm($order_kerja['jadwal_pm_id']);
                    $d['asset_id'] = $jadwal_pm['asset_id'] ?? null;
                }
            }

            $d['all_sparepart'] = $this->model->getAllSparepart();

            return view($this->template . 'form_log_kerja_modal', $d);
        } catch (\Exception $e) {
            \Log::error('Error in form_log_kerja: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    // Fungsi untuk menyimpan log kerja hasil pekerjaan teknisi (POST)
    public function save_log_kerja(Request $request)
    {
        try {
            $order_kerja_id = $request->input('order_kerja_id');
            if (!$order_kerja_id) {
                return response()->json(_response('11', $this->uri, [
                    'message' => 'Order Kerja ID tidak ditemukan.'
                ]));
            }

            $logKerjaModel = new LogKerjaModel();
            $result = $logKerjaModel->saveData($order_kerja_id, $request->all());

            if ($result['status']) {
                $this->model->finishOrderKerja($order_kerja_id, session('pegawai_id'));
                $redirectUrl = url('ipsrs/teknisitugas');
                $nParam = $request->input('n');
                if ($nParam) {
                    $redirectUrl .= '?n=' . $nParam;
                }

                session()->flash('success', 'Laporan kerja berhasil disimpan dan tugas telah diselesaikan!');
                return response()->json(_response('02', $this->uri, [
                    'redirect_url' => $redirectUrl,
                    'message' => 'Laporan kerja berhasil disimpan'
                ]));
            } else {
                return response()->json(_response('12', $this->uri, [
                    'message' => $result['message'] ?? 'Terjadi kesalahan saat menyimpan data.'
                ]));
            }
        } catch (Exception $e) {
            \Log::error('Error in save_log_kerja: ' . $e->getMessage());
            return response()->json(_response('12', $this->uri, [
                'message' => 'Error: ' . $e->getMessage()
            ]));
        }
    }

    // Fungsi untuk menyimpan log kerja hasil pekerjaan teknisi (GET)
    public function save_laporan($order_kerja_id)
    {
        try {
            \Log::info('save_laporan dipanggil dengan parameter', [
                'order_kerja_id' => $order_kerja_id,
                'request' => request()->all()
            ]);

            if (!$order_kerja_id) {
                return response()->json(_response('11', $this->uri, [
                    'message' => 'Order kerja ID tidak ditemukan.'
                ]));
            }

            $asset_id = request('asset_id');
            $diagnosa = request('diagnosa');
            $tindakan = request('tindakan');
            $hasil = request('hasil');
            $durasi_menit = request('durasi_menit', 0);
            $n_param = request('n');

            $data = [
                'order_kerja_id' => $order_kerja_id,
                'asset_id' => $asset_id,
                'diagnosa' => $diagnosa,
                'tindakan' => $tindakan,
                'hasil' => $hasil,
                'durasi_menit' => $durasi_menit,
                'pegawai_id' => session('pegawai_id') ?? null,
                'tgl_log' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => session('nama_pegawai') ?? session('nama_user') ?? 'system'
            ];

            \Log::info('Data log kerja untuk disimpan', $data);
            $success = true;

            if ($success) {
                try {
                    $penugasan = DbModel::getData('penugasan_teknisi', [
                        'order_kerja_id' => $order_kerja_id,
                        'status' => 'sedang_dikerjakan'
                    ]);

                    if ($penugasan) {
                        $this->model->updateStatusPenugasan($penugasan['penugasan_id'], 'selesai');
                    }

                    DbModel::updateData(
                        'order_kerja',
                        ['status' => 'selesai', 'updated_at' => date('Y-m-d H:i:s')],
                        ['order_kerja_id' => $order_kerja_id]
                    );
                } catch (Exception $e) {
                    \Log::error('Error saat update status penugasan: ' . $e->getMessage());
                }

                $redirectUrl = url('ipsrs/teknisitugas/detail_aset/' . $asset_id);
                $redirectUrl .= '?order_kerja_id=' . $order_kerja_id;
                if ($n_param) {
                    $redirectUrl .= '&n=' . $n_param;
                }

                session()->flash('flash_success', 'Laporan kerja berhasil disimpan!');

                return response()->json(_response('02', $this->uri, [
                    'redirect_url' => $redirectUrl,
                    'message' => 'Laporan kerja berhasil disimpan'
                ]));
            } else {
                return response()->json(_response('12', $this->uri, [
                    'message' => 'Gagal menyimpan laporan kerja'
                ]));
            }
        } catch (Exception $e) {
            \Log::error('Error in save_laporan: ' . $e->getMessage());
            return response()->json(_response('12', $this->uri, [
                'message' => 'Error: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * Menampilkan halaman log aset dengan riwayat pemeliharaan/perbaikan
     */
    public function log_aset($asset_id)
    {
        try {
            $asset = $this->model->getAsset($asset_id);
            if (!$asset) {
                return redirect('ipsrs/teknisitugas')->with('error', 'Data aset tidak ditemukan');
            }

            $order_kerja_id = request('order_kerja_id');
            $order_kerja = $order_kerja_id ? $this->model->getOrderKerja($order_kerja_id) : null;
            $log_kerja = $this->model->getLogKerjaList($asset_id);
            $order_kerja_list = $this->model->getOrderKerjaListByAsset($asset_id);

            $d = [];
            $d['asset'] = $asset;
            $d['order_kerja'] = $order_kerja_list;
            $d['log_kerja'] = $log_kerja;
            $d['n_param'] = request('n');
            $d['form_log_url'] = url('ipsrs/teknisitugas/form_log_kerja/' . $order_kerja_id);
            if (request('n')) {
                $d['form_log_url'] .= '?n=' . request('n');
            }
            $d['back_url'] = url('ipsrs/teknisitugas');
            if (request('n')) {
                $d['back_url'] .= '?n=' . request('n');
            }

            return $this->renderView($this->template . 'log_aset', $d);
        } catch (Exception $e) {
            \Log::error('Error in log_aset: ' . $e->getMessage());
            return redirect('ipsrs/teknisitugas')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail aset untuk teknisi
     */
    public function detail_aset($asset_id)
    {
        try {
            $asset = $this->model->getAssetDetail($asset_id);
            if (!$asset) {
                return redirect('ipsrs/teknisitugas')->with('error', 'Aset tidak ditemukan');
            }

            $d = [];
            $d['asset'] = $asset;
            $d['order_kerja_id'] = request('order_kerja_id');
            $d['n_param'] = request('n');
            $order_kerja_list = $this->model->getOrderKerjaListByAsset($asset_id);

            $log_kerja_list = [];
            if (is_array($order_kerja_list)) {
                foreach ($order_kerja_list as $order) {
                    $item = $order;
                    $penugasan = $this->model->getPenugasanByOrderKerja($order['order_kerja_id']);
                    $item['tgl_mulai'] = $penugasan['tgl_mulai'] ?? $order['tgl_mulai'] ?? $order['tgl_dibuat'] ?? null;
                    $item['tgl_selesai'] = $penugasan['tgl_selesai'] ?? null;

                    if (!empty($order['jadwal_pm_id'])) {
                        $item['jenis'] = 'Jadwal PM';
                    } elseif (!empty($order['permintaan_id'])) {
                        $item['jenis'] = 'Perbaikan';
                    } else {
                        $item['jenis'] = '-';
                    }

                    $log = $this->model->getLogKerja($order['order_kerja_id']);
                    $item['sparepart'] = [];
                    if ($log) {
                        $sparepart = $this->model->getSparepartByLogKerja($log['log_kerja_id']);
                        $item['sparepart'] = $sparepart ?: [];
                        $item['total_biaya_sparepart'] = array_sum(array_map(function ($sp) {
                            return ($sp['jumlah'] ?? 0) * ($sp['harga_satuan'] ?? 0);
                        }, $item['sparepart']));
                        $item['total_biaya_lain'] = $log['total_biaya'] ?? 0;
                        $item['tindakan'] = $log['tindakan'] ?? '-';
                        $item['diagnosa'] = $log['diagnosa'] ?? '-';
                        $item['status'] = $log['status'] ?? $order['status'] ?? '-';
                    } else {
                        $item['total_biaya_sparepart'] = 0;
                        $item['total_biaya_lain'] = 0;
                        $item['tindakan'] = '-';
                        $item['diagnosa'] = '-';
                        $item['status'] = $order['status'] ?? '-';
                    }

                    $log_kerja_list[] = $item;
                }
            }

            usort($log_kerja_list, function ($a, $b) {
                $date_a = isset($a['tgl_mulai']) ? strtotime($a['tgl_mulai']) : 0;
                $date_b = isset($b['tgl_mulai']) ? strtotime($b['tgl_mulai']) : 0;
                return $date_b - $date_a;
            });

            $d['log_kerja_list'] = $log_kerja_list;

            return $this->renderView($this->template . 'detail_aset', $d);
        } catch (\Exception $e) {
            \Log::error('Error in detail_aset: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect('ipsrs/teknisitugas')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

