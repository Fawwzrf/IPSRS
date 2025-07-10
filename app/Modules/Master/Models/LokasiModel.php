<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    /**
     * PERBAIKAN TOTAL PADA FUNGSI INI
     * Mengadopsi pola modern dan aman untuk membangun query.
     */
    static function loadDatatables()
    {
        self::initSession();

        // 1. Query dasar tanpa klausa WHERE
        $query = "SELECT
                    a.lokasi_id, a.lokasi_nm, a.tipe_lokasi, a.parent_lokasi_id,
                    a.deskripsi, a.active_st,
                    b.lokasi_nm as parent_lokasi_nm
                  FROM mst_lokasi a
                  LEFT JOIN mst_lokasi b ON a.parent_lokasi_id = b.lokasi_id";

        // 2. Kolom yang dapat dicari oleh DataTables
        $searchableColumns = [
            'a.lokasi_id',
            'a.lokasi_nm',
            'a.tipe_lokasi',
            'b.lokasi_nm',
            'a.deskripsi'
        ];

        // 3. Kumpulkan kondisi WHERE dalam bentuk array (key-value untuk keamanan)
        $whereConditions = [];
        $whereConditions['a.deleted_st'] = 0; // Kondisi wajib

        // Filter dari sesi pencarian
        if (!empty(self::$nav_sess['search']['data']['tipe_lokasi'])) {
            $whereConditions['a.tipe_lokasi'] = self::$nav_sess['search']['data']['tipe_lokasi'];
        }
        if (!empty(self::$nav_sess['search']['data']['parent_lokasi_id'])) {
            $whereConditions['a.parent_lokasi_id'] = self::$nav_sess['search']['data']['parent_lokasi_id'];
        }
        if (isset(self::$nav_sess['search']['data']['active_st']) && self::$nav_sess['search']['data']['active_st'] !== '') {
            $whereConditions['a.active_st'] = self::$nav_sess['search']['data']['active_st'];
        }

        // 4. Buat string WHERE terpisah untuk pencarian teks (LIKE)
        $whereString = '';
        if (!empty(self::$nav_sess['search']['data']['term'])) {
            $searchTerm = strtolower(self::$nav_sess['search']['data']['term']);
            // Pencarian diperluas ke beberapa kolom yang relevan
            $whereString = "
                (LOWER(a.lokasi_id) LIKE '%{$searchTerm}%' 
                OR LOWER(a.lokasi_nm) LIKE '%{$searchTerm}%' 
                OR LOWER(a.deskripsi) LIKE '%{$searchTerm}%'
                OR LOWER(b.lokasi_nm) LIKE '%{$searchTerm}%')
            ";
        }

        // 5. Panggil fungsi datatables dengan parameter yang sudah terstruktur
        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, $whereString);

        return $result;
    }

    static function getLokasiPath($lokasiId)
    {
        $path = [];
        $currentId = $lokasiId;
        while ($currentId) {
            $lokasi = DbModel::rawData('row_array', "SELECT lokasi_id, lokasi_nm, parent_lokasi_id FROM mst_lokasi WHERE lokasi_id = ?", [$currentId]);
            if ($lokasi) {
                array_unshift($path, $lokasi['lokasi_nm']);
                $currentId = $lokasi['parent_lokasi_id'];
            } else {
                $currentId = null;
            }
        }
        return implode(' > ', $path);
    }
}
