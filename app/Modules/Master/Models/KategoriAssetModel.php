<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class KategoriAssetModel extends Model
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
        
        // Gunakan pola yang sama dengan PegawaiModel (acuan)
        $where = "1 = 1 ";

        // Filter berdasarkan active_st
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $where .= " AND a.active_st = '" . @self::$nav_sess['search']['data']['active_st'] . "' ";
        }
        
        // Filter pencarian
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (
                LOWER(a.kategori_asset_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                OR LOWER(a.deskripsi) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
            )";
        }

        // Gunakan subquery dengan alias seperti pada PegawaiModel
        $query = "SELECT * FROM (
                    SELECT 
                        a.kategori_asset_id, 
                        a.kategori_asset_nm, 
                        a.deskripsi, 
                        a.active_st
                    FROM 
                        mst_kategori_asset a
                    WHERE $where AND a.deleted_st = 0
                ) x ";
                
        $search = ['kategori_asset_id', 'kategori_asset_nm', 'deskripsi'];
        $where = null;
        $isWhere = null;
        
        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }
}