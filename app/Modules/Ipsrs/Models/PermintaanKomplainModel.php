<?php

namespace App\Modules\Ipsrs\Models; // Perbaiki namespace: namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel; // Perbaiki namespace: use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PermintaanKomplainModel extends Model
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
                    pk.permintaan_id, pk.tgl, pk.deskripsi, pk.status, pk.active_st, pk.deleted_st,
                    ast.asset_id, ast.asset_nm, ast.no_seri as asset_no_seri, loc.lokasi_nm as asset_lokasi_nm,
                    pgw.pegawai_nm as pembuat_komplain_nm
                  FROM permintaan_komplain pk
                  LEFT JOIN asset ast ON pk.asset_id = ast.asset_id
                  LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                  LEFT JOIN mst_pegawai pgw ON pk.pegawai_id = pgw.pegawai_id";

        // Kolom yang dapat dicari oleh DataTables
        $search = [
            'pk.permintaan_id', 'pk.tgl', 'pk.deskripsi', 'pk.status',
            'ast.asset_nm', 'ast.no_seri', 'loc.lokasi_nm', 'pgw.pegawai_nm'
        ];

        // Kumpulkan semua kondisi WHERE untuk parameter $where (key-value pairs)
        $conditionsForDbModel = []; 
        $conditionsForDbModel[] = 'pk.deleted_st = 0'; // Kondisi dasar

        // Filter dari sesi pencarian
        if (@self::$nav_sess['search']['data']['asset_id'] != '') {
            $conditionsForDbModel['pk.asset_id'] = @self::$nav_sess['search']['data']['asset_id'];
        }
        if (@self::$nav_sess['search']['data']['pegawai_id'] != '') {
            $conditionsForDbModel['pk.pegawai_id'] = @self::$nav_sess['search']['data']['pegawai_id'];
        }
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $conditionsForDbModel['pk.status'] = @self::$nav_sess['search']['data']['status'];
        }
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForDbModel['pk.active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }
        
        // Kondisi pencarian 'term' sebagai string SQL murni
        $isWhereString = ''; 
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            $isWhereString .= (empty($isWhereString) ? "" : " AND ") . " ( LOWER(pk.permintaan_id) LIKE '%{$searchTerm}%'
                         OR LOWER(pk.deskripsi) LIKE '%{$searchTerm}%'
                         OR LOWER(ast.asset_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(ast.no_seri) LIKE '%{$searchTerm}%'
                         OR LOWER(loc.lokasi_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(pgw.pegawai_nm) LIKE '%{$searchTerm}%'
                       ) ";
        }
        
        // Panggil DbModel::datatablesQuery dengan parameter yang benar
        $result = DbModel::datatablesQuery($query, $search, $conditionsForDbModel, $isWhereString);
        return $result;
    }
}