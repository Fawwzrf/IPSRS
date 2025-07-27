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
        $data = [];
        $this->save_session_search($data);
        return $this->renderView($this->template . 'index', $data);
    }

    // Menampilkan form modal untuk tambah/edit kategori aset
    function form_modal($id = null)
    {
        $data['main'] = DbModel::getData('mst_kategori_asset', ['kategori_asset_id' => $id]);
        $data['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $data);
    }

    // Validasi data kategori aset
    private function validate($data, $id = null)
    {
        // Validasi ID
        if ($id === null) {
            if (empty($data['kategori_asset_id'])) {
                return ['status' => false, 'code' => '11', 'message' => 'ID Kategori Aset wajib diisi!'];
            }
            if (DbModel::validId('mst_kategori_asset', 'kategori_asset_id', $data['kategori_asset_id'])) {
                return ['status' => false, 'code' => '20', 'message' => 'ID Kategori Aset sudah ada!'];
            }
        }

        // Validasi Nama
        if (empty($data['kategori_asset_nm'])) {
            return ['status' => false, 'code' => '11', 'message' => 'Nama Kategori Aset wajib diisi!'];
        }

        $params = [$data['kategori_asset_nm']];
        $query = "SELECT 1 FROM mst_kategori_asset WHERE kategori_asset_nm = ? AND deleted_st = 0";
        if ($id !== null) {
            $query .= " AND kategori_asset_id != ?";
            $params[] = $id;
        }
        if (DbModel::rawData('row_array', $query, $params)) {
            return ['status' => false, 'code' => '20', 'message' => 'Nama Kategori Aset sudah digunakan!'];
        }

        return ['status' => true];
    }

    // Menyimpan data kategori aset (tambah/edit)
    function save($id = null)
    {
        $data = _post();
        $validation = $this->validate($data, $id);

        if (!$validation['status']) {
            return response()->json(_response($validation['code'], $this->uri, ['message' => $validation['message']]));
        }

        if ($id === null) {
            $result = DbModel::insertData('mst_kategori_asset', $data);
            $code = $result ? '01' : '11';
        } else {
            $result = DbModel::updateData('mst_kategori_asset', $data, ['kategori_asset_id' => $id]);
            $code = $result ? '02' : '12';
        }
        return response()->json(_response($code, $this->uri, $data));
    }

    // Menghapus kategori aset
    public function delete($id)
    {
        $relatedAssets = DbModel::getData('mst_asset', ['kategori_asset_id' => $id, 'deleted_st' => 0]);
        if ($relatedAssets) {
            return response()->json(_response('13', $this->uri, ['message' => 'Kategori ini masih terhubung dengan aset dan tidak dapat dihapus.']));
        }

        $result = DbModel::deleteData('mst_kategori_asset', ['kategori_asset_id' => $id]);
        $code = $result ? '03' : '13';
        return response()->json(_response($code, $this->uri));
    }

    // Mengambil data kategori aset untuk datatables (AJAX)
    public function ajax_datatables()
    {
        return KategoriAssetModel::loadDatatables();
    }
}
