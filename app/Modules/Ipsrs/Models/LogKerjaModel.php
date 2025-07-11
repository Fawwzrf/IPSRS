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

            // Update status Order Kerja
            $status_baru = ($data['hasil'] == 'berhasil') ? 'selesai' : 'menunggu_sparepart'; // Contoh logika
            DbModel::updateData('order_kerja', ['status' => $status_baru], ['order_kerja_id' => $order_kerja_id]);

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
