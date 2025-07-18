<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogStatusOrderKerjaModel
{
    /**
     * Mencatat perubahan status order kerja
     * 
     * @param string $order_kerja_id ID order kerja
     * @param string $status_lama Status sebelumnya
     * @param string $status_baru Status baru
     * @param string $oleh_pegawai_id ID pegawai yang melakukan perubahan
     * @param string $keterangan Keterangan perubahan status (opsional)
     * @return array Status operasi
     */
    public function logPerubahanStatus($order_kerja_id, $status_lama, $status_baru, $oleh_pegawai_id, $keterangan = '')
    {
        try {
            // Generate ID untuk log status
            $log_status_id = DbModel::getId('log_status_order_kerja', 2, 12);
            $userId = session('user_id') ?? $oleh_pegawai_id;
            $currentTime = date('Y-m-d H:i:s');
            
            // Data untuk diinsert dengan kolom format perusahaan di awal
            $data = [
                // Kolom format perusahaan di awal
                'created_at' => $currentTime,
                'created_by' => $userId,
                'updated_at' => $currentTime,
                'updated_by' => $userId,
                'deleted_at' => null,
                'deleted_by' => null,
                'deleted_st' => 0,
                'active_st' => 1,
                
                // Kolom asli tabel
                'log_status_id' => $log_status_id,
                'order_kerja_id' => $order_kerja_id,
                'status_sebelumnya' => $status_lama,
                'status_baru' => $status_baru,
                'tgl_perubahan' => $currentTime,
                'oleh_pegawai_id' => $oleh_pegawai_id,
                'catatan' => $keterangan
            ];
            
            // Insert ke database menggunakan DbModel
            DbModel::insertData('log_status_order_kerja', $data);
            
            return ['status' => true, 'message' => 'Perubahan status berhasil dicatat'];
        } catch (\Exception $e) {
            // Log error
            error_log('Error saat mencatat perubahan status: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Gagal mencatat perubahan status: ' . $e->getMessage()];
        }
    }

    /**
     * Mengambil riwayat perubahan status order kerja
     * 
     * @param string $order_kerja_id ID order kerja
     * @return array Riwayat status
     */
    public function getRiwayatStatus($order_kerja_id)
    {
        $query = "SELECT ls.*, p.pegawai_nm 
                  FROM log_status_order_kerja AS ls
                  LEFT JOIN mst_pegawai AS p ON ls.oleh_pegawai_id = p.pegawai_id
                  WHERE ls.order_kerja_id = ? 
                  AND ls.deleted_st = 0
                  ORDER BY ls.created_at DESC";
                  
        return DbModel::rawData('result_array', $query, [$order_kerja_id]);
    }
    
    /**
     * Method untuk memuat data di DataTables
     */
    static function loadDatatables()
    {
        $nav_sess = session(request('n'));

        $where = "1 = 1 ";
        
        // Filter berdasarkan order kerja
        if (@$nav_sess['search']['data']['order_kerja_id'] != '') {
            $where .= " AND ls.order_kerja_id = '" . @$nav_sess['search']['data']['order_kerja_id'] . "' ";
        }
        
        // Filter berdasarkan kata kunci
        if (@$nav_sess['search']['data']['term'] != '') {
            $term = strtolower($nav_sess['search']['data']['term']);
            $where .= " AND (
                LOWER(ls.status_sebelumnya) LIKE '%$term%' OR 
                LOWER(ls.status_baru) LIKE '%$term%' OR
                LOWER(ls.catatan) LIKE '%$term%' OR
                LOWER(p.pegawai_nm) LIKE '%$term%'
            ) ";
        }

        $query = "SELECT * FROM (
                SELECT 
                    ls.log_status_id,
                    ls.order_kerja_id,
                    ls.status_sebelumnya,
                    ls.status_baru,
                    ls.tgl_perubahan,
                    ls.catatan,
                    p.pegawai_nm
                FROM 
                    log_status_order_kerja ls
                    LEFT JOIN mst_pegawai p ON ls.oleh_pegawai_id = p.pegawai_id
                WHERE $where AND ls.deleted_st = 0
            ) x ";

        $search = ['order_kerja_id', 'status_sebelumnya', 'status_baru', 'pegawai_nm', 'catatan'];
        $where = null;
        $isWhere = null;

        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }
}