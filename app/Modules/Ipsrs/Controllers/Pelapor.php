<?php

namespace App\Modules\Ipsrs\Controllers;

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
        $this->template = 'ipsrs::pelapor.';
    }

    /**
     * Menampilkan halaman utama pelapor dan riwayat komplain.
     */
    public function index()
    {
        $pegawai_id = session('pegawai_id');
        $d = [];

        $d['history'] = $this->model->getHistoryByPegawai($pegawai_id);

        return $this->renderView($this->template . 'index', $d);
    }

    /**
     * Menampilkan form modal komplain beserta data yang dibutuhkan.
     */
    public function form_komplain_modal()
    {
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1], 'lokasi_nm ASC');
        $d['all_asset'] = DbModel::allData('mst_asset', ['deleted_st' => 0, 'active_st' => 1], 'asset_nm ASC');
        $d['form_act'] = url('ipsrs/pelapor/save');
        $d['pegawai_id'] = session('pegawai_id');

        return $this->renderView($this->template . 'form_komplain_modal', $d);
    }

    /**
     * Menyimpan permintaan komplain dari pelapor.
     */
    public function save()
    {
        try {
            $d = _post();
            $d['tgl'] = date('Y-m-d');
            $d['status'] = PelaporModel::STATUS_BARU;
            $d['created_at'] = date('Y-m-d H:i:s');
            $d['created_by'] = session('nama_pegawai') ?? 'Pelapor';

            if (request()->hasFile('foto_file')) {
                $upload = upload_file('komplain', 'foto_file');
                if ($upload['status']) {
                    $d['foto_url'] = $upload['data'];
                }
            }

            // Validasi pegawai_id
            if (empty($d['pegawai_id'])) {
                return response()->json(_response('20', null, ['message' => 'Pegawai ID wajib diisi']));
            }

            $save = \App\Modules\Ipsrs\Models\PermintaanKomplainModel::saveData(null, $d);

            if ($save['status']) {
                return response()->json(_response('01', 'ipsrs/pelapor/index', $save));
            } else {
                return response()->json(_response('11', null, $save));
            }
        } catch (\Exception $e) {
            \Log::error('Error in Pelapor::save - ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(_response('29', null, ['message' => $e->getMessage()]));
        }
    }

    /**
     * Mendapatkan data tabel komplain untuk refresh AJAX.
     */
    public function get_table_data()
    {
        $pegawai_id = session('pegawai_id');
        $limit = 10;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $limit;

        $model = new PelaporModel();
        $d['history'] = $model->getHistoryByPegawai($pegawai_id, $limit, $offset);
        $total = $model->countHistoryByPegawai($pegawai_id);

        $d['pagination'] = [
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => ceil($total / $limit)
        ];

        return $this->renderPartial('table_komplain', $d);
    }
}
