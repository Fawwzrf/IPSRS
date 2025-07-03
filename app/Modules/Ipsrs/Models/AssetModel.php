<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;


class AssetModel extends Model
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
        self::initSession(); // Inisialisasi sesi


        $whereClause = "1 = 1 ";

        if (@self::$nav_sess['search']['data']['lokasi_id'] != '') {
            $whereClause .= " AND a.lokasi_id = '" . @self::$nav_sess['search']['data']['lokasi_id'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['kategori_asset_id'] != '') {
            $whereClause .= " AND a.kategori_asset_id = '" . @self::$nav_sess['search']['data']['kategori_asset_id'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $whereClause .= " AND a.status = '" . @self::$nav_sess['search']['data']['status'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['active_st'] != '') {
            $whereClause .= " AND a.active_st = '" . @self::$nav_sess['search']['data']['active_st'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $whereClause .= " AND LOWER(a.asset_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' ";
        }


        $query = "SELECT
                    *
                  FROM (
                    SELECT
                        a.asset_id, a.asset_nm, a.jenis, a.no_seri, a.merk, a.model, a.status, a.active_st,
                        l.lokasi_nm,
                        k.kategori_asset_nm,
                        a.perolehan_tgl, 
                        a.pm_berikutnya 
                    FROM asset a
                    LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                    LEFT JOIN mst_kategori_asset k ON a.kategori_asset_id = k.kategori_asset_id
                    WHERE a.deleted_st = 0 AND " . $whereClause . "
                  ) x";

        $search = [
            'asset_id',
            'asset_nm',
            'no_seri',
            'merk',
            'model',
            'lokasi_nm',
            'kategori_asset_nm',
            'perolehan_tgl',
            'pm_berikutnya'
        ];
        $where = null;
        $isWhere = null;

        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return $result;
    }

    static function getAssetByNoSeri($noSeri)
    {
        $sql = "SELECT
                a.*,
                l.lokasi_nm,
                l.denah_url,
                l.tipe_lokasi,
                l.parent_lokasi_id,
                k.kategori_asset_nm
            FROM asset a
            LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
            LEFT JOIN mst_kategori_asset k ON a.kategori_asset_id = k.kategori_asset_id
            WHERE a.no_seri = ? AND a.active_st = 1 AND a.deleted_st = 0";
        $result = DbModel::rawData('row_array', $sql, [$noSeri]);

        if ($result && isset($result['lokasi_id'])) {
            $locationPath = self::getLokasiPath($result['lokasi_id']);
            $result['lokasi_path'] = $locationPath;
        }
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
