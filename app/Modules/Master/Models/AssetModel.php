<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;


class AssetModel extends Model
{
    protected static $nav_sess;
    // Konstanta status aset untuk konsistensi
    const STATUS_AKTIF = 'aktif';
    const STATUS_PERBAIKAN = 'perbaikan';
    const STATUS_NONAKTIF = 'nonaktif';
    const STATUS_DIHAPUS = 'dihapus';
    
    // Array status yang valid untuk validasi
    protected static $valid_statuses = [
        self::STATUS_AKTIF, 
        self::STATUS_PERBAIKAN, 
        self::STATUS_NONAKTIF, 
        self::STATUS_DIHAPUS
    ];
    
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
    
    /**
     * Memperbarui status aset dengan konsistensi active_st dan deleted_st
     * 
     * @param string $asset_id ID aset yang akan diperbarui
     * @param string $status Status baru ('aktif', 'perbaikan', 'nonaktif', 'dihapus')
     * @return array Hasil operasi
     */
    public function updateStatus($asset_id, $status)
    {
        // Validasi status yang valid
        if (!in_array($status, self::$valid_statuses)) {
            return ['status' => false, 'message' => 'Status tidak valid. Status yang diperbolehkan: ' . implode(', ', self::$valid_statuses)];
        }
        
        // Tentukan nilai active_st dan deleted_st berdasarkan status
        $active_st = 0;
        $deleted_st = 0;
        
        switch ($status) {
            case self::STATUS_AKTIF:
            case self::STATUS_PERBAIKAN:
                $active_st = 1;
                break;
            case self::STATUS_NONAKTIF:
                $active_st = 0;
                break;
            case self::STATUS_DIHAPUS:
                $active_st = 0;
                $deleted_st = 1;
                break;
        }
        
        // Update status aset
        $data = [
            'status' => $status,
            'active_st' => $active_st,
            'deleted_st' => $deleted_st
        ];
        
        $result = DbModel::updateData('asset', $data, ['asset_id' => $asset_id]);
        
        if ($result) {
            // Log perubahan status jika diperlukan
            $log_data = [
                'asset_id' => $asset_id,
                'status_lama' => DbModel::getData('asset', ['asset_id' => $asset_id])['status'] ?? '',
                'status_baru' => $status,
                'created_by' => auth()->id() ?? 0,
                'created_at' => now()
            ];
            
            // Jika ada tabel log_status_asset, gunakan ini:
            // DbModel::insertData('log_status_asset', $log_data);
            
            return ['status' => true, 'message' => 'Status aset berhasil diperbarui'];
        }
        
        return ['status' => false, 'message' => 'Gagal memperbarui status aset'];
    }
    
    /**
     * Mengambil semua status valid untuk aset
     * 
     * @return array Array status valid
     */
    public static function getValidStatuses()
    {
        return self::$valid_statuses;
    }

    /**
     * Memperbarui status aset menjadi perbaikan
     * 
     * @param string $asset_id ID aset
     * @return array Hasil operasi
     */
    public function setRepairStatus($asset_id)
    {
        return $this->updateStatus($asset_id, self::STATUS_PERBAIKAN);
    }
    
    /**
     * Memperbarui status aset menjadi aktif
     * 
     * @param string $asset_id ID aset
     * @return array Hasil operasi
     */
    public function setActiveStatus($asset_id)
    {
        return $this->updateStatus($asset_id, self::STATUS_AKTIF);
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
     * FUNGSI BARU: Mengambil riwayat Order Kerja untuk satu aset.
     */
    public function getAssetHistory($asset_id)
    {
        $query = "SELECT 
                    ok.order_kerja_id,
                    ok.tgl_dibuat,
                    ok.jenis,
                    ok.status,
                    COALESCE(pk.deskripsi, 'Pemeliharaan Rutin Sesuai Jadwal') as deskripsi, -- Nama alias diubah menjadi 'deskripsi'
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
