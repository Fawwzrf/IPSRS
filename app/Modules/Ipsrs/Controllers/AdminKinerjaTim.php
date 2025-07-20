<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\KinerjaTimModel;

class AdminKinerjaTim extends MyController
{
    public function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::admin.laporan.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);

        // Set default rentang tanggal jika belum ada di session
        if (empty($d['nav_sess']['search']['data']['tgl_start'])) {
            $d['nav_sess']['search']['data']['tgl_start'] = date('01-m-Y');
            $d['nav_sess']['search']['data']['tgl_end'] = date('d-m-Y');
        }

        // Ambil data teknisi untuk dropdown filter
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['jabatan_id' => '90', 'deleted_st' => 0]);

        return $this->renderView($this->template . 'kinerja_tim', $d);
    }

    public function ajax_datatables()
    {
        return KinerjaTimModel::loadDatatables();
    }
}
