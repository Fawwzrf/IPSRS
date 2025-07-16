<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogKerjaModel extends Model
{
    public function saveData($order_kerja_id, $data)
    {
        $pegawai_id = Auth::id();
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
                foreach ($data['sparepart'] as $item) {
                    if (!empty($item['id']) && !empty($item['jumlah']) && $item['jumlah'] > 0) {
                        $sparepartMaster = DbModel::getData('mst_sparepart', ['sparepart_id' => $item['id']]);

                        // Cek stok cukup
                        if ($sparepartMaster['stok'] < $item['jumlah']) {
                            throw new \Exception('Stok untuk ' . $sparepartMaster['sparepart_nm'] . ' tidak mencukupi.');
                        }

                        $penggunaanData = [
                            'penggunaan_id' => DbModel::getId('penggunaan_sparepart', 2, 12),
                            'log_kerja_id' => $log_kerja_id, // ID dari log kerja yang baru dibuat
                            'sparepart_id' => $item['id'],
                            'jumlah' => $item['jumlah'],
                            'harga_satuan' => $sparepartMaster['harga'] ?? 0
                        ];
                        DbModel::insertData('penggunaan_sparepart', $penggunaanData);

                        \DB::table('mst_sparepart')->where('sparepart_id', $item['id'])->decrement('stok', $item['jumlah']);
                    }
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
            DbModel::updateData('order_kerja', ['status' => $status_baru], ['order_kerja_id' => $order_kerja_id]);

            // Catat perubahan status
            $log_status_data = [
                'log_status_id' => DbModel::getId('log_status_order_kerja', 2, 12),
                'order_kerja_id' => $order_kerja_id,
                'status_sebelumnya' => $status_sebelumnya,
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
}
