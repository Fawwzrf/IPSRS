<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel; // Perbaiki namespace: use App\Modules\App\Models\DbModel;
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

    static function loadDatatables()
    {
        self::initSession();

        // 1. Siapkan query utama TANPA klausa WHERE
        $query = "SELECT
                    a.lokasi_id, a.lokasi_nm, a.tipe_lokasi, a.parent_lokasi_id,
                    a.deskripsi, a.active_st,
                    b.lokasi_nm as parent_lokasi_nm
                  FROM mst_lokasi a
                  LEFT JOIN mst_lokasi b ON a.parent_lokasi_id = b.lokasi_id";

        // 2. Siapkan array untuk menampung kondisi WHERE
        $where = [];
        $where[] = "a.deleted_st = 0"; // Kondisi dasar (sebagai string)

        // Tambahkan filter lain dari session ke dalam array
        if ($tipe = @self::$nav_sess['search']['data']['tipe_lokasi']) {
            $where[] = "a.tipe_lokasi = '" . addslashes($tipe) . "'";
        }
        if ($parent_id = @self::$nav_sess['search']['data']['parent_lokasi_id']) {
            if ($parent_id == 'NULL') {
                $where[] = "a.parent_lokasi_id IS NULL";
            } else {
                $where[] = "a.parent_lokasi_id = '" . addslashes($parent_id) . "'";
            }
        }
        if (($active_st = @self::$nav_sess['search']['data']['active_st']) !== '' && $active_st !== null) {
            $where[] = "a.active_st = " . (int)$active_st;
        }
        if ($term = @self::$nav_sess['search']['data']['term']) {
            $searchTerm = strtolower(addslashes($term));
            $where[] = "(LOWER(a.lokasi_nm) LIKE '%{$searchTerm}%' OR LOWER(a.deskripsi) LIKE '%{$searchTerm}%')";
        }

        // 3. Gabungkan semua kondisi menjadi satu string
        $whereClause = implode(' AND ', $where);

        // Kolom yang dapat dicari oleh Datatables
        $search = [
            'a.lokasi_id',
            'a.lokasi_nm',
            'a.tipe_lokasi',
            'b.lokasi_nm',
            'a.deskripsi'
        ];

        // 4. Kirim query dan string 'where' ke helper
        // Parameter $isWhere sekarang diisi dengan string kondisi kita
        $result = DbModel::datatablesQuery($query, $search, null, $whereClause);

        return $result;
    }

    // --- HAPUS: Metode generateLokasiId() ---
    // public static function generateLokasiId($parent_id = null, $tipe_lokasi) { ... } // DIHAPUS

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