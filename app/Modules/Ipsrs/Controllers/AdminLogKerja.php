<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;

class AdminLogKerja extends MyController
{
    protected $model;
    public function __construct()
    {
        parent::__construct();
        $this->model = new LogKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.log_kerja.';
    }

    public function form_modal($order_kerja_id = null)
    {
        if (!$order_kerja_id) return 'Error: Order Kerja ID tidak valid.';
        $d['order_kerja'] = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
        $d['log_kerja'] = DbModel::getData('log_kerja', ['order_kerja_id' => $order_kerja_id, 'deleted_st' => 0]);
        $d['form_act'] = url('ipsrs/adminlogkerja/save/' . $order_kerja_id);
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function form_view_log_modal($order_kerja_id = null)
    {
        if (!$order_kerja_id) {
            return '<h5>Error: Order Kerja ID tidak valid.</h5>';
        }

        // Ambil data log kerja utama
        $d['log'] = $this->model->getLogByOrderId($order_kerja_id);

        if (!$d['log']) {
            return '<h5>Laporan kerja untuk Order Kerja ini belum dibuat.</h5>';
        }

        // Ambil data foto dan sparepart jika log ditemukan
        $d['photos'] = $this->model->getPhotosByLogId($d['log']['log_kerja_id']);

        return $this->renderView($this->template . 'view_log_modal', $d);
    }

    public function save($order_kerja_id)
    {
        $d = _post();
        if (empty($d['tindakan'])) return response()->json(_response('11', $this->uri, ['message' => 'Tindakan wajib diisi.']));
        if (empty($d['hasil'])) return response()->json(_response('11', $this->uri, ['message' => 'Hasil pekerjaan wajib dipilih.']));

        $result = $this->model->saveData($order_kerja_id, $d);
        if ($result['status']) {
            return response()->json(_response('01', $this->uri, ['message' => 'Laporan kerja berhasil disimpan.']));
        } else {
            return response()->json(_response('11', $this->uri, ['message' => $result['message']]));
        }
    }
}
