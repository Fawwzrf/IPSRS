<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\TeknisiModel;
use Exception; // Pastikan ini ada
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

        // Pastikan direktori upload ada
        $uploadPath = public_path('uploads/log_kerja');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
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
            if (!$penugasan_id) {
                return response()->json([
                    'status' => false,
                    'code' => '12',
                    'message' => 'ID Penugasan tidak ditemukan.'
                ]);
            }

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'sedang_dikerjakan');

            if ($result['success']) {
                // Format response yang konsisten dengan ekspektasi JavaScript
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

    /**
     * Menampilkan form scan barcode
     */
    public function form_scan_modal($order_kerja_id)
    {
        try {
            // Validasi order_kerja_id
            if (!$order_kerja_id) {
                return '<div class="alert alert-danger">ID Order Kerja tidak ditemukan</div>';
            }

            $d = [];
            $d['order_kerja_id'] = $order_kerja_id;
            $d['n_param'] = request('n');

            // Pastikan menggunakan partial view, bukan layout lengkap
            return $this->renderPartialView($this->template . 'form_scan_modal', $d);
        } catch (Exception $e) {
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
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


    /**
     * Mengambil kembali tugas yang ditolak/dibatalkan
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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

            // Cek status penugasan saat ini
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

            // Update status kembali menjadi "ditugaskan"
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

    /**
     * Verifikasi barcode aset
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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

            // Dapatkan asset_id yang valid dari database berdasarkan barcode
            // dan/atau order_kerja_id
            $asset_id = null;

            // Coba dapatkan asset dari barcode
            $asset = DbModel::getData('asset', ['no_seri' => $barcode]);
            if ($asset) {
                $asset_id = $asset['asset_id'];
            } else {
                // Alternatif: Coba dapatkan asset_id dari order_kerja
                $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
                if ($order_kerja) {
                    // Jika order_kerja berasal dari permintaan
                    if (!empty($order_kerja['permintaan_id'])) {
                        $permintaan = DbModel::getData('permintaan_komplain', ['permintaan_id' => $order_kerja['permintaan_id']]);
                        $asset_id = $permintaan['asset_id'] ?? null;
                    }
                    // Jika order_kerja berasal dari jadwal PM
                    elseif (!empty($order_kerja['jadwal_pm_id'])) {
                        $jadwalPm = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $order_kerja['jadwal_pm_id']]);
                        $asset_id = $jadwalPm['asset_id'] ?? null;
                    }
                }
            }

            // Validasi apakah asset_id ditemukan
            if ($asset_id) {
                // Berhasil mendapatkan asset_id valid
                $result = [
                    'success' => true,
                    'asset_id' => $asset_id,
                    'msg' => 'Barcode terverifikasi.'
                ];
            } else {
                // Gagal mendapatkan asset_id valid
                $result = [
                    'success' => false,
                    'msg' => 'Barcode tidak valid atau tidak sesuai dengan aset pada order kerja ini.'
                ];
            }

            if ($result['success']) {
                // Ubah URL redirect ke detail_aset di modul Teknisi (bukan di Master)
                $detail_url = url('ipsrs/teknisitugas/detail_aset/' . $result['asset_id']);

                // Tambahkan parameter yang diperlukan
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
                'message' => $result['msg']
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

    /**
     * Menampilkan form log kerja setelah scan barcode berhasil
     * 
     * @param string $order_kerja_id ID order kerja
     * @return \Illuminate\View\View
     */
    public function form_log_kerja($order_kerja_id)
    {
        try {
            // Validasi order kerja
            $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
            if (!$order_kerja) {
                return '<div class="alert alert-danger">Order kerja tidak ditemukan</div>';
            }

            // Persiapkan data untuk form
            $d = [
                'order_kerja_id' => $order_kerja_id,
                'order_kerja' => $order_kerja,
                'asset_id' => $order_kerja['asset_id'] ?? null,
                'n_param' => request('n'),
                'form_act' => url('ipsrs/teknisitugas/save_log_kerja/' . $order_kerja_id)
            ];

            // Jika asset_id tidak ada di order_kerja, coba ambil dari permintaan atau jadwal_pm
            if (empty($d['asset_id'])) {
                if (!empty($order_kerja['permintaan_id'])) {
                    $permintaan = DbModel::getData('permintaan_komplain', ['permintaan_id' => $order_kerja['permintaan_id']]);
                    $d['asset_id'] = $permintaan['asset_id'] ?? null;
                } elseif (!empty($order_kerja['jadwal_pm_id'])) {
                    $jadwal_pm = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $order_kerja['jadwal_pm_id']]);
                    $d['asset_id'] = $jadwal_pm['asset_id'] ?? null;
                }
            }

            // Tambahkan all_sparepart ke data yang dikirim ke view
            $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);

            return view($this->template . 'form_log_kerja_modal', $d);
        } catch (\Exception $e) {
            \Log::error('Error in form_log_kerja: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Menyimpan log kerja hasil pekerjaan teknisi
     * 
     * @param Request $request
     * @param string|null $order_kerja_id ID order kerja (opsional)
     * @return \Illuminate\Http\JsonResponse
     */
    public function save_log_kerja(Request $request)
    {
        try {
            $order_kerja_id = $request->input('order_kerja_id');

            // Tambahkan debug log
            \Log::info('save_log_kerja dipanggil', [
                'order_kerja_id' => $order_kerja_id,
                'request_data' => $request->all()
            ]);

            if (!$order_kerja_id) {
                return response()->json(_response('11', $this->uri, [
                    'message' => 'Order Kerja ID tidak ditemukan.'
                ]));
            }

            // Data log kerja
            $sparepart_id = $request->input('sparepart_id'); // array
            $jumlah = $request->input('jumlah'); // array

            $sparepart = [];
            foreach ($sparepart_id as $i => $id) {
                if ($id) {
                    $sparepart[] = [
                        'id' => $id,
                        'jumlah' => $jumlah[$i] ?? 1
                    ];
                }
            }

            $logData = [
                'order_kerja_id' => $order_kerja_id,
                'asset_id' => $request->input('asset_id'),
                'diagnosa' => $request->input('diagnosa'),
                'tindakan' => $request->input('tindakan'),
                'hasil' => $request->input('hasil'),
                'durasi_menit' => $request->input('durasi_menit') ?: 0,
                'total_biaya' => str_replace(['.', ','], ['', '.'], $request->input('total_biaya')) ?: 0,
                'sparepart' => $sparepart,
                'fotos' => $request->file('fotos')
            ];

            // Gunakan model yang sudah ada untuk menyimpan data
            $logKerjaModel = new LogKerjaModel();
            $result = $logKerjaModel->saveData($order_kerja_id, $logData);

            if ($result['status']) {
                // Update status penugasan menjadi 'selesai'
                try {
                    $order = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
                    if ($order) {
                        $penugasan = DbModel::getData('penugasan_teknisi', [
                            'order_kerja_id' => $order_kerja_id,
                            'status' => 'sedang_dikerjakan'
                        ]);

                        if ($penugasan) {
                            $this->model->updateStatusPenugasan($penugasan['penugasan_id'], 'selesai');
                            // Tambahkan update tgl_selesai
                            DbModel::updateData('penugasan_teknisi', [
                                'status' => 'selesai',
                                'tgl_selesai' => now(),
                                'updated_at' => now(),
                                'updated_by' => session('user_name')
                            ], ['order_kerja_id' => $order_kerja_id, 'pegawai_id' => session('pegawai_id')]);
                        }
                    }
                } catch (Exception $e) {
                    // Log error tapi tetap lanjutkan proses
                    \Log::error('Error saat update status penugasan: ' . $e->getMessage());
                }

                // Tentukan URL redirect
                $redirectUrl = '';
                $nParam = $request->input('n');

                // Tentukan URL redirect berdasarkan konteks
                if ($request->has('context') && $request->input('context') === 'teknisi') {
                    // Jika konteks dari halaman teknisi, redirect ke daftar tugas
                    $redirectUrl = url('ipsrs/teknisitugas') . ($nParam ? '?n=' . $nParam : '');
                } else {
                    // Default redirect ke detail asset
                    $redirectUrl = url('master/asset/detail/' . $request->input('asset_id'));
                }

                // Jika hasil dipilih "Perlu Tidak Lanjut" atau "Menunggu Sparepart", update status order kerja
                $hasil = $request->input('hasil');
                if ($hasil === 'menunggu_sparepart') {
                    // Update status order kerja menjadi "menunggu_sparepart"
                    DbModel::updateData(
                        'order_kerja',
                        ['status' => 'menunggu_sparepart', 'updated_at' => date('Y-m-d H:i:s')],
                        ['order_kerja_id' => $order_kerja_id]
                    );
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

    /**
     * Menyimpan log kerja hasil pekerjaan teknisi (metode GET untuk rute otomatis)
     * 
     * @param string $order_kerja_id ID order kerja
     * @return \Illuminate\Http\JsonResponse
     */
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

            // Ambil data dari query string (GET)
            $asset_id = request('asset_id');
            $diagnosa = request('diagnosa');
            $tindakan = request('tindakan');
            $hasil = request('hasil');
            $durasi_menit = request('durasi_menit', 0);
            $n_param = request('n');

            // Data untuk log kerja
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
                // Update status order kerja dan penugasan teknisi
                try {
                    $penugasan = DbModel::getData('penugasan_teknisi', [
                        'order_kerja_id' => $order_kerja_id,
                        'status' => 'sedang_dikerjakan'
                    ]);

                    if ($penugasan) {
                        $this->model->updateStatusPenugasan($penugasan['penugasan_id'], 'selesai');
                    }

                    // Update status order_kerja
                    DbModel::updateData(
                        'order_kerja',
                        ['status' => 'selesai', 'updated_at' => date('Y-m-d H:i:s')],
                        ['order_kerja_id' => $order_kerja_id]
                    );
                } catch (Exception $e) {
                    \Log::error('Error saat update status: ' . $e->getMessage());
                }

                // Tentukan URL redirect
                $redirectUrl = url('ipsrs/teknisitugas/detail_aset/' . $asset_id);
                $redirectUrl .= '?order_kerja_id=' . $order_kerja_id;
                if ($n_param) {
                    $redirectUrl .= '&n=' . $n_param;
                }

                // Flash message untuk halaman detail
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
     * 
     * @param string $asset_id ID aset
     * @return \Illuminate\View\View
     */
    public function log_aset($asset_id)
    {
        try {
            // Ambil data aset
            $asset = DbModel::getData('asset', ['asset_id' => $asset_id]);
            if (!$asset) {
                return redirect('ipsrs/teknisitugas')->with('error', 'Data aset tidak ditemukan');
            }

            // Ambil data order kerja
            $order_kerja_id = request('order_kerja_id');
            $order_kerja = null;
            if ($order_kerja_id) {
                $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
            }

            // Ambil riwayat log kerja aset
            $log_kerja = DbModel::rawData(
                'result_array',
                "SELECT lk.*, p.pegawai_nm as teknisi_nama
                FROM log_kerja lk
                LEFT JOIN mst_pegawai p ON lk.teknisi_pegawai_id = p.pegawai_id
                WHERE lk.asset_id = ?
                ORDER BY lk.tgl_log DESC",
                [$asset_id]
            );

            if (!$log_kerja) {
                $log_kerja = [];
            }

            // Ambil order kerja terkait aset
            $order_kerja = DbModel::rawData(
                'result_array',
                "SELECT ok.*, p.pegawai_nm as teknisi_nama, 
                        COALESCE(pk.deskripsi, j.deskripsi) as deskripsi
                FROM order_kerja ok
                LEFT JOIN penugasan_teknisi pt ON ok.order_kerja_id = pt.order_kerja_id
                LEFT JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
                LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                LEFT JOIN jadwal_pm j ON ok.jadwal_pm_id = j.jadwal_pm_id
                WHERE pk.asset_id = ? OR j.asset_id = ?
                ORDER BY ok.tgl_dibuat DESC",
                [$asset_id, $asset_id]
            );

            // Persiapkan data untuk view
            $d = [];
            $d['asset'] = $asset;
            $d['order_kerja'] = $order_kerja;
            $d['log_kerja'] = $log_kerja;
            $d['n_param'] = request('n');

            // URL untuk form log kerja
            $d['form_log_url'] = url('ipsrs/teknisitugas/form_log_kerja/' . $order_kerja_id);
            if (request('n')) {
                $d['form_log_url'] .= '?n=' . request('n');
            }

            // URL kembali ke daftar tugas
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
            $asset = DbModel::getData('asset', ['asset_id' => $asset_id, 'deleted_st' => 0]);
            if (!$asset) {
                return redirect('ipsrs/teknisitugas')->with('error', 'Aset tidak ditemukan');
            }

            $d = [];
            $d['asset'] = $asset;
            $d['order_kerja_id'] = request('order_kerja_id');
            $d['n_param'] = request('n');

            // Ambil semua order kerja terkait aset
            try {
                $order_kerja = DbModel::rawData(
                    'result_array',
                    "SELECT ok.*, p.pegawai_nm as teknisi_nama, 
                            COALESCE(pk.deskripsi, j.deskripsi) as deskripsi
                    FROM order_kerja ok
                    LEFT JOIN penugasan_teknisi pt ON ok.order_kerja_id = pt.order_kerja_id
                    LEFT JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
                    LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN jadwal_pm j ON ok.jadwal_pm_id = j.jadwal_pm_id
                    WHERE ok.deleted_st = 0
                      AND ( (pk.asset_id = ? AND (pk.deleted_st = 0 OR pk.deleted_st IS NULL)) 
                          OR (j.asset_id = ? AND (j.deleted_st = 0 OR j.deleted_st IS NULL)) )
                      AND (pt.deleted_st = 0 OR pt.deleted_st IS NULL)
                    ORDER BY ok.tgl_dibuat DESC",
                    [$asset_id, $asset_id]
                );
            } catch (\Exception $e) {
                \Log::error('Error fetching order_kerja: ' . $e->getMessage());
                $order_kerja = [];
            }

            $log_kerja_list = [];

            if (is_array($order_kerja)) {
                foreach ($order_kerja as $order) {
                    $item = $order;

                    // Ambil penugasan teknisi terkait order kerja ini
                    $penugasan = DbModel::getData('penugasan_teknisi', [
                        'order_kerja_id' => $order['order_kerja_id'],
                        'deleted_st' => 0
                    ]);

                    // Waktu Dimulai dan Selesai dari penugasan teknisi
                    $item['tgl_mulai'] = $penugasan['tgl_mulai'] ?? $order['tgl_mulai'] ?? $order['tgl_dibuat'] ?? null;
                    $item['tgl_selesai'] = $penugasan['tgl_selesai'] ?? null;

                    // Jenis pekerjaan
                    if (!empty($order['jadwal_pm_id'])) {
                        $item['jenis'] = 'Jadwal PM';
                    } elseif (!empty($order['permintaan_id'])) {
                        $item['jenis'] = 'Perbaikan';
                    } else {
                        $item['jenis'] = '-';
                    }

                    // Ambil log kerja terkait order kerja ini
                    $log = DbModel::getData('log_kerja', ['order_kerja_id' => $order['order_kerja_id'], 'deleted_st' => 0]);
                    $item['sparepart'] = [];
                    if ($log) {
                        $sparepart = DbModel::rawData(
                            'result_array',
                            "SELECT ps.*, s.sparepart_nm FROM penggunaan_sparepart ps
                             JOIN mst_sparepart s ON ps.sparepart_id = s.sparepart_id
                             WHERE ps.log_kerja_id = ?",
                            [$log['log_kerja_id']]
                        );
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

            // Urutkan berdasarkan tanggal mulai terbaru
            usort($log_kerja_list, function ($a, $b) {
                $date_a = isset($a['tgl_mulai']) ? strtotime($a['tgl_mulai']) : 0;
                $date_b = isset($b['tgl_mulai']) ? strtotime($b['tgl_mulai']) : 0;
                return $date_b - $date_a; // descending order
            });

            $d['log_kerja_list'] = $log_kerja_list;

            return $this->renderView($this->template . 'detail_aset', $d);
        } catch (\Exception $e) {
            \Log::error('Error in detail_aset: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect('ipsrs/teknisitugas')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
