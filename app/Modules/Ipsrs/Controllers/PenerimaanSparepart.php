<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PenerimaanSparepartModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Dipertahankan sesuai Asset.php

class PenerimaanSparepart extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::penerimaan_sparepart.'; // Lokasi views untuk modul ini
    }

    function index()
    {
        $d = [];
        // Data untuk filter di index (misalnya daftar sparepart)
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => '0', 'active_st' => '1']);
        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('trx_penerimaan_sparepart', ['penerimaan_id' => $id]);
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => '0', 'active_st' => '1']); // Pilihan sparepart
        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    function save($id = null)
    {
        $d = _post();

        // Format tanggal penerimaan (DD-MM-YYYY)
        if (isset($d['tgl']) && $d['tgl'] != '') {
            $d['tgl'] = to_date($d['tgl'], '-', 'date');
        } else {
            $d['tgl'] = null;
        }

        // Validasi ID Sparepart
        if (empty($d['sparepart_id']) || !DbModel::validId('mst_sparepart', 'sparepart_id', $d['sparepart_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Sparepart tidak valid atau kosong!']));
        }
        // Validasi Jumlah
        if (!isset($d['jumlah']) || !is_numeric($d['jumlah']) || $d['jumlah'] <= 0) {
            return response()->json(_response('11', $this->uri, ['message' => 'Jumlah penerimaan harus angka positif!']));
        }
        
        try {
            DB::beginTransaction(); // Memulai transaksi database

            if ($id == null) {
                $d['penerimaan_id'] = DbModel::getId('trx_penerimaan_sparepart', 2, 12); // Generate ID baru
                if (empty($d['penerimaan_id'])) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Gagal membuat ID Penerimaan!']));
                }

                $result = DbModel::insertData('trx_penerimaan_sparepart', $d);
                if (!$result) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Data penerimaan gagal disimpan!']));
                }

                // --- PENTING: Update Stok di mst_sparepart ---
                $updateStockResult = DB::table('mst_sparepart')
                                       ->where('sparepart_id', $d['sparepart_id'])
                                       ->increment('stok', $d['jumlah']);
                if (!$updateStockResult) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Gagal memperbarui stok sparepart!']));
                }
                // --- AKHIR UPDATE STOK ---

                DB::commit(); // Komit transaksi
                return response()->json(_response('01', $this->uri, $d));
            } else {
                // Untuk EDIT: Harus hitung selisih stok yang diubah
                // Dapatkan data lama
                $oldData = DbModel::getData('trx_penerimaan_sparepart', ['penerimaan_id' => $id]);
                $oldJumlah = $oldData['jumlah'];
                $oldSparepartId = $oldData['sparepart_id'];

                $result = DbModel::updateData('trx_penerimaan_sparepart', $d, ['penerimaan_id' => $id]);
                if (!$result) {
                    DB::rollBack();
                    return response()->json(_response('12', $this->uri, ['message' => 'Data penerimaan gagal diubah!']));
                }

                // --- PENTING: Sesuaikan Stok saat EDIT ---
                // Jika sparepart_id berubah, atau jumlah berubah
                if ($oldSparepartId !== $d['sparepart_id']) {
                    // Kurangi stok dari sparepart lama
                    DB::table('mst_sparepart')->where('sparepart_id', $oldSparepartId)->decrement('stok', $oldJumlah);
                    // Tambahkan stok ke sparepart baru
                    DB::table('mst_sparepart')->where('sparepart_id', $d['sparepart_id'])->increment('stok', $d['jumlah']);
                } else {
                    // Jika sparepart_id sama, hanya sesuaikan stok berdasarkan selisih jumlah
                    $diffJumlah = $d['jumlah'] - $oldJumlah;
                    if ($diffJumlah !== 0) {
                        DB::table('mst_sparepart')->where('sparepart_id', $d['sparepart_id'])->increment('stok', $diffJumlah);
                    }
                }
                // --- AKHIR PENYESUAIAN STOK ---

                DB::commit(); // Komit transaksi
                return response()->json(_response('02', $this->uri, $d));
            }
        } catch (\Throwable $th) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            Log::error('Error saving penerimaan sparepart: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction(); // Memulai transaksi database

            // Dapatkan data penerimaan yang akan dihapus
            $dataToDelete = DbModel::getData('trx_penerimaan_sparepart', ['penerimaan_id' => $id]);
            if (!$dataToDelete) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data penerimaan tidak ditemukan!']));
            }

            // --- PENTING: Kurangi Stok saat HAPUS ---
            $updateStockResult = DB::table('mst_sparepart')
                                   ->where('sparepart_id', $dataToDelete['sparepart_id'])
                                   ->decrement('stok', $dataToDelete['jumlah']);
            if (!$updateStockResult) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Gagal mengurangi stok sparepart saat penghapusan!']));
            }
            // --- AKHIR PENGURANGAN STOK ---

            $result = DbModel::deleteData('trx_penerimaan_sparepart', ['penerimaan_id' => $id]); // Logically delete
            if (!$result) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data penerimaan gagal dihapus!']));
            }

            DB::commit(); // Komit transaksi
            return response()->json(_response('03', $this->uri));
        } catch (\Throwable $th) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            Log::error('Error deleting penerimaan sparepart: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('13', $this->uri, ['message' => 'Terjadi kesalahan saat menghapus data: ' . $th->getMessage()]));
        }
    }

    public function ajax_datatables()
    {
        return PenerimaanSparepartModel::loadDatatables();
    }
}