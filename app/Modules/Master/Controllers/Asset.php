<?php

namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\AssetModel;
use Illuminate\Support\Facades\Log;

class Asset extends MyController
{
  function __construct()
  {
    parent::__construct();
    $this->template = 'master::asset.';
  }

  function index()
  {
    $d = [];
    
    // Debug untuk melihat apakah fungsi save_session_search dipanggil
    Log::info("Before save_session_search");
    
    $this->save_session_search($d);
    
    // Debug untuk melihat hasil save_session_search
    Log::info("After save_session_search: ", [session(request('n'))]);
    
    $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => '0', 'active_st' => '1', 'tipe_lokasi' => 'Ruangan']);
    $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => '0', 'active_st' => '1']);
    
    return $this->renderView($this->template . 'index', $d);
  }

  function form_modal($id = null)
  {
    // Load data referensi untuk dropdown
    $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => '0', 'active_st' => '1', 'tipe_lokasi' => 'Ruangan']);
    $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => '0', 'active_st' => '1']);

    // Load data aset jika mode edit
    $d['main'] = DbModel::getData('asset', ['asset_id' => $id]);
    $d['form_act'] = $this->uri . '/save/' . $id;
    
    return $this->renderView($this->template . 'form_modal', $d);
  }

  function save($id = null)
  {
    $d = _post();
    
    // Proses tanggal
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
    
    // Validasi nomor seri
    if (!empty($d['no_seri'])) {
        $queryCheckNoSeri = DbModel::rawData('row_array', "SELECT * FROM asset WHERE no_seri = '" . addslashes($d['no_seri']) . "' AND asset_id != '" . addslashes($id) . "' AND deleted_st = 0");
        if ($queryCheckNoSeri != null) {
            return response()->json(_response('20', $this->uri, ['message' => 'Nomor Seri sudah digunakan untuk aset lain!']));
        }
    }
    
    // Insert atau update berdasarkan ID
    if ($id == null) {
        // Generate ID aset jika mode tambah
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
        // Update data jika mode edit
        $result = DbModel::updateData('asset', $d, ['asset_id' => $id]);
        if ($result) {
            return response()->json(_response('02', $this->uri, $d));
        } else {
            return response()->json(_response('12', $this->uri, $d));
        }
    }
  }

  // Fungsi detail modal - tetap dipertahankan karena ini fitur tambahan yang baik
  function form_detail_modal($id = null)
  {
    $assetModel = new AssetModel();
    $d['asset'] = $assetModel->getAssetDetailById($id);
    $d['history'] = $assetModel->getAssetHistory($id);
    $d['title'] = 'Detail Aset: ' . $d['asset']['asset_nm'];
    
    return $this->renderView($this->template . 'detail_modal', $d);
  }

  // Fungsi detail - tetap dipertahankan sebagai fitur tambahan
  function detail($id = null)
  {
    $assetModel = new AssetModel();
    $d['asset'] = $assetModel->getAssetDetailById($id);
    $d['history'] = $assetModel->getAssetHistory($id);
    $d['title'] = 'Detail Aset: ' . $d['asset']['asset_nm'];
    
    return $this->renderView($this->template . 'detail', $d);
  }

  // Penting: Fungsi untuk datatables yang memanggil model
  function ajax_datatables()
  {
    // Debug untuk melihat session saat Ajax dipanggil
    Log::info("Ajax datatables session: ", [session(request('n'))]);
    
    return AssetModel::loadDatatables();
  }
}
