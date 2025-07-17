<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;
use Illuminate\Support\Facades\Log;

class AdminLogKerja extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LogKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.log_kerja.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        
        // Data untuk filter (jika diperlukan)
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['deleted_st' => 0, 'active_st' => 1, 'jabatan_id' => '90']);
        
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($order_kerja_id = null)
    {
        if (!$order_kerja_id) {
            return '<h5>Error: Order Kerja ID tidak valid.</h5>';
        }

        $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
        if (!$order_kerja) {
            return '<h5>Error: Data Order Kerja tidak ditemukan.</h5>';
        }

        $d['order_kerja'] = $order_kerja;

        // Ambil asset_id untuk keperluan redirect
        $asset_id = null;
        if (!empty($order_kerja['permintaan_id'])) {
            $sumber = DbModel::getData('permintaan_komplain', ['permintaan_id' => $order_kerja['permintaan_id']]);
            $asset_id = $sumber['asset_id'] ?? null;
        } else if (!empty($order_kerja['jadwal_pm_id'])) {
            $sumber = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $order_kerja['jadwal_pm_id']]);
            $asset_id = $sumber['asset_id'] ?? null;
        }
        
        // Simpan asset_id ke dalam data view
        $d['asset_id'] = $asset_id;

        // Dapatkan data log kerja jika sudah ada
        $d['log_kerja'] = $this->model->getLogByOrderId($order_kerja_id);
        
        // Data sparepart untuk dropdown
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);

        // Dapatkan foto-foto dari log kerja jika ada
        if (!empty($d['log_kerja'])) {
            $d['log_fotos'] = $this->model->getPhotosByLogId($d['log_kerja']['log_kerja_id']);
        } else {
            $d['log_fotos'] = [];
        }

        // Standarisasi form action
        $d['form_act'] = $this->uri . '/save/' . $order_kerja_id;

        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($order_kerja_id = null)
    {
        $d = _post();
        
        // Validasi data
        if (empty($d['tindakan'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Tindakan yang dilakukan wajib diisi.']));
        }
        
        if (empty($d['hasil'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Hasil pekerjaan wajib dipilih.']));
        }

        try {
            $result = $this->model->saveData($order_kerja_id, $d);
            
            if ($result['status']) {
                // Mendapatkan asset_id untuk redirect
                $asset_id = $d['asset_id'] ?? null;
                
                return response()->json(_response('01', $this->uri, [
                    'message' => 'Laporan pekerjaan berhasil disimpan.',
                    'asset_id' => $asset_id
                ]));
            } else {
                throw new \Exception($result['message'] ?? 'Gagal menyimpan laporan.');
            }
        } catch (\Exception $e) {
            Log::error('Error saving log kerja: ' . $e->getMessage());
            return response()->json(_response('11', $this->uri, ['message' => $e->getMessage()]));
        }
    }

    public function ajax_datatables()
    {
        return $this->model->loadDatatables();
    }
}
