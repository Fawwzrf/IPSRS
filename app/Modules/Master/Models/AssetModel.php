<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class AssetModel extends Model
{
    protected static $nav_sess;

    // Status aset
    const STATUS_AKTIF = 'aktif';
    const STATUS_PERBAIKAN = 'perbaikan';
    const STATUS_NONAKTIF = 'nonaktif';
    const STATUS_DIHAPUS = 'dihapus';

    // Status yang valid
    protected static $valid_statuses = [
        self::STATUS_AKTIF,
        self::STATUS_PERBAIKAN,
        self::STATUS_NONAKTIF,
        self::STATUS_DIHAPUS
    ];

    /**
     * Konstruktor kelas AssetModel
     */
    public function __construct()
    {
        parent::__construct();
        self::initSession();
    }

    /**
     * Inisialisasi session navigasi
     */
    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    /**
     * Load data aset untuk datatables dengan filter
     */
    static function loadDatatables()
    {
        self::initSession();
        
        $where = "1 = 1 ";

        if (@self::$nav_sess['search']['data']['lokasi_id'] != '') {
            $where .= " AND a.lokasi_id = '" . @self::$nav_sess['search']['data']['lokasi_id'] . "' ";
        }
        
        if (@self::$nav_sess['search']['data']['kategori_asset_id'] != '') {
            $where .= " AND a.kategori_asset_id = '" . @self::$nav_sess['search']['data']['kategori_asset_id'] . "' ";
        }
        
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $where .= " AND a.status = '" . @self::$nav_sess['search']['data']['status'] . "' ";
        }
        
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (LOWER(a.asset_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                      OR LOWER(a.no_seri) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(a.merk) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%') ";
        }

        $query = "SELECT * FROM (
                  SELECT 
                    a.*,
                    b.kategori_asset_nm,
                    c.lokasi_nm
                  FROM 
                    asset a
                    LEFT JOIN mst_kategori_asset b ON a.kategori_asset_id = b.kategori_asset_id
                    LEFT JOIN mst_lokasi c ON a.lokasi_id = c.lokasi_id
                  WHERE $where AND a.deleted_st = 0
                ) x ";
                
        $search = ['asset_id', 'asset_nm', 'no_seri', 'merk', 'kategori_asset_nm', 'lokasi_nm'];
        $where = null;
        $isWhere = null;
        
        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }

    /**
     * Ambil detail aset berdasarkan ID
     */
    public function getAssetDetailById($id)
    {
        $query = "SELECT a.*, k.kategori_asset_nm, l.lokasi_nm
                  FROM asset a
                  LEFT JOIN mst_kategori_asset k ON a.kategori_asset_id = k.kategori_asset_id
                  LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                  WHERE a.asset_id = ? AND a.deleted_st = 0";
        return DbModel::rawData('row_array', $query, [$id]);
    }

    /**
     * Ambil histori aset berdasarkan asset_id
     */
    public function getAssetHistory($asset_id)
    {
        $query = "SELECT 
                    ok.order_kerja_id,
                    ok.tgl_dibuat,
                    ok.jenis,
                    ok.status,
                    COALESCE(pk.deskripsi, 'Pemeliharaan Rutin Sesuai Jadwal') as deskripsi,
                    (SELECT GROUP_CONCAT(p.pegawai_nm SEPARATOR ', ') 
                     FROM penugasan_teknisi pt 
                     JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id 
                     WHERE pt.order_kerja_id = ok.order_kerja_id AND pt.deleted_st = 0) as tim_teknisi,
                    (SELECT MIN(tgl_mulai) FROM penugasan_teknisi WHERE order_kerja_id = ok.order_kerja_id) as tgl_mulai,
                    (SELECT MAX(tgl_selesai) FROM penugasan_teknisi WHERE order_kerja_id = ok.order_kerja_id) as tgl_selesai
                  FROM order_kerja ok
                  LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                  LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                  WHERE (pk.asset_id = ? OR jp.asset_id = ?) AND ok.deleted_st = 0
                  ORDER BY ok.tgl_dibuat DESC";
        return DbModel::rawData('result_array', $query, [$asset_id, $asset_id]);
    }

    /**
     * Ambil daftar status aset yang valid
     */
    public static function getValidStatuses()
    {
        return self::$valid_statuses;
    }

    /**
     * Set status aset menjadi perbaikan
     */
    public function setRepairStatus($asset_id)
    {
        return $this->updateStatus($asset_id, self::STATUS_PERBAIKAN);
    }

    /**
     * Set status aset menjadi aktif
     */
    public function setActiveStatus($asset_id)
    {
        return $this->updateStatus($asset_id, self::STATUS_AKTIF);
    }
}

