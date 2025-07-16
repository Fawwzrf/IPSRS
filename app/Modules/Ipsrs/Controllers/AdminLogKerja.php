<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;

class AdminLogKerja extends MyController
{
    public function __construct()
    {
        parent::__construct();
        // Path view disederhanakan agar lebih mudah ditemukan
        $this->template = 'ipsrs::admin.pekerjaan.log_kerja.';
    }


    public function form_modal($order_kerja_id = null)
    {
        if (!$order_kerja_id) return '<h5>Error: Order Kerja ID tidak valid.</h5>';

        $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
        if (!$order_kerja) return '<h5>Error: Data Order Kerja tidak ditemukan.</h5>';

        $d['order_kerja'] = $order_kerja;

        // Ambil asset_id untuk keperluan redirect
        $asset_id = null;
        if (!empty($order_kerja['permintaan_id'])) {
            $sumber = DbModel::getData('permintaan_komplain', ['permintaan_id' => $order_kerja['permintaan_id']]);
            $asset_id = $sumber['asset_id'] ?? null;
        } else {
            $sumber = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $order_kerja['jadwal_pm_id']]);
            $asset_id = $sumber['asset_id'] ?? null;
        }
        $d['asset_id'] = $asset_id; // <-- Perbaikan: simpan asset_id di $d, bukan di $order_kerja

        $d['log_kerja'] = DbModel::getData('log_kerja', ['order_kerja_id' => $order_kerja_id, 'deleted_st' => 0]);
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);

        // Form action sekarang menunjuk ke rute yang benar dan pasti ada
        $d['form_act'] = url('ipsrs/adminorderkerja/save/' . $order_kerja_id);

        // URL redirect setelah berhasil disimpan
        $d['redirect_url'] = $asset_id ? url('master/asset/detail/' . $asset_id) : url('ipsrs/adminorderkerja');

        return $this->renderView($this->template . 'form_modal', $d);
    }
}
