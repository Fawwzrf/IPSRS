<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class SparepartModel extends Model
{
    protected static $nav_sess;

    /**
     * Konstruktor model, inisialisasi session navigasi.
     */
    public function __construct()
    {
        parent::__construct();
        self::initSession();
    }

    /**
     * Inisialisasi session navigasi jika belum ada.
     */
    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    /**
     * Memuat data untuk datatables dengan filter dan pencarian.
     * @return \Illuminate\Http\JsonResponse
     */
    static function loadDatatables()
    {
        self::initSession();

        $filters = [];
        $params = [];

        // Filter status aktif
        $activeSt = @self::$nav_sess['search']['data']['active_st'];
        if ($activeSt !== '') {
            $filters[] = "a.active_st = ?";
            $params[] = $activeSt;
        }

        // Filter pencarian
        $term = @self::$nav_sess['search']['data']['term'];
        if ($term !== '') {
            $likeTerm = '%' . strtolower($term) . '%';
            $filters[] = "(" .
                "LOWER(a.sparepart_id) LIKE ? OR " .
                "LOWER(a.sparepart_nm) LIKE ? OR " .
                "LOWER(a.no_seri) LIKE ? OR " .
                "LOWER(a.merk) LIKE ? OR " .
                "LOWER(a.lokasi_penyimpanan) LIKE ?" .
            ")";
            $params = array_merge($params, array_fill(0, 5, $likeTerm));
        }

        // Always filter deleted_st
        $filters[] = "a.deleted_st = 0";

        $where = implode(' AND ', $filters);

        $query = "SELECT * FROM (
                    SELECT 
                        a.sparepart_id, a.sparepart_nm, a.no_seri, a.merk, a.satuan, 
                        a.harga, a.stok, a.lokasi_penyimpanan, a.active_st
                    FROM 
                        mst_sparepart a
                    WHERE $where
                ) x ";

        $search = ['sparepart_id', 'sparepart_nm', 'no_seri', 'merk', 'lokasi_penyimpanan'];
        $where = null;
        $isWhere = null;

        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere, $params);
        return response()->json($result);
    }
}

