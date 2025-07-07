<?php

namespace App\Modules\Ipsrs\Models; // Perbaiki namespace: namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel; // Perbaiki namespace: use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderKerjaModel extends Model
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
                    ok.order_kerja_id, ok.jenis, ok.tgl_dibuat, ok.tgl_target_selesai,
                    ok.status, ok.prioritas, ok.estimasi_biaya, ok.active_st, ok.deleted_st,
                    jp.frekuensi as jadwal_frekuensi, jp.jenis as jadwal_jenis,
                    pk.deskripsi as komplain_deskripsi,
                    ast.asset_id, ast.asset_nm, ast.no_seri as asset_no_seri, ast.lokasi_id,
                    loc.lokasi_nm as asset_lokasi_nm
                  FROM order_kerja ok
                  LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                  LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                  LEFT JOIN asset ast ON (jp.asset_id = ast.asset_id OR pk.asset_id = ast.asset_id) -- Link asset melalui JP atau PK
                  LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id";

        // Kolom yang dapat dicari oleh DataTables
        $search = [
            'ok.order_kerja_id', 'ok.jenis', 'ok.status', 'ok.prioritas',
            'ast.asset_nm', 'ast.no_seri', 'loc.lokasi_nm',
            'jp.frekuensi', 'jp.jenis', 'pk.deskripsi'
        ];

        // Kumpulkan semua kondisi WHERE untuk parameter $where (key-value pairs)
        $conditionsForDbModel = []; 
        $conditionsForDbModel[] = 'ok.deleted_st = 0'; // Kondisi dasar

        // Filter berdasarkan jenis (pemeliharaan/perbaikan) jika ada di sesi
        if (@self::$nav_sess['search']['data']['jenis_filter'] != '') {
            $conditionsForDbModel['ok.jenis'] = @self::$nav_sess['search']['data']['jenis_filter'];
        }
        
        // Filter dari sesi pencarian (sisanya)
        if (@self::$nav_sess['search']['data']['asset_id'] != '') {
            $conditionsForDbModel['ast.asset_id'] = @self::$nav_sess['search']['data']['asset_id'];
        }
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $conditionsForDbModel['ok.status'] = @self::$nav_sess['search']['data']['status'];
        }
        if (@self::$nav_sess['search']['data']['prioritas'] != '') {
            $conditionsForDbModel['ok.prioritas'] = @self::$nav_sess['search']['data']['prioritas'];
        }
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForDbModel['ok.active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }
        
        // Kondisi pencarian 'term' sebagai string SQL murni
        $isWhereString = ''; 
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            $isWhereString .= (empty($isWhereString) ? "" : " AND ") . " ( LOWER(ok.order_kerja_id) LIKE '%{$searchTerm}%'
                         OR LOWER(ast.asset_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(ast.no_seri) LIKE '%{$searchTerm}%'
                         OR LOWER(loc.lokasi_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(jp.frekuensi) LIKE '%{$searchTerm}%'
                         OR LOWER(jp.jenis) LIKE '%{$searchTerm}%'
                         OR LOWER(pk.deskripsi) LIKE '%{$searchTerm}%'
                       ) ";
        }
        
        // Panggil DbModel::datatablesQuery dengan parameter yang benar
        $result = DbModel::datatablesQuery($query, $search, $conditionsForDbModel, $isWhereString);
        return $result;
    }
}