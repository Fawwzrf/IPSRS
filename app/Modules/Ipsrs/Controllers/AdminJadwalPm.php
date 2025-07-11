<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\JadwalPmModel;
use Carbon\Carbon; // 💡 PASTIKAN Carbon di-import di bagian atas

class AdminJadwalPm extends MyController
{
    public function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::admin.pekerjaan.jadwal_pm.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $id]);
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['form_act'] = $id ? url('ipsrs/adminjadwalpm/save/' . $id) : url('ipsrs/adminjadwalpm/save');
        return $this->renderView($this->template . 'form_modal', $d);
    }

    /**
     * =================================================================
     * START PERBAIKAN: Method `save` yang sudah disempurnakan
     * =================================================================
     */
    public function save($id = null)
    {
        $d = _post();

        // 1. Validasi Backend yang lebih solid
        if (empty($d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Aset wajib dipilih!']));
        }
        if (empty($d['frekuensi'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Frekuensi wajib dipilih!']));
        }
        if (empty($d['tgl_terakhir'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Tanggal Terakhir PM wajib diisi sebagai dasar kalkulasi.']));
        }

        // 2. Kalkulasi tgl_berikutnya secara otomatis
        try {
            // Ambil tanggal terakhir dari form (format DD-MM-YYYY)
            $tglTerakhir = Carbon::createFromFormat('d-m-Y', $d['tgl_terakhir']);
            $tglBerikutnya = clone $tglTerakhir; // Duplikasi objek untuk kalkulasi

            // Lakukan penambahan waktu sesuai frekuensi
            switch ($d['frekuensi']) {
                case 'Harian':
                    $tglBerikutnya->addDay();
                    break;
                case 'Mingguan':
                    $tglBerikutnya->addWeek();
                    break;
                case 'Bulanan':
                    $tglBerikutnya->addMonth();
                    break;
                case 'Kuartalan':
                    $tglBerikutnya->addMonths(3);
                    break;
                case 'Tahunan':
                    $tglBerikutnya->addYear();
                    break;
                default:
                    $tglBerikutnya = null;
                    break; // Default jika frekuensi tidak valid
            }

            // Masukkan tanggal yang sudah dihitung ke dalam array data untuk disimpan
            $d['tgl_berikutnya'] = $tglBerikutnya ? $tglBerikutnya->format('Y-m-d') : null;
        } catch (\Exception $e) {
            // Tangani jika format tanggal yang diinput salah
            return response()->json(_response('10', $this->uri, ['message' => 'Format Tanggal Terakhir tidak valid.']));
        }

        // 3. Format 'tgl_terakhir' agar sesuai dengan format database (Y-m-d)
        $d['tgl_terakhir'] = to_date($d['tgl_terakhir'], '-', 'date');

        // 4. Proses Simpan ke Database menggunakan DbModel
        if ($id == null) {
            // Mode Insert: Generate ID baru jika belum ada
            if (empty($d['jadwal_pm_id'])) {
                $d['jadwal_pm_id'] = DbModel::getId('jadwal_pm', 2, 12);
            }
            $result = DbModel::insertData('jadwal_pm', $d);
            return response()->json(_response($result ? '01' : '11', $this->uri, $d));
        } else {
            // Mode Update
            $result = DbModel::updateData('jadwal_pm', $d, ['jadwal_pm_id' => $id]);
            return response()->json(_response($result ? '02' : '12', $this->uri, $d));
        }
    }
    /**
     * =================================================================
     * END PERBAIKAN
     * =================================================================
     */

    public function delete($id)
    {
        if (DbModel::getData('order_kerja', ['jadwal_pm_id' => $id, 'deleted_st' => 0])) {
            return response()->json(_response('13', $this->uri, ['message' => 'Jadwal ini sudah digunakan di Order Kerja.']));
        }
        $result = DbModel::deleteData('jadwal_pm', ['jadwal_pm_id' => $id]);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return JadwalPmModel::loadDatatables();
    }
}
