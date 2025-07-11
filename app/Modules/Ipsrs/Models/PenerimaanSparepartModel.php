<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PenerimaanSparepartModel extends Model
{
    protected static $nav_sess;
    public function __construct()
    {
        parent::__construct();
        self::initSession();
    }
    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) self::$nav_sess = session(request('n'));
    }

    static function loadDatatables()
    {
        self::initSession();
        $query = "SELECT p.*, s.sparepart_nm 
                  FROM trx_penerimaan_sparepart p
                  JOIN mst_sparepart s ON p.sparepart_id = s.sparepart_id";
        $searchableColumns = ['p.penerimaan_id', 's.sparepart_nm', 'p.vendor', 'p.no_faktur'];
        $whereConditions = ['p.deleted_st' => 0];
        // ... (Tambahkan logika filter jika diperlukan) ...

        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, '');
        return response()->json($result);
    }

    public function getById($id)
    {
        return DbModel::getData('trx_penerimaan_sparepart', ['penerimaan_id' => $id]);
    }

    public function saveData($id, $data)
    {
        $sparepart_id = $data['sparepart_id'];
        $jumlah_diterima = (int)$data['jumlah'] / 2; //karena tombol form mengirimkan dua kali response
        $harga_penerimaan = (float)($data['harga_satuan'] ?? 0);
        try {
            \DB::beginTransaction();

            $sparepartMaster = \DB::table('mst_sparepart')->where('sparepart_id', $sparepart_id)->lockForUpdate()->first();
            if (!$sparepartMaster) {
                throw new \Exception("Sparepart tidak ditemukan.");
            }

            $stok_lama = (int)$sparepartMaster->stok;
            $harga_lama = (float)$sparepartMaster->harga;
            

            // Perhitungan Stok & Harga Baru
            $stok_baru = $stok_lama + $jumlah_diterima;
            $nilai_total_lama = $stok_lama * $harga_lama;
            $nilai_penerimaan_baru = $jumlah_diterima * $harga_penerimaan;
            $harga_rata_rata_baru = ($stok_baru > 0) ? ($nilai_total_lama + $nilai_penerimaan_baru) / $stok_baru : $harga_penerimaan;

            

            // HANYA ADA SATU KALI UPDATE STOK DI SINI
            \DB::table('mst_sparepart')
                ->where('sparepart_id', $sparepart_id)
                ->update([
                    'stok' => $stok_baru,
                    'harga' => $harga_rata_rata_baru
                ]);
            

            // Simpan transaksi penerimaan
            $saveData = [
                'sparepart_id' => $sparepart_id,
                'tgl' => to_date($data['tgl'], '-', 'date'),
                'jumlah' => $jumlah_diterima,
                'harga_satuan' => $harga_penerimaan,
            ];

            // Diasumsikan hanya insert baru, tidak ada edit
            $saveData['penerimaan_id'] = DbModel::getId('trx_penerimaan_sparepart', 2, 12);
            DbModel::insertData('trx_penerimaan_sparepart', $saveData);
            

            \DB::commit();
            
            return ['status' => true, 'mode' => 'insert'];
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return ['status' => false, 'message' => 'Transaksi gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Logika delete juga disederhanakan
     */
    public function deleteData($id)
    {
        try {
            \DB::beginTransaction();
            $data = $this->getById($id);
            if (!$data) return ['status' => false, 'message' => 'Data penerimaan tidak ditemukan.'];

            // Kurangi stok sejumlah yang ada di transaksi ini
            \DB::table('mst_sparepart')->where('sparepart_id', $data->sparepart_id)->decrement('stok', $data->jumlah);

            // Soft delete transaksi
            DbModel::updateData('trx_penerimaan_sparepart', ['deleted_st' => 1], ['penerimaan_id' => $id]);

            \DB::commit();
            return ['status' => true];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => false, 'message' => 'Transaksi gagal: ' . $e->getMessage()];
        }
    }
}
