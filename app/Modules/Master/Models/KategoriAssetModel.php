<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class KategoriAssetModel extends Model
{
    protected static $navSession;

    /**
     * Konstruktor model, menginisialisasi session.
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
        if (is_null(self::$navSession)) {
            self::$navSession = session(request('n'));
        }
    }

    /**
     * Memuat data untuk datatables dengan filter dan pencarian.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function loadDatatables()
    {
        self::initSession();

        $filters = [];
        $searchTerm = @self::$navSession['search']['data']['term'];
        $activeStatus = @self::$navSession['search']['data']['active_st'];

        // Filter berdasarkan active_st
        if ($activeStatus !== '') {
            $filters[] = "a.active_st = '" . addslashes($activeStatus) . "'";
        }

        // Filter pencarian
        if ($searchTerm !== '') {
            $searchTerm = strtolower($searchTerm);
            $filters[] = "(LOWER(a.kategori_asset_nm) LIKE '%{$searchTerm}%' OR LOWER(a.deskripsi) LIKE '%{$searchTerm}%')";
        }

        // Filter deleted_st
        $filters[] = "a.deleted_st = 0";

        $whereClause = implode(' AND ', $filters);

        $query = "
            SELECT 
                a.kategori_asset_id, 
                a.kategori_asset_nm, 
                a.deskripsi, 
                a.active_st
            FROM 
                mst_kategori_asset a
            WHERE {$whereClause}
        ";

        $searchColumns = ['kategori_asset_id', 'kategori_asset_nm', 'deskripsi'];

        $result = DbModel::datatablesQuery($query, $searchColumns, null, null);
        return response()->json($result);
    }
}