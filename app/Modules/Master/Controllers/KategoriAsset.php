<?php

namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\KategoriAssetModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KategoriAsset extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'master::kategori_asset.';
    }

    function index()
    {
        $d = [];
        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('mst_kategori_asset', ['kategori_asset_id' => $id]);
        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    function save($id = null)
    {
        $d = _post();


        if ($id == null) {
            if (empty($d['kategori_asset_id'])) {
                return response()->json(_response('11', $this->uri, ['message' => 'ID Kategori Aset wajib diisi!']));
            }
            if (DbModel::validId('mst_kategori_asset', 'kategori_asset_id', $d['kategori_asset_id'])) {
                return response()->json(_response('20', $this->uri, ['message' => 'ID Kategori Aset sudah ada!']));
            }
        }

        $queryCheckUniqueName = "SELECT * FROM mst_kategori_asset WHERE kategori_asset_nm = '" . addslashes($d['kategori_asset_nm']) . "' AND deleted_st = 0";
        if ($id != null) {
            $queryCheckUniqueName .= " AND kategori_asset_id != '" . addslashes($id) . "'";
        }
        $checkUniqueName = DbModel::rawData('row_array', $queryCheckUniqueName);
        if ($checkUniqueName != null) {
            return response()->json(_response('20', $this->uri, ['message' => 'Nama Kategori Aset sudah digunakan!']));
        }

        try {
            if ($id == null) {
                $result = DbModel::insertData('mst_kategori_asset', $d);
                if ($result) {
                    return response()->json(_response('01', $this->uri, $d));
                } else {
                    return response()->json(_response('11', $this->uri, $d));
                }
            } else {
                $result = DbModel::updateData('mst_kategori_asset', $d, ['kategori_asset_id' => $id]);
                if ($result) {
                    return response()->json(_response('02', $this->uri, $d));
                } else {
                    return response()->json(_response('12', $this->uri, $d));
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error saving kategori asset: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        $hasAssets = DbModel::getData('asset', ['kategori_asset_id' => $id, 'deleted_st' => 0]);
        if ($hasAssets) {
            return response()->json(_response('13', $this->uri, ['message' => 'Kategori ini masih terhubung dengan aset dan tidak dapat dihapus.']));
        }

        $result = DbModel::deleteData('mst_kategori_asset', ['kategori_asset_id' => $id]);
        if ($result) {
            return response()->json(_response('03', $this->uri));
        } else {
            return response()->json(_response('13', $this->uri));
        }
    }

    public function ajax_datatables()
    {
        return KategoriAssetModel::loadDatatables();
    }
}