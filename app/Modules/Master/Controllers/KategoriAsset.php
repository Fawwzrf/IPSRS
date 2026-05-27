<?php

namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\KategoriAssetModel;

class KategoriAsset extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'master::kategori_asset.';
    }

    // Menampilkan halaman utama kategori aset
    function index()
    {
        $d = [];
        $this->save_session_search($d);
        return $this->renderView($this->template . 'index', $d);
    }

    // Menampilkan form modal untuk tambah/edit kategori aset
    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('mst_kategori_asset', ['kategori_asset_id' => $id]);
        $d['form_act'] = $this->uri . '/save' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    // Menyimpan data kategori aset (tambah/edit)
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

        if (empty($d['kategori_asset_nm'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Nama Kategori Aset wajib diisi!']));
        }

        $queryCheckUniqueName = "SELECT * FROM mst_kategori_asset WHERE kategori_asset_nm = ? AND deleted_st = 0";
        $params = [$d['kategori_asset_nm']];
        if ($id != null) {
            $queryCheckUniqueName .= " AND kategori_asset_id != ?";
            $params[] = $id;
        }
        if (DbModel::rawData('row_array', $queryCheckUniqueName, $params)) {
            return response()->json(_response('20', $this->uri, ['message' => 'Nama Kategori Aset sudah digunakan!']));
        }

        if ($id == null) {
            $result = DbModel::insertData('mst_kategori_asset', $d);
            return response()->json(_response($result ? '01' : '11', $this->uri, $d));
        } else {
            $result = DbModel::updateData('mst_kategori_asset', $d, ['kategori_asset_id' => $id]);
            return response()->json(_response($result ? '02' : '12', $this->uri, $d));
        }
    }

    // Menghapus kategori aset
    public function delete($id)
    {
        $hasAssets = DbModel::getData('mst_asset', ['kategori_asset_id' => $id, 'deleted_st' => 0]);
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

    // Mengambil data kategori aset untuk datatables (AJAX)
    public function ajax_datatables()
    {
        return KategoriAssetModel::loadDatatables();
    }
}