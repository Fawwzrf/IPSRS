<?php

namespace App\Modules\Ipsrs\Controllers; // Sesuaikan dengan namespace Anda

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PelaporModel;

class Pelapor extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PelaporModel();
        $this->template = 'ipsrs::pelapor.'; // Path ke view pelapor
    }

    public function index()
    {
        $pegawai_id = session('pegawai_id');
        $d = [];

        $d['history'] = $this->model->getHistoryByPegawai($pegawai_id);

        return $this->renderView($this->template . 'index', $d);
    }

    /**
     * PERBAIKAN: Fungsi ini sekarang mengirim semua data yang dibutuhkan oleh form.
     */
    public function form_komplain_modal()
    {
        // Mengambil semua lokasi aktif
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);

        // Mengambil semua aset aktif untuk difilter oleh JavaScript
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);

        // Form action menunjuk ke controller PermintaanKomplain yang sudah ada
        $d['form_act'] = url('ipsrs/adminpermintaankomplain/save');

        return $this->renderView($this->template . 'form_komplain_modal', $d);
    }
}
