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
