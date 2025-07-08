<?php

namespace App\Modules\Master\Models; // Perbaiki namespace: namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel; // Perbaiki namespace: use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SparepartModel extends Model
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
                    sparepart_id, sparepart_nm, no_seri, merk, satuan, harga, stok, lokasi_penyimpanan, active_st, deleted_st
                  FROM mst_sparepart";

        // Kolom yang dapat dicari oleh DataTables
        $search = [
            'sparepart_id', 'sparepart_nm', 'no_seri', 'merk', 'satuan', 'lokasi_penyimpanan'
        ];

        // Kumpulkan semua kondisi WHERE untuk parameter $where (key-value pairs)
        $conditionsForWhereParam = []; 
        $conditionsForWhereParam[] = 'deleted_st = 0'; // Kondisi dasar

        // Filter dari sesi pencarian
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForWhereParam['active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }
        
        // Kondisi pencarian 'term' sebagai string SQL murni
        $isWhereString = ''; 
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            $isWhereString .= (empty($isWhereString) ? "" : " AND ") . " ( LOWER(sparepart_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(no_seri) LIKE '%{$searchTerm}%'
                         OR LOWER(merk) LIKE '%{$searchTerm}%'
                         OR LOWER(lokasi_penyimpanan) LIKE '%{$searchTerm}%'
                       ) ";
        }
        
        // Panggil DbModel::datatablesQuery dengan parameter yang benar
        $result = DbModel::datatablesQuery($query, $search, $conditionsForWhereParam, $isWhereString);
        return $result;
    }
}