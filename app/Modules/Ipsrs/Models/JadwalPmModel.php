<?php

namespace App\Modules\Ipsrs\Models; // Perbaiki namespace

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

        // Query utama TANPA KLAUSA WHERE di sini
        $query = "SELECT
                    a.jadwal_pm_id, a.frekuensi, a.jenis, a.tgl_terakhir, a.tgl_berikutnya, a.status, a.estimasi_menit, a.active_st, a.deleted_st,
                    ast.asset_nm, ast.no_seri as asset_no_seri
                  FROM jadwal_pm a
                  LEFT JOIN asset ast ON a.asset_id = ast.asset_id";

        // Kolom yang dapat dicari oleh DataTables
        $search = [
            'a.jadwal_pm_id',
            'a.frekuensi',
            'a.jenis',
            'a.status',
            'ast.asset_nm',
            'ast.no_seri'
        ];

        // Kumpulkan semua kondisi WHERE untuk parameter $where (key-value pairs)
        $conditionsForWhereParam = [];
        $conditionsForWhereParam[] = 'a.deleted_st = 0'; // Kondisi dasar

        // Filter dari sesi pencarian
        if (@self::$nav_sess['search']['data']['asset_id'] != '') {
            $conditionsForWhereParam['a.asset_id'] = @self::$nav_sess['search']['data']['asset_id'];
        }
        if (@self::$nav_sess['search']['data']['frekuensi'] != '') {
            $conditionsForWhereParam['a.frekuensi'] = @self::$nav_sess['search']['data']['frekuensi'];
        }
        if (@self::$nav_sess['search']['data']['jenis'] != '') {
            $conditionsForWhereParam['a.jenis'] = @self::$nav_sess['search']['data']['jenis'];
        }
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $conditionsForWhereParam['a.status'] = @self::$nav_sess['search']['data']['status'];
        }
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForWhereParam['a.active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }

        // Kondisi pencarian 'term' sebagai string SQL murni
        $isWhereString = '';
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            $isWhereString .= (empty($isWhereString) ? "" : " AND ") . " ( LOWER(ast.asset_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(ast.no_seri) LIKE '%{$searchTerm}%'
                         OR LOWER(a.deskripsi) LIKE '%{$searchTerm}%'
                       ) ";
        }

        // Panggil DbModel::datatablesQuery dengan parameter yang benar
        $result = DbModel::datatablesQuery($query, $search, $conditionsForWhereParam, $isWhereString);
        return $result;
    }
}
