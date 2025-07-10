<?php

namespace App\Modules\Master\Models;

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

        // 1. Query dasar tanpa klausa WHERE
        $query = "SELECT
                a.asset_id, a.asset_nm, a.jenis, a.no_seri, a.merk, a.model, a.status, a.active_st,
                l.lokasi_nm,
                k.kategori_asset_nm,
                a.perolehan_tgl,
                a.pm_berikutnya
            FROM asset a
            LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
            LEFT JOIN mst_kategori_asset k ON a.kategori_asset_id = k.kategori_asset_id";

        // 2. Kolom yang dapat dicari oleh DataTables (search box bawaan)
        $searchableColumns = [
            'a.asset_nm',
            'a.no_seri',
            'a.merk',
            'a.model',
            'l.lokasi_nm',
            'k.kategori_asset_nm'
        ];

        // 3. Kumpulkan kondisi WHERE dalam bentuk array (lebih aman dari SQL Injection)
        $whereConditions = [];
        $whereConditions['a.deleted_st'] = 0; // Kondisi wajib

        // Tambahkan filter dari sesi pencarian
        if (!empty(self::$nav_sess['search']['data']['lokasi_id'])) {
            $whereConditions['a.lokasi_id'] = self::$nav_sess['search']['data']['lokasi_id'];
        }
        if (!empty(self::$nav_sess['search']['data']['kategori_asset_id'])) {
            $whereConditions['a.kategori_asset_id'] = self::$nav_sess['search']['data']['kategori_asset_id'];
        }
        if (!empty(self::$nav_sess['search']['data']['status'])) {
            $whereConditions['a.status'] = self::$nav_sess['search']['data']['status'];
        }
        if (isset(self::$nav_sess['search']['data']['active_st']) && self::$nav_sess['search']['data']['active_st'] !== '') {
            $whereConditions['a.active_st'] = self::$nav_sess['search']['data']['active_st'];
        }

        // 4. Buat string WHERE terpisah untuk pencarian teks (LIKE)
        $whereString = '';
        if (!empty(self::$nav_sess['search']['data']['term'])) {
            $searchTerm = strtolower(self::$nav_sess['search']['data']['term']);
            // PERBAIKAN: Pencarian diperluas ke beberapa kolom relevan
            $whereString = "
                (LOWER(a.asset_nm) LIKE '%{$searchTerm}%'
                OR LOWER(a.no_seri) LIKE '%{$searchTerm}%'
                OR LOWER(a.merk) LIKE '%{$searchTerm}%'
                OR LOWER(a.model) LIKE '%{$searchTerm}%'
                OR LOWER(l.lokasi_nm) LIKE '%{$searchTerm}%'
                OR LOWER(k.kategori_asset_nm) LIKE '%{$searchTerm}%')
            ";
        }

        // 5. Panggil fungsi datatables dengan parameter yang sudah terstruktur
        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, $whereString);

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
