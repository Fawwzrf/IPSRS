<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenerimaanSparepartModel extends Model
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
        // PERBAIKAN: Kolom a.penerimaan_jumlah, a.penerimaan_vendor, a.penerimaan_no_faktur diubah ke nama kolom asli
        $query = "SELECT
                    a.penerimaan_id, a.tgl, a.jumlah, a.harga_satuan,
                    a.vendor, a.no_faktur, a.active_st, a.deleted_st,
                    s.sparepart_nm, s.no_seri
                  FROM trx_penerimaan_sparepart a
                  LEFT JOIN mst_sparepart s ON a.sparepart_id = s.sparepart_id";

        // Kolom yang dapat dicari oleh DataTables
        // PERBAIKAN: Kolom a.penerimaan_tgl, a.penerimaan_vendor, a.penerimaan_no_faktur diubah ke alias yang benar
        $search = [
            'a.penerimaan_id', 'a.tgl', 'a.vendor', 's.sparepart_nm', 's.no_seri', 'a.no_faktur'
        ];

        // --- Kumpulkan kondisi untuk parameter $where (hanya key-value pairs) ---
        $conditionsForWhereParam = []; 
        // Filter dari sesi pencarian
        if (@self::$nav_sess['search']['data']['sparepart_id'] != '') {
            $conditionsForWhereParam['a.sparepart_id'] = @self::$nav_sess['search']['data']['sparepart_id'];
        }
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $conditionsForWhereParam['a.active_st'] = @self::$nav_sess['search']['data']['active_st'];
        }
        
        // --- Kondisi string SQL murni, akan masuk ke parameter $isWhere ---
        $isWhereString = 'a.deleted_st = 0'; // Kondisi dasar (diawali tanpa AND)

        // Filter pencarian 'term'
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $searchTerm = strtolower(@self::$nav_sess['search']['data']['term']);
            // Tambahkan 'AND' di awal karena $isWhereString sudah diawali 'a.deleted_st = 0'
            $isWhereString .= " AND ( LOWER(a.vendor) LIKE '%{$searchTerm}%'
                         OR LOWER(a.no_faktur) LIKE '%{$searchTerm}%'
                         OR LOWER(s.sparepart_nm) LIKE '%{$searchTerm}%'
                         OR LOWER(s.no_seri) LIKE '%{$searchTerm}%'
                       ) ";
        }
        // --- AKHIR PENYESUAIAN KONDISI WHERE ---

        // Panggil DbModel::datatablesQuery dengan parameter yang benar
        // Kondisi key-value ke parameter $where, kondisi string murni ke parameter $isWhere.
        $result = DbModel::datatablesQuery($query, $search, $conditionsForWhereParam, $isWhereString);
        return $result;
    }
}