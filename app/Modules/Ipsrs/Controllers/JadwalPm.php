<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\JadwalPmModel; // Perbaiki namespace: use App\Modules\Ipsrs\Models\JadwalPmModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Dipertahankan sesuai pola

class JadwalPm extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::jadwal_pm.'; // Lokasi views untuk modul ini
    }

    function index()
    {
        $d = [];
        // Data untuk filter di index
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => '0', 'active_st' => '1']);
        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $id]);
        $sql = "SELECT
                    a.asset_id, a.asset_nm, a.no_seri, a.active_st,
                    l.lokasi_nm
                FROM asset a
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                WHERE a.deleted_st = 0 AND a.active_st = 1";
        $d['all_asset'] = DbModel::rawData('result_array', $sql);
        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    function save($id = null)
    {
        $d = _post();

        if (isset($d['tgl_terakhir']) && $d['tgl_terakhir'] != '') {
            try {
                $d['tgl_terakhir'] = (new \DateTime($d['tgl_terakhir']))->format('Y-m-d');
            } catch (\Exception $e) {
                $d['tgl_terakhir'] = null;
                Log::error('Date conversion failed for tgl_terakhir: ' . $e->getMessage());
            }
        } else {
            $d['tgl_terakhir'] = null;
        }

        if (isset($d['tgl_berikutnya']) && $d['tgl_berikutnya'] != '') {
            try {
                $d['tgl_berikutnya'] = (new \DateTime($d['tgl_berikutnya']))->format('Y-m-d');
            } catch (\Exception $e) {
                $d['tgl_berikutnya'] = null;
                Log::error('Date conversion failed for tgl_berikutnya: ' . $e->getMessage());
            }
        } else {
            $d['tgl_berikutnya'] = null;
        }


        // Validasi ID Aset
        if (empty($d['asset_id']) || !DbModel::validId('asset', 'asset_id', $d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Aset tidak valid atau kosong!']));
        }
        // Validasi Frekuensi dan Jenis
        if (empty($d['frekuensi'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Frekuensi wajib dipilih!']));
        }
        if (empty($d['jenis'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Jenis Pemeliharaan wajib dipilih!']));
        }

        try {
            if ($id == null) {
                $d['jadwal_pm_id'] = DbModel::getId('jadwal_pm', 2, 12); // Generate ID baru
                if (empty($d['jadwal_pm_id'])) {
                    return response()->json(_response('11', $this->uri, ['message' => 'Gagal membuat ID Jadwal PM!']));
                }

                $result = DbModel::insertData('jadwal_pm', $d);
                if ($result) {
                    return response()->json(_response('01', $this->uri, $d));
                } else {
                    return response()->json(_response('11', $this->uri, $d));
                }
            } else {
                $result = DbModel::updateData('jadwal_pm', $d, ['jadwal_pm_id' => $id]);
                if ($result) {
                    return response()->json(_response('02', $this->uri, $d));
                } else {
                    return response()->json(_response('12', $this->uri, $d));
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error saving jadwal pm: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        // Validasi integritas data sebelum menghapus
        // Periksa apakah jadwal PM ini masih terhubung dengan order_kerja
        $hasOrders = DbModel::getData('order_kerja', ['jadwal_pm_id' => $id, 'deleted_st' => 0]);
        if ($hasOrders) {
            return response()->json(_response('13', $this->uri, ['message' => 'Jadwal PM ini memiliki Order Kerja terkait dan tidak dapat dihapus.']));
        }

        $result = DbModel::deleteData('jadwal_pm', ['jadwal_pm_id' => $id]); // Logically delete
        if ($result) {
            return response()->json(_response('03', $this->uri));
        } else {
            return response()->json(_response('13', $this->uri));
        }
    }

    public function ajax_datatables()
    {
        return JadwalPmModel::loadDatatables();
    }
}
