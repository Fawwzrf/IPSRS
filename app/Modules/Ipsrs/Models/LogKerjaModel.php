<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogKerjaModel extends Model
{
    protected static $nav_sess = [];

    public function saveData($order_kerja_id, $data)
    {
        // Gunakan teknisi_pegawai_id dari input jika ada (admin), jika tidak pakai Auth::id()
        $pegawai_id = $data['teknisi_pegawai_id'] ?? (\Illuminate\Support\Facades\Auth::id() ?: null);
        $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
        if (!$order_kerja) return ['status' => false, 'message' => 'Order Kerja tidak ditemukan.'];

        $logKerjaData = [
            'order_kerja_id' => $order_kerja_id,
            'teknisi_pegawai_id' => $pegawai_id,
            'tgl_mulai' => now(), // Diasumsikan mulai saat laporan dibuat
            'tgl_selesai' => now(),
            'diagnosa' => $data['diagnosa'] ?? 'Sesuai deskripsi Order Kerja',
            'tindakan' => $data['tindakan'],
            'hasil' => $data['hasil'],
            'durasi_menit' => $data['durasi_menit'] ?? 0,
            'total_biaya' => $data['total_biaya'] ?? 0,
        ];

        try {
            \DB::beginTransaction();

            $existingLog = DbModel::getData('log_kerja', ['order_kerja_id' => $order_kerja_id]);
            if ($existingLog) {
                DbModel::updateData('log_kerja', $logKerjaData, ['log_kerja_id' => $existingLog['log_kerja_id']]);
                $log_kerja_id = $existingLog['log_kerja_id'];
            } else {
                $log_kerja_id = DbModel::getId('log_kerja', 2, 12);
                $logKerjaData['log_kerja_id'] = $log_kerja_id;
                DbModel::insertData('log_kerja', $logKerjaData);
            }

            // Proses Upload Foto
            if (isset($_FILES['fotos']) && count($_FILES['fotos']['name']) > 0) {
                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileContent = file_get_contents($tmp_name);
                        $base64Content = base64_encode($fileContent);
                        $mimeType = mime_content_type($tmp_name);
                        $fotoData = [
                            'log_foto_id' => DbModel::getId('log_kerja_foto', 2, 12),
                            'log_kerja_id' => $log_kerja_id,
                            'foto_url' => 'data:' . $mimeType . ';base64,' . $base64Content,
                        ];
                        DbModel::insertData('log_kerja_foto', $fotoData);
                    }
                }
            }

            if (!empty($data['sparepart'])) {
                foreach ($data['sparepart'] as $sp) {
                    $sparepart = DbModel::getData('mst_sparepart', ['sparepart_id' => $sp['sparepart_id']]);
                    if (!$sparepart || $sparepart['stok'] < $sp['jumlah']) {
                        \DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'Stok sparepart ' . ($sparepart['sparepart_nm'] ?? '') . ' tidak mencukupi.'
                        ];
                    }

                    $penggunaanData = [
                        'penggunaan_id' => DbModel::getId('penggunaan_sparepart', 2, 12),
                        'log_kerja_id' => $log_kerja_id, // ID dari log kerja yang baru dibuat
                        'sparepart_id' => $sp['sparepart_id'],
                        'jumlah' => $sp['jumlah'],
                        'harga_satuan' => $sparepart['harga'] ?? 0
                    ];
                    DbModel::insertData('penggunaan_sparepart', $penggunaanData);

                    \DB::table('mst_sparepart')->where('sparepart_id', $sp['sparepart_id'])->decrement('stok', $sp['jumlah']);
                }
            }

            // Logika pembaruan status Order Kerja yang lebih detail
            $status_baru = '';
            if ($data['hasil'] == 'berhasil') {
                $status_baru = 'selesai';
            } elseif ($data['hasil'] == 'perlu_tindak_lanjut') {
                // Periksa apakah memerlukan sparepart
                $status_baru = !empty($data['sparepart']) ? 'menunggu_sparepart' : 'diproses';
            } else {
                // Kasus tidak berhasil
                $status_baru = 'diproses'; // Gunakan status yang tepat dari enum yang distandarisasi
            }

            // Dapatkan status saat ini sebelum diupdate
            $order_current = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
            $status_sebelumnya = $order_current['status'] ?? null;

            // Update status order_kerja
            DbModel::updateData('order_kerja', [
                'status' => $status_baru,
                'updated_at' => now(),
                'updated_by' => $pegawai_id
            ], ['order_kerja_id' => $order_kerja_id]);

            // Catat perubahan status
            $log_status_data = [
                'log_status_id' => DbModel::getId('log_status_order_kerja', 2, 12),
                'order_kerja_id' => $order_kerja_id,
                'status_lama' => $status_sebelumnya,
                'status_baru' => $status_baru,
                'oleh_pegawai_id' => $pegawai_id,
                'catatan' => 'Update dari log kerja: ' . $data['hasil']
            ];
            DbModel::insertData('log_status_order_kerja', $log_status_data);

            // Update sumber asli (jika pekerjaan selesai)
            if ($status_baru == 'selesai') {
                if (!empty($order_current['permintaan_id'])) {
                    DbModel::updateData(
                        'permintaan_komplain',
                        ['status' => 'selesai'],
                        ['permintaan_id' => $order_current['permintaan_id']]
                    );
                }

                if (!empty($order_current['jadwal_pm_id'])) {
                    DbModel::updateData(
                        'jadwal_pm',
                        ['status' => 'selesai'],
                        ['jadwal_pm_id' => $order_current['jadwal_pm_id']]
                    );
                }
            }

            // TAMBAHKAN KODE PEMBARUAN STATUS ASET DI SINI
            // Dapatkan asset_id dari permintaan atau jadwal
            $asset_id = null;
            if (!empty($order_current['permintaan_id'])) {
                $permintaan = DbModel::getData('permintaan_komplain', ['permintaan_id' => $order_current['permintaan_id']]);
                $asset_id = $permintaan['asset_id'] ?? null;
            } elseif (!empty($order_current['jadwal_pm_id'])) {
                $jadwal = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $order_current['jadwal_pm_id']]);
                $asset_id = $jadwal['asset_id'] ?? null;
            }

            if ($asset_id) {
                if ($status_baru == 'diproses' || $status_baru == 'menunggu_sparepart') {
                    // Update status aset menjadi 'perbaikan'
                    DbModel::updateData('asset', ['status' => 'perbaikan'], ['asset_id' => $asset_id]);
                } elseif ($status_baru == 'selesai') {
                    // Cek apakah masih ada order kerja lain yang aktif untuk aset ini
                    $active_orders_query = "SELECT COUNT(*) as jumlah FROM order_kerja 
                                          WHERE (permintaan_id IN (SELECT permintaan_id FROM permintaan_komplain WHERE asset_id = ?) 
                                          OR jadwal_pm_id IN (SELECT jadwal_pm_id FROM jadwal_pm WHERE asset_id = ?))
                                          AND status NOT IN ('selesai', 'dibatalkan')
                                          AND order_kerja_id != ?";

                    $active_orders = DbModel::rawData('row_array', $active_orders_query, [$asset_id, $asset_id, $order_kerja_id]);

                    if ($active_orders['jumlah'] == 0) {
                        // Tidak ada order kerja aktif lain, kembalikan status aset menjadi 'aktif'
                        DbModel::updateData('asset', ['status' => 'aktif'], ['asset_id' => $asset_id]);
                    }
                }
            }

            \DB::commit();
            return ['status' => true];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => false, 'message' => 'Transaksi database gagal: ' . $e->getMessage()];
        }
    }
    public function getLogByOrderId($order_kerja_id)
    {
        $query = "SELECT lk.*, p.pegawai_nm as teknisi_nm
                  FROM log_kerja lk
                  LEFT JOIN mst_pegawai p ON lk.teknisi_pegawai_id = p.pegawai_id
                  WHERE lk.order_kerja_id = ? AND lk.deleted_st = 0";
        return DbModel::rawData('row_array', $query, [$order_kerja_id]);
    }

    /**
     * Mengambil semua foto bukti berdasarkan log_kerja_id.
     */
    public function getPhotosByLogId($log_kerja_id)
    {
        return DbModel::allData('log_kerja_foto', ['log_kerja_id' => $log_kerja_id]);
    }

    /**
     * Method untuk memuat data di DataTables.
     */
    static function loadDatatables()
    {
        // Ambil session filter
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }

        $where = "1 = 1 ";

        // Filter berdasarkan teknisi
        if (@self::$nav_sess['search']['data']['teknisi_id'] != '') {
            $where .= " AND lk.teknisi_pegawai_id = '" . @self::$nav_sess['search']['data']['teknisi_id'] . "' ";
        }

        // Filter berdasarkan hasil
        if (@self::$nav_sess['search']['data']['hasil'] != '') {
            $where .= " AND lk.hasil = '" . @self::$nav_sess['search']['data']['hasil'] . "' ";
        }

        // Filter berdasarkan pencarian
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $term = strtolower(self::$nav_sess['search']['data']['term']);
            $where .= " AND (
                LOWER(lk.diagnosa) LIKE '%$term%' OR 
                LOWER(lk.tindakan) LIKE '%$term%' OR
                LOWER(p.pegawai_nm) LIKE '%$term%' OR
                LOWER(lk.order_kerja_id) LIKE '%$term%'
            ) ";
        }

        $query = "SELECT * FROM (
                SELECT 
                    lk.log_kerja_id,
                    lk.order_kerja_id,
                    lk.tgl_mulai,
                    lk.tgl_selesai,
                    lk.hasil,
                    lk.diagnosa,
                    lk.tindakan,
                    lk.durasi_menit,
                    p.pegawai_nm as teknisi_nm,
                    (SELECT COUNT(*) FROM log_kerja_foto WHERE log_kerja_id = lk.log_kerja_id) as foto_count
                FROM 
                    log_kerja lk
                    LEFT JOIN mst_pegawai p ON lk.teknisi_pegawai_id = p.pegawai_id
                WHERE $where AND lk.deleted_st = 0
            ) x ";

        $search = ['order_kerja_id', 'teknisi_nm', 'diagnosa', 'tindakan', 'hasil'];
        $result = DbModel::datatablesQuery($query, $search, null, null);
        return response()->json($result);
    }

    public function getAllOrderKerja()
    {
        $sql = "SELECT 
                ok.order_kerja_id,
                a.asset_nm,
                CASE 
                    WHEN ok.jadwal_pm_id IS NOT NULL THEN 'Jadwal PM' 
                    ELSE 'Perbaikan' 
                END as jenis
            FROM order_kerja ok
            LEFT JOIN permintaan_komplain p ON p.permintaan_id = ok.permintaan_id
            LEFT JOIN jadwal_pm jp ON jp.jadwal_pm_id = ok.jadwal_pm_id
            LEFT JOIN asset a ON a.asset_id = COALESCE(p.asset_id, jp.asset_id)
            WHERE ok.deleted_st = 0 AND ok.status != 'selesai'
            ORDER BY ok.order_kerja_id DESC";
        return DbModel::rawData('result_array', $sql) ?: [];
    }

    public function getAllTeknisi()
    {
        return DbModel::allData('mst_pegawai', [
            'deleted_st' => 0,
            'active_st' => 1,
            'jabatan_id' => '90'
        ]) ?: [];
    }

    public function getAllSparepart()
    {
        return DbModel::allData('mst_sparepart', [
            'deleted_st' => 0,
            'active_st' => 1
        ]) ?: [];
    }

    public function getLogById($log_kerja_id)
    {
        $sql = "SELECT lk.*, p.pegawai_nm as teknisi_nm
            FROM log_kerja lk
            LEFT JOIN mst_pegawai p ON lk.teknisi_pegawai_id = p.pegawai_id
            WHERE lk.log_kerja_id = ? AND lk.deleted_st = 0";
        return DbModel::rawData('row_array', $sql, [$log_kerja_id]);
    }

    public function getPenugasanByOrderKerja($order_kerja_id)
    {
        return DbModel::rawData('result_array', "SELECT * FROM penugasan_teknisi WHERE order_kerja_id = ?", [$order_kerja_id]) ?: [];
    }

    public function getLogByOrderKerja($order_kerja_id)
    {
        return DbModel::rawData('result_array', "SELECT * FROM log_kerja WHERE order_kerja_id = ?", [$order_kerja_id]) ?: [];
    }

    public function getSparepartByLogKerja($log_kerja_id)
    {
        return DbModel::rawData('result_array', "SELECT s.sparepart_nm, ps.jumlah FROM penggunaan_sparepart ps JOIN mst_sparepart s ON ps.sparepart_id = s.sparepart_id WHERE ps.log_kerja_id = ?", [$log_kerja_id]) ?: [];
    }
}
