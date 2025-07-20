<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class LaporanModel extends Model
{
    public function getLaporanKinerjaAset($filter)
    {
        $where = ["a.deleted_st = 0"];
        $bindings = [];

        if (!empty($filter['kategori_asset_id'])) {
            $where[] = "a.kategori_asset_id = ?";
            $bindings[] = $filter['kategori_asset_id'];
        }
        if (!empty($filter['lokasi_id'])) {
            $where[] = "a.lokasi_id = ?";
            $bindings[] = $filter['lokasi_id'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    a.asset_id, a.asset_nm, a.merk, l.lokasi_nm,
                    COUNT(ok.order_kerja_id) as jumlah_ok,
                    SUM(CASE WHEN ok.jenis = 'perbaikan' THEN 1 ELSE 0 END) as jumlah_perbaikan,
                    SUM(CASE WHEN ok.jenis = 'pemeliharaan' THEN 1 ELSE 0 END) as jumlah_pemeliharaan,
                    MAX(ok.tgl_dibuat) as terakhir_ditangani
                FROM asset a
                LEFT JOIN permintaan_komplain pk ON a.asset_id = pk.asset_id
                LEFT JOIN jadwal_pm jp ON a.asset_id = jp.asset_id
                LEFT JOIN order_kerja ok ON pk.permintaan_id = ok.permintaan_id OR jp.jadwal_pm_id = ok.jadwal_pm_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                WHERE {$whereClause}
                GROUP BY a.asset_id, a.asset_nm, a.merk, l.lokasi_nm
                ORDER BY jumlah_perbaikan DESC";

        return DbModel::rawData('result_array', $sql, $bindings);
    }

    public function getLaporanKinerjaTeknisi($filter)
    {
        $where = ["p.jabatan_id = '90'"];
        $bindings = [];

        if (!empty($filter['pegawai_id'])) {
            $where[] = "p.pegawai_id = ?";
            $bindings[] = $filter['pegawai_id'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    p.pegawai_id, p.pegawai_nm,
                    COUNT(pt.penugasan_id) as total_tugas,
                    SUM(CASE WHEN ok.status = 'selesai' THEN 1 ELSE 0 END) as tugas_selesai,
                    AVG(lk.durasi_menit) as rata_rata_durasi
                FROM mst_pegawai p
                LEFT JOIN penugasan_teknisi pt ON p.pegawai_id = pt.pegawai_id
                LEFT JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                LEFT JOIN log_kerja lk ON ok.order_kerja_id = lk.order_kerja_id
                WHERE {$whereClause}
                GROUP BY p.pegawai_id, p.pegawai_nm
                ORDER BY total_tugas DESC";

        return DbModel::rawData('result_array', $sql, $bindings);
    }

    public function getLaporanBiaya($filter)
    {
        $where = ["ok.deleted_st = 0"];
        $bindings = [];

        if (!empty($filter['tgl_start']) && !empty($filter['tgl_end'])) {
            $where[] = "ok.tgl_dibuat BETWEEN ? AND ?";
            $bindings[] = to_date($filter['tgl_start'], '-', 'date');
            $bindings[] = to_date($filter['tgl_end'], '-', 'date');
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    ok.order_kerja_id,
                    ok.tgl_dibuat,
                    a.asset_nm,
                    ok.jenis,
                    (SELECT SUM(ps.jumlah * ps.harga_satuan) FROM penggunaan_sparepart ps WHERE ps.log_kerja_id = lk.log_kerja_id) as total_biaya_sparepart,
                    lk.total_biaya as biaya_lain,
                    COALESCE((SELECT SUM(ps.jumlah * ps.harga_satuan) FROM penggunaan_sparepart ps WHERE ps.log_kerja_id = lk.log_kerja_id), 0) + COALESCE(lk.total_biaya, 0) as total_biaya_ok
                FROM order_kerja ok
                LEFT JOIN log_kerja lk ON ok.order_kerja_id = lk.order_kerja_id
                LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                LEFT JOIN asset a ON pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id
                WHERE {$whereClause}
                ORDER BY ok.tgl_dibuat DESC";

        return DbModel::rawData('result_array', $sql, $bindings);
    }

    public static function getLaporanKinerjaTim($filter = [])
    {
        $where = "pt.deleted_st = 0";
        $bindings = [];

        // Filter tanggal
        if (!empty($filter['tgl_start']) && !empty($filter['tgl_end'])) {
            $where .= " AND DATE(pt.tgl_selesai) BETWEEN ? AND ?";
            $bindings[] = to_date($filter['tgl_start'], '-', 'date');
            $bindings[] = to_date($filter['tgl_end'], '-', 'date');
        }

        // Filter teknisi
        if (!empty($filter['pegawai_id'])) {
            $where .= " AND pt.pegawai_id = ?";
            $bindings[] = $filter['pegawai_id'];
        }

        $sql = "SELECT 
                ok.order_kerja_id,
                p.pegawai_nm as nama_teknisi,
                COALESCE(a1.asset_nm, a2.asset_nm) as nama_aset,
                pk.created_at as waktu_komplain_masuk,
                ok.tgl_dibuat as waktu_ok_dibuat,
                pt.tgl_mulai as waktu_tugas_diterima,
                pt.tgl_selesai as waktu_tugas_selesai,
                TIMESTAMPDIFF(MINUTE, pk.created_at, ok.tgl_dibuat) as durasi_respon_admin,
                TIMESTAMPDIFF(MINUTE, ok.tgl_dibuat, pt.tgl_mulai) as durasi_penerimaan_teknisi,
                TIMESTAMPDIFF(MINUTE, pt.tgl_mulai, pt.tgl_selesai) as durasi_pengerjaan,
                TIMESTAMPDIFF(MINUTE, pk.created_at, pt.tgl_selesai) as durasi_total
            FROM penugasan_teknisi pt
            JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
            JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
            LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
            LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
            LEFT JOIN asset a1 ON pk.asset_id = a1.asset_id
            LEFT JOIN asset a2 ON jp.asset_id = a2.asset_id
            WHERE $where";

        return DbModel::rawData('result_array', $sql, $bindings);
    }
}
