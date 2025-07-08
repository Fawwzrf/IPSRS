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

        // Query utama dengan LEFT JOIN (TANPA KLAUSA WHERE DI SINI)
        // Pastikan semua kolom diberi alias tabel untuk menghindari ambiguitas
        $query = "SELECT
                    a.lokasi_id, a.lokasi_nm, a.tipe_lokasi, a.parent_lokasi_id,
                    a.deskripsi, a.denah_url, a.active_st, a.deleted_st,
                    b.lokasi_nm as parent_lokasi_nm
                  FROM mst_lokasi a
                  LEFT JOIN mst_lokasi b ON a.parent_lokasi_id = b.lokasi_id";

        // Kolom yang dapat dicari oleh DataTables (gunakan alias tabel yang benar)
        $search = [
            'a.lokasi_id', 'a.lokasi_nm', 'a.tipe_lokasi', 'b.lokasi_nm', 'a.deskripsi' // 'b.lokasi_nm'
        ];

        // --- Kumpulkan kondisi WHERE untuk parameter $where (key-value pairs) ---
        $conditionsForWhereParam = []; 
        $conditionsForWhereParam[] = 'a.deleted_st = 0'; // Kondisi dasar (sebagai string)

        // Filter dari sesi pencarian (akan ditambahkan ke $conditionsForWhereParam)
        // Logika filter dinamis ini tetap ada meskipun form filter dihapus,
        // Ini memastikan jika ada data filter di sesi dari akses sebelumnya atau secara programatis
        if (@self::$nav_sess['search']['data']['tipe_lokasi'] != '') {
            $conditionsForWhereParam['a.tipe_lokasi'] = @self::$nav_sess['search']['data']['tipe_lokasi'];
        }
        if (@self::$nav_sess['search']['data']['parent_lokasi_id'] != '') {
            if (@self::$nav_sess['search']['data']['parent_lokasi_id'] == 'NULL' || @self::$nav_sess['search']['data']['parent_lokasi_id'] == '') {
                $conditionsForWhereParam[] = 'a.parent_lokasi_id IS NULL';
            } else {
                $conditionsForWhereParam['a.parent_lokasi_id'] = @self::$nav_sess['search']['data']['parent_lokasi_id'];
            }
        }
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForWhereParam['a.active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }
        
        // Kondisi pencarian 'term' sebagai string SQL murni, akan masuk ke parameter $isWhere (string murni)
        $isWhereString = ''; // Inisialisasi $isWhereString
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            $isWhereString .= (empty($isWhereString) ? "" : " AND ") . " ( LOWER(a.lokasi_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(a.deskripsi) LIKE '%{$searchTerm}%'
                         OR LOWER(b.lokasi_nm) LIKE '%{$searchTerm}%'
                       ) ";
        }
        // --- AKHIR PENYESUAIAN KONDISI WHERE ---

        $result = DbModel::datatablesQuery($query, $search, $conditionsForWhereParam, $isWhereString);
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