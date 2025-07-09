<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

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

        // 1. Siapkan query utama TANPA klausa WHERE
        $query = "SELECT
                    sparepart_id, sparepart_nm, no_seri, merk, satuan, harga, stok, lokasi_penyimpanan, active_st
                  FROM mst_sparepart";

        // 2. Siapkan array untuk menampung kondisi WHERE
        $where = [];
        $where[] = "deleted_st = 0"; // Kondisi dasar

        // Filter dari sesi pencarian
        if (($active_st = @self::$nav_sess['search']['data']['active_st']) !== '' && $active_st !== null) {
            $where[] = "active_st = " . (int)$active_st;
        }
        if ($term = @self::$nav_sess['search']['data']['term']) {
            $searchTerm = strtolower(addslashes($term));
            // Tambahkan pencarian pada ID, No. Seri, Merk, dan Lokasi Penyimpanan
            $where[] = "(LOWER(sparepart_id) LIKE '%{$searchTerm}%' 
                          OR LOWER(sparepart_nm) LIKE '%{$searchTerm}%' 
                          OR LOWER(no_seri) LIKE '%{$searchTerm}%' 
                          OR LOWER(merk) LIKE '%{$searchTerm}%' 
                          OR LOWER(lokasi_penyimpanan) LIKE '%{$searchTerm}%')";
        }

        // 3. Gabungkan semua kondisi
        $whereClause = implode(' AND ', $where);

        // Kolom yang bisa dicari
        $search = [
            'sparepart_id',
            'sparepart_nm',
            'no_seri',
            'merk',
            'lokasi_penyimpanan'
        ];

        // 4. Panggil datatablesQuery
        $result = DbModel::datatablesQuery($query, $search, null, $whereClause);

        return response()->json($result);
    }
}
