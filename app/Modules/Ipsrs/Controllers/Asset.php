<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\AssetModel;
use Illuminate\Support\Facades\Log;


class Asset extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::asset.';
    }

    function index()
    {
        $d = [];
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => '0', 'active_st' => '1', 'tipe_lokasi' => 'Ruangan']);
        $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => '0', 'active_st' => '1']);
        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('asset', ['asset_id' => $id]);
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => '0', 'active_st' => '1', 'tipe_lokasi' => 'Ruangan']);
        $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => '0', 'active_st' => '1']);

        if ($id === null) {
            $d['form_act'] = $this->uri . '/save';
        } else {
            $d['form_act'] = $this->uri . '/save/' . $id;
        }

        return $this->renderView($this->template . 'form_modal', $d);
    }


    function save($id = null)
    {
        $d = _post();

        if (isset($d['perolehan_tgl']) && $d['perolehan_tgl'] != '') {
            $d['perolehan_tgl'] = to_date($d['perolehan_tgl'], '-', 'date');
        } else {
            $d['perolehan_tgl'] = null;
        }

        if (isset($d['pm_berikutnya']) && $d['pm_berikutnya'] != '') {
            $d['pm_berikutnya'] = to_date($d['pm_berikutnya'], '-', 'date');
        } else {
            $d['pm_berikutnya'] = null;
        }

        if (!empty($d['no_seri'])) {
            $queryCheckNoSeri = DbModel::rawData('row_array', "SELECT * FROM asset WHERE no_seri = '" . addslashes($d['no_seri']) . "' AND asset_id != '" . addslashes($id) . "' AND deleted_st = 0");
            if ($queryCheckNoSeri != null) {
                return response()->json(_response('20', $this->uri, ['message' => 'Nomor Seri sudah digunakan untuk aset lain!']));
            }
        }
        if (!empty($d['lokasi_id']) && !DbModel::validId('mst_lokasi', 'lokasi_id', $d['lokasi_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Lokasi tidak ditemukan!']));
        }
        if (!empty($d['kategori_asset_id']) && !DbModel::validId('mst_kategori_asset', 'kategori_asset_id', $d['kategori_asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Kategori Aset tidak ditemukan!']));
        }

        try {
            if ($id == null) {
                $d['asset_id'] = DbModel::getId('asset', 2, 12);
                if (empty($d['asset_id'])) {
                    return response()->json(_response('11', $this->uri, ['message' => 'Gagal membuat ID Aset baru!']));
                }

                $result = DbModel::insertData('asset', $d);
                if ($result) {
                    return response()->json(_response('01', $this->uri, $d));
                } else {
                    return response()->json(_response('11', $this->uri, $d));
                }
            } else {
                $result = DbModel::updateData('asset', $d, ['asset_id' => $id]);
                if ($result) {
                    return response()->json(_response('02', $this->uri, $d));
                } else {
                    return response()->json(_response('12', $this->uri, $d));
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error saving asset: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);

            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        $hasKomplain = DbModel::getData('permintaan_komplain', ['asset_id' => $id, 'deleted_st' => 0]);
        if ($hasKomplain) {
            return response()->json(_response('13', $this->uri, ['message' => 'Aset ini memiliki Permintaan Komplain terkait dan tidak dapat dihapus.']));
        }
        $hasJadwalPm = DbModel::getData('jadwal_pm', ['asset_id' => $id, 'deleted_st' => 0]);
        if ($hasJadwalPm) {
            return response()->json(_response('13', $this->uri, ['message' => 'Aset ini memiliki Jadwal PM terkait dan tidak dapat dihapus.']));
        }

        $result = DbModel::deleteData('asset', ['asset_id' => $id]);
        if ($result) {
            return response()->json(_response('03', $this->uri));
        } else {
            return response()->json(_response('13', $this->uri));
        }
    }

    public function ajax_datatables()
    {
        return AssetModel::loadDatatables();
    }


    public function find_by_barcode($noSeri)
    {

        if (empty($noSeri)) {
            return response()->json(_response('10', null, ['message' => 'Nomor seri/barcode wajib diisi.']));
        }

        $asset = AssetModel::getAssetByNoSeri($noSeri);

        if ($asset) {
            return response()->json(_response('00', null, ['asset' => $asset]));
        } else {
            return response()->json(_response('10', null, ['message' => 'Aset dengan nomor seri/barcode tersebut tidak ditemukan.']));
        }
    }
}
