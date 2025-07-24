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

        // Data untuk filter
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', [
            'deleted_st' => 0,
            'active_st' => 1,
            'jabatan_id' => '90'
        ]);
        $d['search_act'] = $this->uri . '/search';

        // Ambil session filter
        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    public function search()
    {
        // Simpan filter ke session
        $d = _post();
        $this->save_session_search($d);
        return redirect($this->uri);
    }

    public function form_modal($log_kerja_id = null)
    {
        $d = [];
        $d['main'] = [];
        $d['log_fotos'] = [];
        $d['all_order_kerja'] = $this->model->getAllOrderKerja() ?: [];
        $d['all_teknisi'] = $this->model->getAllTeknisi() ?: [];
        $d['all_sparepart'] = $this->model->getAllSparepart() ?: [];
        $d['form_act'] = $this->uri . '/save';

        // Jika edit log kerja
        if ($log_kerja_id) {
            $d['main'] = $this->model->getLogById($log_kerja_id);
            $d['log_fotos'] = $this->model->getPhotosByLogId($log_kerja_id);
            $d['form_act'] = $this->uri . '/save/' . $log_kerja_id;
        }

        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($log_kerja_id = null)
    {
        $d = _post();

        // Ambil order_kerja_id dari input (bukan dari parameter)
        $order_kerja_id = $d['order_kerja_id'] ?? null;

        // Validasi
        if (empty($order_kerja_id)) {
            return response()->json(_response('11', $this->uri, ['message' => 'Order Kerja wajib dipilih.']));
        }
        if (empty($d['tindakan'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Tindakan yang dilakukan wajib diisi.']));
        }
        if (empty($d['hasil'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Hasil pekerjaan wajib dipilih.']));
        }

        // Gabungkan sparepart_id[] dan jumlah[] menjadi array
        $spareparts = [];
        if (!empty($d['sparepart_id']) && is_array($d['sparepart_id'])) {
            foreach ($d['sparepart_id'] as $i => $sp_id) {
                if ($sp_id) {
                    $spareparts[] = [
                        'sparepart_id' => $sp_id,
                        'jumlah' => $d['jumlah'][$i] ?? 1
                    ];
                }
            }
        }
        $d['sparepart'] = $spareparts;

        try {
            // Kirim ke model, pastikan data sparepart dan teknisi_pegawai_id juga dikirim
            $result = $this->model->saveData($order_kerja_id, $d);

            if ($result['status']) {
                return response()->json(_response('01', $this->uri, [
                    'message' => 'Laporan pekerjaan berhasil disimpan.',
                    'asset_id' => $d['asset_id'] ?? null
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

    public function detail_modal($order_kerja_id)
    {
        $penugasan = $this->model->getPenugasanByOrderKerja($order_kerja_id);
        $log_kerja = $this->model->getLogByOrderKerja($order_kerja_id);
        foreach ($log_kerja as &$log) {
            $log['sparepart'] = $this->model->getSparepartByLogKerja($log['log_kerja_id']);
            $log['fotos'] = $this->model->getPhotosByLogId($log['log_kerja_id']);
        }
        return view($this->template . 'hasil_teknisi_modal', compact('penugasan', 'log_kerja'));
    }
}
