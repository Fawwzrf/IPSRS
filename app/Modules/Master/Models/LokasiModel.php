<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class LokasiModel extends Model
{
    protected static $nav_sess;

    /**
     * Konstruktor model Lokasi, inisialisasi session navigasi.
     */
    public function __construct()
    {
        parent::__construct();
        self::initSession();
    }

    /**
     * Inisialisasi session navigasi jika belum ada.
     */
    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    /**
     * Generate lokasi_id baru berdasarkan tipe dan parent.
     *
     * @param string $tipe
     * @param string|null $parentId
     * @return string
     */
    public static function generateLokasiId($tipe, $parentId = null)
    {
        $prefix = $parentId ? $parentId . '.' : '';
        $query = "SELECT MAX(lokasi_id) as last_id FROM mst_lokasi WHERE tipe_lokasi = ? AND deleted_st = 0";
        $params = [$tipe];

        if ($parentId) {
            $query .= " AND parent_lokasi_id = ?";
            $params[] = $parentId;
        } else {
            $query .= " AND parent_lokasi_id IS NULL";
        }

        $lastData = DbModel::rawData('row_array', $query, $params);
        $lastId = $lastData['last_id'] ?? '';

        if (empty($lastId)) {
            return $prefix . '01';
        } else {
            $parts = explode('.', $lastId);
            $lastNumber = (int)end($parts);
            $newNumber = $lastNumber + 1;
            return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Load data lokasi untuk datatables dengan filter pencarian.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    static function loadDatatables()
    {
        self::initSession();
        
        $where = "1 = 1 ";

        if (@self::$nav_sess['search']['data']['tipe_lokasi'] != '') {
            $where .= " AND a.tipe_lokasi = '" . @self::$nav_sess['search']['data']['tipe_lokasi'] . "' ";
        }
        
        if (@self::$nav_sess['search']['data']['parent_lokasi_id'] != '') {
            $where .= " AND a.parent_lokasi_id = '" . @self::$nav_sess['search']['data']['parent_lokasi_id'] . "' ";
        }
        
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $where .= " AND a.active_st = '" . @self::$nav_sess['search']['data']['active_st'] . "' ";
        }
        
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (
                      LOWER(a.lokasi_id) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                      OR LOWER(a.lokasi_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(a.deskripsi) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(b.lokasi_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                    ) ";
        }

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
