<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class LokasiModel extends Model
{
    protected static $navSession;

    /**
     * Konstruktor model Lokasi, inisialisasi session navigasi.
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
     * Generate lokasi_id baru berdasarkan tipe dan parent.
     *
     * @param string $tipe
     * @param string|null $parentId
     * @return string
     */
    public static function generateLokasiId($tipe, $parentId = null)
    {
        $prefix = $parentId ? $parentId . '.' : '';
        $sql = "SELECT MAX(lokasi_id) AS last_id FROM mst_lokasi WHERE tipe_lokasi = ? AND deleted_st = 0";
        $params = [$tipe];

        if ($parentId) {
            $sql .= " AND parent_lokasi_id = ?";
            $params[] = $parentId;
        } else {
            $sql .= " AND parent_lokasi_id IS NULL";
        }

        $row = DbModel::rawData('row_array', $sql, $params);
        $lastId = $row['last_id'] ?? '';

        if (empty($lastId)) {
            return $prefix . '01';
        }

        $parts = explode('.', $lastId);
        $lastNumber = (int)end($parts);
        $newNumber = $lastNumber + 1;
        return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Load data lokasi untuk datatables dengan filter pencarian.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function loadDatatables()
    {
        self::initSession();

        $filter = "1 = 1";

        $searchData = self::$navSession['search']['data'] ?? [];

        if (!empty($searchData['tipe_lokasi'])) {
            $filter .= " AND a.tipe_lokasi = '" . addslashes($searchData['tipe_lokasi']) . "'";
        }

        if (!empty($searchData['parent_lokasi_id'])) {
            $filter .= " AND a.parent_lokasi_id = '" . addslashes($searchData['parent_lokasi_id']) . "'";
        }

        if (!empty($searchData['active_st'])) {
            $filter .= " AND a.active_st = '" . addslashes($searchData['active_st']) . "'";
        }

        if (!empty($searchData['term'])) {
            $term = strtolower($searchData['term']);
            $filter .= " AND (
                LOWER(a.lokasi_id) LIKE '%$term%' 
                OR LOWER(a.lokasi_nm) LIKE '%$term%'
                OR LOWER(a.deskripsi) LIKE '%$term%'
                OR LOWER(b.lokasi_nm) LIKE '%$term%'
            )";
        }

        $sql = "
            SELECT * FROM (
                SELECT
                    a.lokasi_id,
                    a.lokasi_nm,
                    a.tipe_lokasi,
                    a.parent_lokasi_id,
                    a.deskripsi,
                    a.active_st,
                    a.denah_url,
                    b.lokasi_nm AS parent_lokasi_nm
                FROM mst_lokasi a
                LEFT JOIN mst_lokasi b ON a.parent_lokasi_id = b.lokasi_id
                WHERE $filter AND a.deleted_st = 0
            ) x
        ";

        $searchColumns = ['lokasi_id', 'lokasi_nm', 'tipe_lokasi', 'parent_lokasi_id', 'deskripsi'];
        $where = null;
        $isWhere = null;

        $result = DbModel::datatablesQuery($sql, $searchColumns, $where, $isWhere);
        return response()->json($result);
    }

    // Pertahankan fungsi lain yang sudah ada...
}
