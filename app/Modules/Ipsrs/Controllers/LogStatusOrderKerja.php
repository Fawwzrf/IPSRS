<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LogStatusOrderKerjaModel;
use Illuminate\Http\Request;

class LogStatusOrderKerja extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LogStatusOrderKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.log_status.';
    }

    /**
     * Menampilkan daftar riwayat status untuk order kerja tertentu
     */
    public function index($order_kerja_id = null)
    {
        $d = [];
        $this->save_session_search($d);

        if ($order_kerja_id) {
            // Jika order_kerja_id disediakan, ambil data order kerja
            $d['trx_order_kerja'] = DbModel::getData('trx_order_kerja', ['order_kerja_id' => $order_kerja_id]);

            // Jika trx_order_kerja tidak ditemukan, redirect ke halaman daftar
            if (!$d['trx_order_kerja']) {
                return redirect($this->uri);
            }

            // Ambil riwayat status
            $d['riwayat'] = $this->model->getRiwayatStatus($order_kerja_id);
        }

        return $this->renderView($this->template . 'index', $d);
    }

    /**
     * Modal untuk menampilkan riwayat status
     */
    public function form_modal($order_kerja_id = null)
    {
        if (!$order_kerja_id) {
            return '<div class="alert alert-danger">ID Order Kerja tidak valid.</div>';
        }

        $d['trx_order_kerja'] = DbModel::getData('trx_order_kerja', ['order_kerja_id' => $order_kerja_id]);

        if (!$d['trx_order_kerja']) {
            return '<div class="alert alert-danger">Data Order Kerja tidak ditemukan.</div>';
        }

        $d['riwayat'] = $this->model->getRiwayatStatus($order_kerja_id);

        return $this->renderView($this->template . 'form_modal', $d);
    }

    /**
     * Ajax untuk datatables
     */
    public function ajax_datatables()
    {
        return $this->model->loadDatatables();
    }
}
