<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class LaporanModel extends Model
{
    // --- DATATABLES KINERJA ASET ---
    public function getDatatablesKinerjaAset($filter, $start, $length, $order, $search)
    {
        \Log::debug('Model Filter:', $filter);
        \Log::debug('Model Search:', ['search' => $search]);

        $where = [
            "a.deleted_st = 0",
            "(pk.deleted_st IS NULL OR pk.deleted_st = 0)",
            "(jp.deleted_st IS NULL OR jp.deleted_st = 0)",
            "(ok.deleted_st IS NULL OR ok.deleted_st = 0)"
        ];
        $bindings = [];

        if (!empty($filter['kategori_asset_id'])) {
            $where[] = "a.kategori_asset_id = ?";
            $bindings[] = $filter['kategori_asset_id'];
        }
        if (!empty($filter['lokasi_id'])) {
            $where[] = "a.lokasi_id = ?";
            $bindings[] = $filter['lokasi_id'];
        }
        if (!empty($search)) {
            $where[] = "(a.asset_nm LIKE ? OR l.lokasi_nm LIKE ? OR a.merk LIKE ? OR ka.kategori_asset_nm LIKE ?)";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        // Order
        $columns = ['a.asset_nm', 'l.lokasi_nm', 'jumlah_ok', 'jumlah_perbaikan', 'jumlah_pemeliharaan', 'terakhir_ditangani'];
        $orderBy = "jumlah_perbaikan DESC";
        if (!empty($order) && isset($order[0]['column'])) {
            $colIdx = $order[0]['column'] - 1;
            if (isset($columns[$colIdx])) {
                $orderBy = $columns[$colIdx] . ' ' . strtoupper($order[0]['dir']);
            }
        }

        // Count total
        $sqlCount = "SELECT COUNT(*) as total FROM mst
        _asset a WHERE a.deleted_st = 0";
        $total = DbModel::rawData('row_array', $sqlCount)['total'] ?? 0;

        // Query data
        $sql = "SELECT 
            a.asset_id, 
            a.asset_nm, 
            a.merk,          
            ka.kategori_asset_nm,
            l.lokasi_nm,
            COUNT(ok.order_kerja_id) as jumlah_ok,
            SUM(CASE WHEN ok.jenis = 'perbaikan' THEN 1 ELSE 0 END) as jumlah_perbaikan,
            SUM(CASE WHEN ok.jenis = 'pemeliharaan' THEN 1 ELSE 0 END) as jumlah_pemeliharaan,
            MAX(ok.tgl_dibuat) as terakhir_ditangani
        FROM mst_asset a
        LEFT JOIN permintaan_komplain pk ON a.asset_id = pk.asset_id
        LEFT JOIN trx_jadwal_pm jp ON a.asset_id = jp.asset_id
        LEFT JOIN trx_order_kerja ok ON pk.permintaan_id = ok.permintaan_id OR jp.jadwal_pm_id = ok.jadwal_pm_id
        LEFT JOIN mst_kategori_asset ka ON a.kategori_asset_id = ka.kategori_asset_id
        LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
        WHERE {$whereClause}
        GROUP BY a.asset_id, a.asset_nm, a.merk, ka.kategori_asset_nm, l.lokasi_nm
        ORDER BY {$orderBy}
        LIMIT {$length} OFFSET {$start}";

        $data = DbModel::rawData('result_array', $sql, $bindings);
        $filtered = is_array($data) ? count($data) : 0;

        return [
            'draw' => intval(request('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ];
    }

    // --- DATATABLES KINERJA TEKNISI ---
    public function getDatatablesKinerjaTeknisi($filter, $start, $length, $order, $search)
    {
        $where = [
            "p.jabatan_id = '90'",
            "p.deleted_st = 0",
            "(pt.deleted_st IS NULL OR pt.deleted_st = 0)",
            "(ok.deleted_st IS NULL OR ok.deleted_st = 0)",
            "(lk.deleted_st IS NULL OR lk.deleted_st = 0)"
        ];
        $bindings = [];

        if (!empty($filter['pegawai_id'])) {
            $where[] = "p.pegawai_id = ?";
            $bindings[] = $filter['pegawai_id'];
        }
        if (!empty($search)) {
            $where[] = "(p.pegawai_nm LIKE ?)";
            $bindings[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $columns = ['p.pegawai_nm', 'total_tugas', 'tugas_selesai', 'rata_rata_durasi'];
        $orderBy = "total_tugas DESC";
        if (!empty($order) && isset($order[0]['column'])) {
            $colIdx = $order[0]['column'] - 1;
            if (isset($columns[$colIdx])) {
                $orderBy = $columns[$colIdx] . ' ' . strtoupper($order[0]['dir']);
            }
        }

        $sqlCount = "SELECT COUNT(*) as total FROM mst_pegawai p WHERE p.jabatan_id = '90' AND p.deleted_st = 0";
        $total = DbModel::rawData('row_array', $sqlCount)['total'] ?? 0;

        $sql = "SELECT 
                    p.pegawai_id, p.pegawai_nm,
                    COUNT(pt.penugasan_id) as total_tugas,
                    SUM(CASE WHEN ok.status = 'selesai' THEN 1 ELSE 0 END) as tugas_selesai,
                    AVG(lk.durasi_menit) as rata_rata_durasi
                FROM mst_pegawai p
                LEFT JOIN penugasan_teknisi pt ON p.pegawai_id = pt.pegawai_id
                LEFT JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                LEFT JOIN trx_log_kerja lk ON ok.order_kerja_id = lk.order_kerja_id
                WHERE {$whereClause}
                GROUP BY p.pegawai_id, p.pegawai_nm
                ORDER BY {$orderBy}
                LIMIT {$length} OFFSET {$start}";

        $data = DbModel::rawData('result_array', $sql, $bindings);
        $filtered = count($data);

        $mappedData = [];
        foreach ($data as $row) {
            $persentase = ($row['total_tugas'] > 0) ? round(($row['tugas_selesai'] / $row['total_tugas']) * 100, 2) : 0;
            $mappedData[] = [
                'nama_teknisi' => $row['pegawai_nm'],
                'total_tugas' => $row['total_tugas'],
                'tugas_selesai' => $row['tugas_selesai'],
                'persentase_selesai' => $persentase . '%',
                'rata_rata_durasi' => $row['rata_rata_durasi'],
            ];
        }

        return [
            'draw' => intval(request('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $mappedData
        ];
    }

    // --- DATATABLES BIAYA PEMELIHARAAN ---
    public function getDatatablesBiayaPemeliharaan($filter, $start, $length, $order, $search)
    {
        $where = [
            "ok.deleted_st = 0",
            "(lk.deleted_st IS NULL OR lk.deleted_st = 0)",
            "((a1.deleted_st IS NULL OR a1.deleted_st = 0) OR (a2.deleted_st IS NULL OR a2.deleted_st = 0))"
        ];
        $bindings = [];

        if (!empty($filter['tgl_start']) && !empty($filter['tgl_end'])) {
            $where[] = "ok.tgl_dibuat BETWEEN ? AND ?";
            $bindings[] = to_date((string)$filter['tgl_start'], '-', 'date');
            $bindings[] = to_date((string)$filter['tgl_end'], '-', 'date');
        }
        if (!empty($search)) {
            $where[] = "(COALESCE(a1.asset_nm, a2.asset_nm) LIKE ? OR ok.order_kerja_id LIKE ? OR ok.jenis LIKE ?)";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $columns = ['ok.tgl_dibuat', 'ok.order_kerja_id', 'asset_nm', 'ok.jenis', 'total_biaya_sparepart', 'biaya_lain', 'total_biaya_ok'];
        $orderBy = "ok.tgl_dibuat DESC";
        if (!empty($order) && isset($order[0]['column'])) {
            $colIdx = $order[0]['column'] - 1;
            if (isset($columns[$colIdx])) {
                $orderBy = $columns[$colIdx] . ' ' . strtoupper($order[0]['dir']);
            }
        }

        $sqlCount = "SELECT COUNT(*) as total FROM trx_order_kerja ok WHERE ok.deleted_st = 0";
        $total = DbModel::rawData('row_array', $sqlCount)['total'] ?? 0;

        $sql = "SELECT 
            ok.order_kerja_id,
            ok.tgl_dibuat,
            COALESCE(a1.asset_nm, a2.asset_nm) as asset_nm,
            ok.jenis,
            (SELECT SUM(ps.jumlah * ps.harga_satuan) FROM penggunaan_sparepart ps WHERE ps.log_kerja_id = lk.log_kerja_id) as total_biaya_sparepart,
            lk.total_biaya as biaya_lain,
            COALESCE((SELECT SUM(ps.jumlah * ps.harga_satuan) FROM penggunaan_sparepart ps WHERE ps.log_kerja_id = lk.log_kerja_id), 0) + COALESCE(lk.total_biaya, 0) as total_biaya_ok
        FROM trx_order_kerja ok
        LEFT JOIN trx_log_kerja lk ON ok.order_kerja_id = lk.order_kerja_id
        LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
        LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
        LEFT JOIN mst_asset a1 ON pk.asset_id = a1.asset_id
        LEFT JOIN mst_asset a2 ON jp.asset_id = a2.asset_id
        WHERE {$whereClause}
        ORDER BY {$orderBy}
        LIMIT {$length} OFFSET {$start}";

        $data = DbModel::rawData('result_array', $sql, $bindings);

        $filtered = count($data);

        // Hitung total biaya sesuai filter (gunakan filter yang sama!)
        $total_biaya = $this->getTotalBiayaPemeliharaan($filter);

        return [
            'draw' => intval(request('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
            'total_biaya' => $total_biaya // <-- tambahkan ini
        ];
    }

    // --- DATATABLES KINERJA TIM ---
    public function getDatatablesKinerjaTim($filter, $start, $length, $order, $search)
    {
        $where = "pt.deleted_st = 0 AND ok.deleted_st = 0 AND (a1.deleted_st IS NULL OR a1.deleted_st = 0) AND (a2.deleted_st IS NULL OR a2.deleted_st = 0)";
        $bindings = [];

        if (!empty($filter['tgl_start']) && !empty($filter['tgl_end'])) {
            $where .= " AND DATE(pt.tgl_selesai) BETWEEN ? AND ?";
            $bindings[] = to_date($filter['tgl_start'], '-', 'date');
            $bindings[] = to_date($filter['tgl_end'], '-', 'date');
        }
        if (!empty($filter['pegawai_id'])) {
            $where .= " AND pt.pegawai_id = ?";
            $bindings[] = $filter['pegawai_id'];
        }
        if (!empty($search)) {
            $where .= " AND (ok.order_kerja_id LIKE ? OR p.pegawai_nm LIKE ? OR ok.jenis LIKE ? OR COALESCE(a1.asset_nm, a2.asset_nm) LIKE ?)";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $columns = ['ok.order_kerja_id', 'ok.jenis', 'p.pegawai_nm', 'nama_aset', 'durasi_respon_admin', 'durasi_penerimaan_teknisi', 'durasi_pengerjaan', 'durasi_total'];
        $orderBy = "pt.tgl_selesai DESC";
        if (!empty($order) && isset($order[0]['column'])) {
            $colIdx = $order[0]['column'] - 1;
            if (isset($columns[$colIdx])) {
                $orderBy = $columns[$colIdx] . ' ' . strtoupper($order[0]['dir']);
            }
        }

        $sqlCount = "SELECT COUNT(*) as total FROM penugasan_teknisi pt JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id WHERE pt.deleted_st = 0 AND ok.deleted_st = 0";
        $total = DbModel::rawData('row_array', $sqlCount)['total'] ?? 0;

        $sql = "SELECT 
            ok.order_kerja_id,
            ok.jenis,
            p.pegawai_nm as nama_teknisi,
            COALESCE(a1.asset_nm, a2.asset_nm) as nama_aset,
            IF(ok.jenis = 'Pemeliharaan', jp.tgl_terakhir, pk.created_at) as waktu_awal,
            ok.tgl_dibuat as waktu_ok_dibuat,
            pt.tgl_mulai as waktu_tugas_diterima,
            pt.tgl_selesai as waktu_tugas_selesai,
            TIMESTAMPDIFF(MINUTE, IF(ok.jenis = 'Pemeliharaan', jp.tgl_terakhir, pk.created_at), ok.created_at) as durasi_respon_admin,
            TIMESTAMPDIFF(MINUTE, ok.created_at, pt.tgl_mulai) as durasi_penerimaan_teknisi,
            TIMESTAMPDIFF(MINUTE, pt.tgl_mulai, pt.tgl_selesai) as durasi_pengerjaan,
            TIMESTAMPDIFF(MINUTE, IF(ok.jenis = 'Pemeliharaan', jp.tgl_terakhir, pk.created_at), pt.tgl_selesai) as durasi_total
        FROM penugasan_teknisi pt
        JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
        JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
        LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
        LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
        LEFT JOIN mst_asset a1 ON pk.asset_id = a1.asset_id
        LEFT JOIN mst_asset a2 ON jp.asset_id = a2.asset_id
        WHERE {$where}
        ORDER BY {$orderBy}
        LIMIT {$length} OFFSET {$start}";

        $data = DbModel::rawData('result_array', $sql, $bindings);
        $filtered = count($data);

        return [
            'draw' => intval(request('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ];
    }

    // --- TOTAL BIAYA PEMELIHARAAN ---
    public function getTotalBiayaPemeliharaan($filter)
    {
        $where = [
            "ok.deleted_st = 0",
            "(lk.deleted_st IS NULL OR lk.deleted_st = 0)",
            "((a1.deleted_st IS NULL OR a1.deleted_st = 0) OR (a2.deleted_st IS NULL OR a2.deleted_st = 0))"
        ];
        $bindings = [];

        // Filter tanggal sesuai periode
        if (!empty($filter['tgl_start']) && !empty($filter['tgl_end'])) {
            $where[] = "DATE(ok.tgl_dibuat) BETWEEN ? AND ?";
            $bindings[] = function_exists('to_date') ? to_date((string)$filter['tgl_start'], '-', 'date') : $filter['tgl_start'];
            $bindings[] = function_exists('to_date') ? to_date((string)$filter['tgl_end'], '-', 'date') : $filter['tgl_end'];
        }

        // Filter pencarian aset atau order kerja
        if (!empty($filter['search'])) {
            $where[] = "(COALESCE(a1.asset_nm, a2.asset_nm) LIKE ? OR ok.order_kerja_id LIKE ? OR ok.jenis LIKE ?)";
            $search = $filter['search'];
            if (is_array($search)) {
                $search = implode(' ', $search);
            }
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
        SUM(
            COALESCE((SELECT SUM(ps.jumlah * ps.harga_satuan) FROM penggunaan_sparepart ps WHERE ps.log_kerja_id = lk.log_kerja_id), 0) 
            + COALESCE(lk.total_biaya, 0)
        ) as total_biaya
        FROM trx_order_kerja ok
        LEFT JOIN trx_log_kerja lk ON ok.order_kerja_id = lk.order_kerja_id
        LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
        LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
        LEFT JOIN mst_asset a1 ON pk.asset_id = a1.asset_id
        LEFT JOIN mst_asset a2 ON jp.asset_id = a2.asset_id
        WHERE {$whereClause}";

        $row = DbModel::rawData('row_array', $sql, $bindings);
        return (float) ($row['total_biaya'] ?? 0);
    }

    // --- RATA-RATA PENYELESAIAN TIM ---
    public function getRataRataPenyelesaianTim($filter)
    {
        $where = "pt.deleted_st = 0 AND ok.deleted_st = 0";
        $bindings = [];

        $search = $filter['search'] ?? '';
        if (is_array($search)) {
            $search = implode(' ', $search);
        }

        if (!empty($filter['tgl_start']) && !empty($filter['tgl_end'])) {
            $where .= " AND DATE(pt.tgl_selesai) BETWEEN ? AND ?";
            $bindings[] = to_date($filter['tgl_start'], '-', 'date');
            $bindings[] = to_date($filter['tgl_end'], '-', 'date');
        }
        if (!empty($filter['pegawai_id'])) {
            $where .= " AND pt.pegawai_id = ?";
            $bindings[] = $filter['pegawai_id'];
        }
        if (!empty($search)) {
            $where .= " AND (ok.order_kerja_id LIKE ? OR p.pegawai_nm LIKE ? OR ok.jenis LIKE ? OR COALESCE(a1.asset_nm, a2.asset_nm) LIKE ?)";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, 
                IF(ok.jenis = 'Pemeliharaan', jp.tgl_terakhir, pk.created_at), pt.tgl_selesai)
            ) as rata_rata_penyelesaian
        FROM penugasan_teknisi pt
        JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
        JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
        LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
        LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
        LEFT JOIN mst_asset a1 ON pk.asset_id = a1.asset_id
        LEFT JOIN mst_asset a2 ON jp.asset_id = a2.asset_id
        WHERE $where";
        $row = DbModel::rawData('row_array', $sql, $bindings);
        return round($row['rata_rata_penyelesaian'] ?? 0, 2);
    }
}
