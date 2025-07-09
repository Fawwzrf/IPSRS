<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PermintaanKomplainModel; // Perbaiki namespace jika perlu
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PermintaanKomplain extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::permintaan_komplain.'; // Lokasi views untuk modul ini
    }

    function index()
    {
        $d = [];
        // Data untuk filter di index (aset, pembuat komplain)
        $sqlAllAsset = "SELECT a.asset_id, a.asset_nm, a.no_seri, l.lokasi_nm FROM asset a LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id WHERE a.deleted_st = 0 AND a.active_st = 1";
        $d['all_asset'] = DbModel::rawData('result_array', $sqlAllAsset);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);

        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('permintaan_komplain', ['permintaan_id' => $id]);
        $sqlAllAsset = "SELECT a.asset_id, a.asset_nm, a.no_seri, l.lokasi_nm FROM asset a LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id WHERE a.deleted_st = 0 AND a.active_st = 1";
        $d['all_asset'] = DbModel::rawData('result_array', $sqlAllAsset);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);
        
        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    function save($id = null)
    {
        $d = _post();

        // Format tanggal komplain (DD-MM-YYYY dari form) ke DB (YYYY-MM-DD)
        if (isset($d['tgl']) && $d['tgl'] != '') {
            try { $d['tgl'] = (new \DateTime($d['tgl']))->format('Y-m-d'); } catch (\Exception $e) { $d['tgl'] = null; Log::error('Date conversion failed for tgl: ' . $e->getMessage()); }
        } else { $d['tgl'] = null; }

        

        // Validasi ID Aset
        if (empty($d['asset_id']) || !DbModel::validId('asset', 'asset_id', $d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Aset tidak valid atau kosong!']));
        }
        // Validasi Pembuat Komplain
        if (empty($d['pegawai_id']) || !DbModel::validId('mst_pegawai', 'pegawai_id', $d['pegawai_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Pembuat Komplain tidak valid atau kosong!']));
        }
        // Validasi Status
        if (empty($d['status'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Status Komplain wajib dipilih!']));
        }
        
        try {
            DB::beginTransaction();

            if ($id == null) {
                $d['permintaan_id'] = DbModel::getId('permintaan_komplain', 2, 12); // Generate ID baru
                if (empty($d['permintaan_id'])) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Gagal membuat ID Permintaan Komplain!']));
                }

                $result = DbModel::insertData('permintaan_komplain', $d);
                if (!$result) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Data permintaan komplain gagal disimpan!']));
                }

                DB::commit();
                return response()->json(_response('01', $this->uri, $d));
            } else {
                $result = DbModel::updateData('permintaan_komplain', $d, ['permintaan_id' => $id]);
                if ($result) {
                    DB::commit();
                    return response()->json(_response('02', $this->uri, $d));
                } else {
                    DB::rollBack();
                    return response()->json(_response('12', $this->uri, ['message' => 'Data permintaan komplain gagal diubah!']));
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error saving permintaan komplain: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $dataToDelete = DbModel::getData('permintaan_komplain', ['permintaan_id' => $id]);
            if (!$dataToDelete) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data permintaan komplain tidak ditemukan!']));
            }

            // Validasi: Periksa apakah permintaan ini sudah memiliki order kerja terkait
            $hasOrderKerja = DbModel::getData('order_kerja', ['permintaan_id' => $id, 'deleted_st' => 0]);
            if ($hasOrderKerja) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Permintaan ini memiliki Order Kerja terkait dan tidak dapat dihapus.']));
            }

            $result = DbModel::deleteData('permintaan_komplain', ['permintaan_id' => $id]); // Logically delete
            if (!$result) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data permintaan komplain gagal dihapus!']));
            }

            DB::commit();
            return response()->json(_response('03', $this->uri));
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting permintaan komplain: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('13', $this->uri, ['message' => 'Terjadi kesalahan saat menghapus data: ' . $th->getMessage()]));
        }
    }

    public function ajax_datatables()
    {
        return PermintaanKomplainModel::loadDatatables();
    }
}