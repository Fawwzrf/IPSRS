<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LaporanModel;

class AdminLaporan extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LaporanModel();
        $this->template = 'ipsrs::admin.laporan.';
    }

    /**
     * Menampilkan laporan Kinerja Aset.
     */
    public function kinerjaAset()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));

        // Ambil data untuk filter
        $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => 0]);
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0]);

        // Ambil data laporan berdasarkan filter
        $d['laporan'] = $this->model->getLaporanKinerjaAset($d['nav_sess']['search']['data'] ?? []);

        return $this->renderView($this->template . 'kinerja_aset', $d);
    }

    /**
     * Menampilkan laporan Kinerja Teknisi.
     */
    public function kinerjaTeknisi()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));

        // Ambil data untuk filter
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['jabatan_id' => '90', 'deleted_st' => 0]);

        // Ambil data laporan berdasarkan filter
        $d['laporan'] = $this->model->getLaporanKinerjaTeknisi($d['nav_sess']['search']['data'] ?? []);

        return $this->renderView($this->template . 'kinerja_teknisi', $d);
    }

    /**
     * Menampilkan laporan Biaya Pemeliharaan & Perbaikan.
     */
    public function biayaPemeliharaan()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));

        // Ambil data laporan berdasarkan filter
        $d['laporan'] = $this->model->getLaporanBiaya($d['nav_sess']['search']['data'] ?? []);
        $d['total_biaya'] = array_sum(array_column($d['laporan'], 'total_biaya_ok'));

        return $this->renderView($this->template . 'biaya_pemeliharaan', $d);
    }
}
