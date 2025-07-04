<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

        // Query utama TANPA KLAUSA WHERE di sini
        $query = "SELECT
                    a.kategori_asset_id, a.kategori_asset_nm, a.deskripsi, a.active_st, a.deleted_st
                  FROM mst_kategori_asset a";

        // Kolom yang dapat dicari oleh DataTables
        $search = [
            'a.kategori_asset_id', 'a.kategori_asset_nm', 'a.deskripsi', 'a.active_st'
        ];

        // Kumpulkan semua kondisi WHERE untuk parameter $where (key-value pairs)
        $conditionsForWhereParam = [];
        $conditionsForWhereParam[] = 'a.deleted_st = 0'; // Kondisi dasar

        // Filter dari sesi pencarian
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForWhereParam['a.active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }
        
        // Kondisi pencarian 'term' sebagai string SQL murni
        $isWhereString = ''; 
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            $isWhereString .= (empty($isWhereString) ? "" : " AND ") . " ( LOWER(a.kategori_asset_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(a.deskripsi) LIKE '%{$searchTerm}%'
                       ) ";
        }
        
        // Panggil DbModel::datatablesQuery dengan parameter yang benar
        $result = DbModel::datatablesQuery($query, $search, $conditionsForWhereParam, $isWhereString);
        return $result;
    }
}