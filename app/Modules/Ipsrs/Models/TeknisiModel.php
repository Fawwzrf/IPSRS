<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeknisiModel extends Model
{
    /**
     * Menghitung jumlah tugas dengan status tertentu menggunakan Raw Query.
     */
    public function getCountTugasByStatus($teknisi_id, $status)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM penugasan_teknisi 
                WHERE pegawai_id = ? AND status = ? AND deleted_st = 0";

        $result = DbModel::rawData('row_array', $sql, [$teknisi_id, $status]);
        return $result['total'] ?? 0;
    }

    /**
     * Mengambil daftar tugas dengan status tertentu menggunakan Raw Query.
     */
    public function getListTugasByStatus($teknisi_id, $status, $limit = null)
    {
        $sql = "SELECT pt.penugasan_id,pt.catatan_penolakan, ok.order_kerja_id, a.asset_id, a.asset_nm, l.lokasi_nm, ok.jenis,ok.prioritas,ok.permintaan_id,
        ok.jadwal_pm_id, ok.tgl_dibuat,
                       COALESCE(pk.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                FROM penugasan_teknisi pt
                JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                LEFT JOIN asset a ON pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                WHERE pt.pegawai_id = ? AND pt.status = ? AND pt.deleted_st = 0
                ORDER BY ok.tgl_dibuat DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        return DbModel::rawData('result_array', $sql, [$teknisi_id, $status]);
    }

    public function getDetailTugas($penugasan_id)
    {
        $sql = "SELECT 
                    pt.*,
                    ok.order_kerja_id, 
                    a.asset_id, a.asset_nm, 
                    l.lokasi_nm, 
                    ok.jenis, 
                    ok.tgl_dibuat,
                    pk.deskripsi,
                    pk.anotasi_url,
                    pelapor.pegawai_nm as nama_pelapor,
                    jp.frekuensi,
                    jp.jenis as jenis_pemeliharaan,
                    jp.tgl_berikutnya as tgl_pemeliharaan,
                    jp.deskripsi as deskripsi_pemeliharaan
                FROM penugasan_teknisi pt
                JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                LEFT JOIN asset a ON pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                LEFT JOIN mst_pegawai pelapor ON pk.pegawai_id = pelapor.pegawai_id
                WHERE pt.penugasan_id = ? AND pt.deleted_st = 0";

        return DbModel::rawData('row_array', $sql, [$penugasan_id]);
    }

    public function updateStatusPenugasan($penugasan_id, $status_baru, $alasan = null)
    {
        try {
            DB::beginTransaction();

            // 1. Ambil data penugasan
            $penugasan = DbModel::rawData('row_array', "SELECT * FROM penugasan_teknisi WHERE penugasan_id = ?", [$penugasan_id]);
            if (!$penugasan) {
                throw new \Exception("Data penugasan tidak ditemukan.");
            }

            $order_kerja_id = $penugasan['order_kerja_id'];

            // 2. Update penugasan teknisi
            $updateData = ['status' => $status_baru];
            if ($alasan !== null) {
                $updateData['catatan_penolakan'] = $alasan;
            }
            DbModel::updateData('penugasan_teknisi', $updateData, ['penugasan_id' => $penugasan_id]);

            // 3. Logika update status order_kerja utama
            if ($status_baru == 'sedang_dikerjakan') {
                DbModel::updateData('order_kerja', ['status' => 'diproses'], ['order_kerja_id' => $order_kerja_id]);
            } else if ($status_baru == 'dibatalkan') {

                // Hitung teknisi yang masih aktif (status 'ditugaskan' atau 'sedang_dikerjakan')
                $sql_teknisi_aktif = "SELECT COUNT(*) as total FROM penugasan_teknisi WHERE order_kerja_id = ? AND status IN ('ditugaskan', 'sedang_dikerjakan') AND deleted_st = 0";
                $result = DbModel::rawData('row_array', $sql_teknisi_aktif, [$order_kerja_id]);
                $teknisi_aktif = $result['total'] ?? 0;

                // Jika TIDAK ADA lagi teknisi yang aktif
                if ($teknisi_aktif == 0) {
                    // **Kembalikan status Order Kerja ke 'baru'**
                    DbModel::updateData('order_kerja', ['status' => 'baru'], ['order_kerja_id' => $order_kerja_id]);
                }
            } else if ($status_baru == 'ditugaskan') {
                // Jika ada penugasan baru, pastikan status OK adalah 'ditugaskan'
                DbModel::updateData('order_kerja', ['status' => 'ditugaskan'], ['order_kerja_id' => $order_kerja_id]);
            }

            DB::commit();
            return ['success' => true, 'msg' => 'Status berhasil diperbarui.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'msg' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }
}
