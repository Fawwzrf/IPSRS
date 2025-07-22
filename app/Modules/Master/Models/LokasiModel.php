<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class LokasiModel extends Model
{
    protected static $nav_sess;

    public function __construct()
    {
        parent::__construct();
        self::initSession();
    }

    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    public static function generateLokasiId($tipe, $parentId = null)
    {
        $prefix = $parentId ? $parentId . '.' : '';
        // Query untuk mencari ID terakhir berdasarkan parent dan tipe
        $query = "SELECT MAX(lokasi_id) as last_id FROM mst_lokasi WHERE tipe_lokasi = ? AND deleted_st = 0";
        $params = [$tipe];

        if ($parentId) {
            $query .= " AND parent_lokasi_id = ?";
            $params[] = $parentId;
        } else {
            // Jika tidak ada parent (untuk tipe Gedung)
            $query .= " AND parent_lokasi_id IS NULL";
        }

        $lastData = DbModel::rawData('row_array', $query, $params);
        $lastId = $lastData['last_id'] ?? '';

        if (empty($lastId)) {
            // Jika ini adalah data pertama untuk parent/tipe ini, mulai dari 01
            return $prefix . '01';
        } else {
            // Ambil bagian numerik terakhir dari ID, tambahkan 1, format ulang
            $parts = explode('.', $lastId);
            $lastNumber = (int)end($parts);
            $newNumber = $lastNumber + 1;
            // str_pad untuk memastikan formatnya selalu 2 digit (01, 02, ... 10)
            return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
        }
    }

    static function loadDatatables()
    {
        self::initSession();
        
        $where = "1 = 1 ";

        // Filter berdasarkan tipe lokasi
        if (@self::$nav_sess['search']['data']['tipe_lokasi'] != '') {
            $where .= " AND a.tipe_lokasi = '" . @self::$nav_sess['search']['data']['tipe_lokasi'] . "' ";
        }
        
        // Filter berdasarkan parent lokasi
        if (@self::$nav_sess['search']['data']['parent_lokasi_id'] != '') {
            $where .= " AND a.parent_lokasi_id = '" . @self::$nav_sess['search']['data']['parent_lokasi_id'] . "' ";
        }
        
        // Filter berdasarkan status
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $where .= " AND a.active_st = '" . @self::$nav_sess['search']['data']['active_st'] . "' ";
        }
        
        // Filter berdasarkan pencarian
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (
                      LOWER(a.lokasi_id) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                      OR LOWER(a.lokasi_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(a.deskripsi) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(b.lokasi_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                    ) ";
        }

        // Gunakan subquery dengan alias seperti pada PegawaiModel
        $query = "SELECT * FROM (
                    SELECT
                        a.lokasi_id, a.lokasi_nm, a.tipe_lokasi, a.parent_lokasi_id,
                        a.deskripsi, a.active_st, a.denah_url,
                        b.lokasi_nm as parent_lokasi_nm
                    FROM mst_lokasi a
                    LEFT JOIN mst_lokasi b ON a.parent_lokasi_id = b.lokasi_id
                    WHERE $where AND a.deleted_st = 0
                ) x ";
                
        $search = ['lokasi_id', 'lokasi_nm', 'tipe_lokasi', 'parent_lokasi_id', 'deskripsi'];
        $where = null;
        $isWhere = null;
        
        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }

    // Pertahankan fungsi lain yang sudah ada...
}
