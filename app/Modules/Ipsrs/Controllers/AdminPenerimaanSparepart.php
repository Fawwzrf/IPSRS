<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PenerimaanSparepartModel;
use Illuminate\Support\Facades\Log;

class AdminPenerimaanSparepart extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PenerimaanSparepartModel();
        $this->template = 'ipsrs::admin.inventaris.penerimaan_sparepart.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        
        // Data untuk filter jika diperlukan nanti
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);
        
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d = [];
        if ($id) {
            $d['main'] = $this->model->getById($id);
            if (!$d['main']) {
                return '<h5>Error: Data tidak ditemukan.</h5>';
            }
        }

        // Data untuk dropdown
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);
        
        // Standardisasi format URI untuk konsistensi
        $d['form_act'] = $this->uri . '/save/' . $id;
        
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // Validasi dasar
        if (empty($d['sparepart_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Sparepart wajib dipilih!']));
        }
        
        if (empty($d['jumlah']) || (int)$d['jumlah'] <= 0) {
            return response()->json(_response('11', $this->uri, ['message' => 'Jumlah harus lebih dari 0!']));
        }

        try {
            $result = $this->model->saveData($id, $d);
            
            if ($result['status']) {
                return response()->json(_response($result['mode'] === 'insert' ? '01' : '02', $this->uri, $d));
            } else {
                return response()->json(_response('11', $this->uri, ['message' => $result['message'] ?? 'Gagal menyimpan data.']));
            }
        } catch (\Exception $e) {
            Log::error('Error saving penerimaan: ' . $e->getMessage());
            return response()->json(_response('11', $this->uri, ['message' => 'Terjadi kesalahan: ' . $e->getMessage()]));
        }
    }

    public function delete($id)
    {
        try {
            $result = $this->model->deleteData($id);
            
            if ($result['status']) {
                return response()->json(_response('03', $this->uri));
            } else {
                return response()->json(_response('13', $this->uri, ['message' => $result['message']]));
            }
        } catch (\Exception $e) {
            Log::error('Error deleting penerimaan: ' . $e->getMessage());
            return response()->json(_response('13', $this->uri, ['message' => 'Terjadi kesalahan: ' . $e->getMessage()]));
        }
    }

    public function ajax_datatables()
    {
        return $this->model->loadDatatables();
    }
}
