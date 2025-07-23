<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class SparepartModel extends Model
{
    protected static $nav_sess;

    /**
     * Konstruktor model, inisialisasi session navigasi.
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
     * Memuat data untuk datatables dengan filter dan pencarian.
     * @return \Illuminate\Http\JsonResponse
     */
    static function loadDatatables()
    {
        self::initSession();
        
        $where = "1 = 1 ";

        // Filter berdasarkan status aktif
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $where .= " AND a.active_st = '" . @self::$nav_sess['search']['data']['active_st'] . "' ";
        }
        
        // Filter berdasarkan pencarian
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (
                LOWER(a.sparepart_id) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                OR LOWER(a.sparepart_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                OR LOWER(a.no_seri) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                OR LOWER(a.merk) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                OR LOWER(a.lokasi_penyimpanan) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
            ) ";
        }

        $query = "SELECT * FROM (
                    SELECT 
                        a.sparepart_id, a.sparepart_nm, a.no_seri, a.merk, a.satuan, 
                        a.harga, a.stok, a.lokasi_penyimpanan, a.active_st
                    FROM 
                        mst_sparepart a
                    WHERE $where AND a.deleted_st = 0
                ) x ";
                
        $search = ['sparepart_id', 'sparepart_nm', 'no_seri', 'merk', 'lokasi_penyimpanan'];
        $where = null;
        $isWhere = null;
        
        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }
}

