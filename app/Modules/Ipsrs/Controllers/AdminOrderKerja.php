<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\OrderKerjaModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;
use App\Modules\Ipsrs\Models\LogStatusOrderKerjaModel; // Tambahkan use statement ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Pastikan DB di-import

class AdminOrderKerja extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.order_kerja.';
    }

    public function index()
    {
        $d = [];
        
        // Penting: Panggil save_session_search untuk mengelola session pencarian
        $this->save_session_search($d);
        
        // Data untuk dropdown dan filter
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1', 'jabatan_id' => '90']);
        
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d = [];
        
        if ($id == null) {
            // Form baru
            // Pastikan semua field yang diperlukan tersedia
            $sql = "SELECT jp.*, a.asset_nm, 
                    COALESCE(jp.frekuensi, 'N/A') as frekuensi, 
                    COALESCE(jp.jenis, 'N/A') as jenis
                    FROM jadwal_pm jp 
                    JOIN asset a ON jp.asset_id = a.asset_id 
                    WHERE jp.deleted_st = 0 
                    AND jp.active_st = 1
                    AND jp.status != 'dibatalkan'
                    AND jp.jadwal_pm_id NOT IN (
                        SELECT DISTINCT jadwal_pm_id FROM order_kerja 
                        WHERE jadwal_pm_id IS NOT NULL 
                        AND deleted_st = 0
                        AND status NOT IN ('selesai', 'dibatalkan')
                    )";
            $d['all_jadwal_pm'] = DbModel::rawData('result_array', $sql);
            
            // Ambil permintaan komplain yang belum dibuatkan order kerja
            $sql = "SELECT pk.*, a.asset_nm 
                    FROM permintaan_komplain pk 
                    JOIN asset a ON pk.asset_id = a.asset_id 
                    WHERE pk.deleted_st = 0 
                    AND pk.status IN ('diverifikasi', 'baru', 'dikirim', 'diterima')
                    AND pk.permintaan_id NOT IN (
                        SELECT DISTINCT permintaan_id FROM order_kerja 
                        WHERE permintaan_id IS NOT NULL 
                        AND deleted_st = 0
                        AND status NOT IN ('selesai', 'dibatalkan')
                    )";
            $d['all_komplain'] = DbModel::rawData('result_array', $sql);
            $d['assigned_teknisi'] = [];
        } else {
            // Form edit - pastikan data jadwal_pm lengkap jika ada
            $d['main'] = $this->model->getById($id);
            
            if (!empty($d['main']['jadwal_pm_id'])) {
                $sql = "SELECT jp.*, a.asset_nm, 
                        COALESCE(jp.frekuensi, 'N/A') as frekuensi, 
                        COALESCE(jp.jenis, 'N/A') as jenis
                        FROM jadwal_pm jp 
                        JOIN asset a ON jp.asset_id = a.asset_id 
                        WHERE jp.jadwal_pm_id = ?";
                $d['all_jadwal_pm'] = DbModel::rawData('result_array', $sql, [$d['main']['jadwal_pm_id']]);
            } else {
                $d['all_jadwal_pm'] = [];
            }
            
            // Data permintaan jika ada
            if (!empty($d['main']['permintaan_id'])) {
                $sql = "SELECT pk.*, a.asset_nm 
                        FROM permintaan_komplain pk 
                        JOIN asset a ON pk.asset_id = a.asset_id 
                        WHERE pk.permintaan_id = ?";
                $d['all_komplain'] = DbModel::rawData('result_array', $sql, [$d['main']['permintaan_id']]);
            } else {
                $d['all_komplain'] = [];
            }
            
            // Data teknisi yang ditugaskan
            $d['assigned_teknisi'] = array_column(
                DbModel::allData('penugasan_teknisi', ['order_kerja_id' => $id, 'deleted_st' => 0]),
                'pegawai_id'
            );
        }
        
        // Data umum yang selalu dibutuhkan
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1', 'jabatan_id' => '90']);
        $d['form_act'] = $this->uri . '/save/' . $id;
        
        return $this->renderView($this->template . 'form_modal', $d);
    }

    /**
     * Menyimpan data order kerja atau log kerja
     * 
     * @param string|null $id ID order kerja untuk update (opsional)
     * @return \Illuminate\Http\JsonResponse
     */
    public function save($id = null)
    {
        try {
            $d = request()->all();
            
            // Hapus field yang tidak perlu disimpan ke database
            $fieldsToRemove = ['_token', '_is_ajax', 'n', 'action'];
            foreach ($fieldsToRemove as $field) {
                if (isset($d[$field])) {
                    unset($d[$field]);
                }
            }
            
            // Log input data yang sudah dibersihkan
            \Log::info('AdminOrderKerja::save input setelah dibersihkan', array_keys($d));
            
            // Cek apakah ini aksi simpan log kerja
            if (isset($d['action']) && $d['action'] == 'save_log_kerja') {
                $logKerjaModel = new LogKerjaModel();
                $result = $logKerjaModel->saveData($id, $d);

                if ($result['status']) {
                    return response()->json(_response('01', $this->uri, [
                        'message' => 'Laporan kerja berhasil disimpan.',
                        'log_kerja_id' => $result['log_kerja_id'] ?? null
                    ]));
                } else {
                    return response()->json(_response('11', $this->uri, [
                        'message' => $result['message'] ?? 'Gagal menyimpan laporan kerja.'
                    ]));
                }
            } else {
                // Ini adalah blok untuk menyimpan Order Kerja
                // Ubah pegawai_ids menjadi teknisi untuk kompatibilitas dengan model
                if (isset($d['pegawai_ids']) && !empty($d['pegawai_ids'])) {
                    $d['teknisi'] = $d['pegawai_ids'];
                    unset($d['pegawai_ids']);
                }
                
                // Format tanggal dengan benar
                if (isset($d['tgl_dibuat']) && strpos($d['tgl_dibuat'], '-') !== false) {
                    $parts = explode('-', $d['tgl_dibuat']);
                    if (count($parts) === 3 && strlen($parts[2]) === 4) {
                        // Ubah dari DD-MM-YYYY ke YYYY-MM-DD
                        $d['tgl_dibuat'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                }
                
                if (isset($d['tgl_target_selesai']) && strpos($d['tgl_target_selesai'], '-') !== false) {
                    $parts = explode('-', $d['tgl_target_selesai']);
                    if (count($parts) === 3 && strlen($parts[2]) === 4) {
                        $d['tgl_target_selesai'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                }
                
                // Ambil deskripsi dari jadwal_pm atau permintaan jika tersedia
                if (empty($d['catatan']) && empty($d['deskripsi'])) {
                    if (!empty($d['jadwal_pm_id'])) {
                        $jadwal = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $d['jadwal_pm_id']]);
                        if ($jadwal) {
                            // PERBAIKAN: Gunakan catatan, bukan deskripsi
                            $d['catatan'] = 'Pemeliharaan: ' . $jadwal['jenis'];
                            $d['jenis'] = 'pemeliharaan';
                        }
                    } 
                    else if (!empty($d['permintaan_id'])) {
                        $permintaan = DbModel::getData('permintaan_komplain', ['permintaan_id' => $d['permintaan_id']]);
                        if ($permintaan) {
                            // PERBAIKAN: Gunakan catatan, bukan deskripsi
                            $d['catatan'] = $permintaan['deskripsi'] ?? '';
                            $d['jenis'] = 'perbaikan';
                        }
                    }
                    
                    // Jika masih tidak ada catatan, buat default
                    if (empty($d['catatan'])) {
                        $d['catatan'] = 'Order kerja baru dibuat tanggal ' . date('d-m-Y');
                    }
                } else if (!empty($d['deskripsi']) && empty($d['catatan'])) {
                    // Pindahkan nilai deskripsi ke catatan
                    $d['catatan'] = $d['deskripsi'];
                    unset($d['deskripsi']);
                }
                
                $result = $this->model->saveData($id, $d);
                
                if ($result['status']) {
                    $response_code = ($result['mode'] == 'insert') ? '01' : '02';
                    
                    // Untuk keamanan, jangan kirim semua data request ke respons
                    $response_data = [
                        'order_kerja_id' => $result['order_kerja_id'] ?? $id,
                        'message' => ($result['mode'] == 'insert') ? 
                            'Order kerja berhasil dibuat.' : 
                            'Order kerja berhasil diperbarui.'
                    ];
                    
                    return response()->json(_response($response_code, $this->uri, $response_data));
                } else {
                    return response()->json(_response('11', $this->uri, [
                        'message' => $result['message'] ?? 'Gagal menyimpan order kerja.'
                    ]));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in AdminOrderKerja::save: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(_response('11', $this->uri, [
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]));
        }
    }

    public function delete($id)
    {
        $result = $this->model->deleteData($id);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return OrderKerjaModel::loadDatatables();
    }
    
    /**
     * Update status order kerja
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $order_kerja_id = $request->input('order_kerja_id');
        $status_baru = $request->input('status_baru');
        $keterangan = $request->input('keterangan');
        
        try {
            DB::beginTransaction();
            
            // Ambil status lama
            $query = "SELECT status FROM order_kerja WHERE order_kerja_id = ?";
            $result = DB::select($query, [$order_kerja_id]);
            
            if (empty($result)) {
                return response()->json(['status' => false, 'message' => 'Order kerja tidak ditemukan']);
            }
            
            $status_lama = $result[0]->status;
            $pegawai_id = session('pegawai_id');
            
            // Update status order kerja
            $updateQuery = "UPDATE order_kerja SET 
                            status = ?,
                            updated_at = ?,
                            updated_by = ?
                            WHERE order_kerja_id = ?";
            
            DB::update($updateQuery, [
                $status_baru, 
                date('Y-m-d H:i:s'), 
                session('user_id'), 
                $order_kerja_id
            ]);
            
            // Catat perubahan status ke log
            $logStatusModel = new LogStatusOrderKerjaModel();
            $result = $logStatusModel->logPerubahanStatus(
                $order_kerja_id,
                $status_lama,
                $status_baru,
                $pegawai_id,
                $keterangan
            );
            
            if (!$result['status']) {
                DB::rollBack();
                return response()->json($result);
            }
            
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Status berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Menampilkan form update status
     * 
     * @param string $order_kerja_id ID order kerja
     * @return \Illuminate\Http\Response
     */
    public function update_status_form($order_kerja_id)
    {
        $d['order_kerja'] = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
        
        if (!$d['order_kerja']) {
            return '<div class="alert alert-danger">Data Order Kerja tidak ditemukan.</div>';
        }
        
        return $this->renderView('ipsrs::admin.pekerjaan.order_kerja.update_status_modal', $d);
    }
}
