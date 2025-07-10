<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class JadwalPmModel extends Model
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
                    pm.jadwal_pm_id,
                    a.asset_nm,
                    pm.frekuensi,
                    pm.jenis,
                    pm.tgl_terakhir,
                    pm.tgl_berikutnya,
                    pm.status,
                    pm.active_st
                  FROM jadwal_pm pm
                  LEFT JOIN asset a ON pm.asset_id = a.asset_id";

        // 2. Kolom yang bisa dicari oleh Datatables
        $searchableColumns = ['a.asset_nm', 'pm.frekuensi', 'pm.jenis', 'pm.status'];

        // 3. Kumpulkan kondisi WHERE dalam bentuk array key-value
        $whereConditions = [];
        $whereConditions['pm.deleted_st'] = 0; // Kondisi dasar

        if (!empty(self::$nav_sess['search']['data']['asset_id'])) {
            $whereConditions['pm.asset_id'] = self::$nav_sess['search']['data']['asset_id'];
        }
        if (!empty(self::$nav_sess['search']['data']['status'])) {
            $whereConditions['pm.status'] = self::$nav_sess['search']['data']['status'];
        }

        // 4. Kondisi pencarian 'term' sebagai string SQL murni (jika ada)
        $whereString = '';
        if (!empty(self::$nav_sess['search']['data']['term'])) {
            $searchTerm = strtolower(addslashes(self::$nav_sess['search']['data']['term']));
            $whereString = " (LOWER(a.asset_nm) LIKE '%{$searchTerm}%' OR LOWER(pm.jenis) LIKE '%{$searchTerm}%') ";
        }
        
        // 5. Panggil datatablesQuery dengan parameter yang benar
        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, $whereString);
        
        return response()->json($result);
    }
}
