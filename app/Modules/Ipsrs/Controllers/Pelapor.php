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
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1], 'lokasi_nm ASC');

        // Mengambil semua aset aktif untuk difilter oleh JavaScript
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1], 'asset_nm ASC');

        // Gunakan rute pelapor yang benar sesuai routing system
        $d['form_act'] = url('ipsrs/pelapor/save');

        // Tambahkan data pegawai_id untuk prefill form
        $d['pegawai_id'] = session('pegawai_id');

        return $this->renderView($this->template . 'form_komplain_modal', $d);
    }

    /**
     * Menyimpan permintaan komplain dari pelapor
     */
    public function save()
    {
        try {
            // Ambil semua input dari form
            $d = _post();
            
            // PERBAIKAN: Pastikan tanggal dalam format yang benar (YYYY-MM-DD)
            $d['tgl'] = date('Y-m-d');
            
            // Tambahkan data wajib lainnya
            $d['status'] = 'baru';
            $d['created_at'] = date('Y-m-d H:i:s');
            $d['created_by'] = session('nama_pegawai') ?? 'Pelapor';
            
            // Upload foto jika ada
            if (request()->hasFile('foto_file')) {
                $upload = upload_file('komplain', 'foto_file');
                if ($upload['status']) {
                    $d['foto_url'] = $upload['data'];
                }
            }
            
            // Simpan menggunakan model yang sesuai
            $save = \App\Modules\Ipsrs\Models\PermintaanKomplainModel::saveData(null, $d);
            
            if ($save['status']) {
                // PENTING: Pastikan URI berisi rute yang benar untuk redirect
                $response = _response('01', 'ipsrs/pelapor/index', $save);
                return response()->json($response);
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
     * Mendapatkan data tabel komplain untuk refresh AJAX
     */
    public function get_table_data()
    {
        // Ambil data history komplain untuk pegawai yang login
        $pegawai_id = session('pegawai_id');
        
        // Jumlah per halaman
        $limit = 10;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $limit;
        
        // Model untuk ambil data
        $model = new PelaporModel();
        $d['history'] = $model->getHistoryByPegawai($pegawai_id, $limit, $offset);
        $total = $model->countHistoryByPegawai($pegawai_id);
        
        // Data untuk pagination
        $d['pagination'] = [
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => ceil($total / $limit)
        ];
        
        // Render tabel saja, bukan layout lengkap
        return $this->renderPartial('table_komplain', $d);
    }
}
