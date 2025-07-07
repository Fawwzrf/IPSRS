<?php

namespace App\Modules\Ipsrs\Models; // Perbaiki namespace: namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogKerjaModel extends Model
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
        // PERBAIKAN: Gunakan nama kolom sesuai skema terbaru
        $query = "SELECT
                    lk.log_kerja_id, lk.tgl_mulai, lk.tgl_selesai, lk.diagnosa, lk.tindakan, lk.hasil,
                    lk.durasi_menit, lk.total_biaya, lk.active_st, lk.deleted_st,
                    ok.order_kerja_id, ok.jenis as order_jenis, ok.status as order_status,
                    ast.asset_nm, ast.no_seri as asset_no_seri, loc.lokasi_nm as asset_lokasi_nm,
                    pgw.pegawai_nm as teknisi_nm -- Nama alias untuk teknisi_pegawai_id
                  FROM log_kerja lk
                  LEFT JOIN order_kerja ok ON lk.order_kerja_id = ok.order_kerja_id
                  LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                  LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                  LEFT JOIN asset ast ON (jp.asset_id = ast.asset_id OR pk.asset_id = ast.asset_id)
                  LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                  LEFT JOIN mst_pegawai pgw ON lk.teknisi_pegawai_id = pgw.pegawai_id"; // PERBAIKAN: Join ke teknisi_pegawai_id

        // Kolom yang dapat dicari oleh DataTables
        // PERBAIKAN: Gunakan nama kolom baru yang sesuai
        $search = [
            'lk.log_kerja_id', 'lk.tgl_mulai', 'lk.tgl_selesai', 'lk.diagnosa', 'lk.tindakan', 'lk.hasil',
            'ok.order_kerja_id', 'ok.jenis', 'ok.status',
            'ast.asset_nm', 'ast.no_seri', 'loc.lokasi_nm',
            'pgw.pegawai_nm'
        ];

        // Kumpulkan semua kondisi WHERE untuk parameter $where (key-value pairs)
        $conditionsForDbModel = []; 
        $conditionsForDbModel[] = 'lk.deleted_st = 0'; // Kondisi dasar

        // Filter dari sesi pencarian
        if (@self::$nav_sess['search']['data']['jenis_filter'] != '') {
            $conditionsForDbModel['ok.jenis'] = @self::$nav_sess['search']['data']['jenis_filter'];
        }
        if (@self::$nav_sess['search']['data']['order_kerja_id'] != '') {
            $conditionsForDbModel['lk.order_kerja_id'] = @self::$nav_sess['search']['data']['order_kerja_id'];
        }
        if (@self::$nav_sess['search']['data']['pegawai_id'] != '') {
            $conditionsForDbModel['lk.teknisi_pegawai_id'] = @self::$nav_sess['search']['data']['pegawai_id']; // Gunakan teknisi_pegawai_id
        }
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForDbModel['lk.active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }
        
        // Kondisi pencarian 'term' sebagai string SQL murni
        $isWhereString = ''; 
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            $isWhereString .= (empty($isWhereString) ? "" : " AND ") . " ( LOWER(lk.log_kerja_id) LIKE '%{$searchTerm}%'
                         OR LOWER(lk.diagnosa) LIKE '%{$searchTerm}%'
                         OR LOWER(lk.tindakan) LIKE '%{$searchTerm}%'
                         OR LOWER(ok.order_kerja_id) LIKE '%{$searchTerm}%'
                         OR LOWER(ok.jenis) LIKE '%{$searchTerm}%'
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