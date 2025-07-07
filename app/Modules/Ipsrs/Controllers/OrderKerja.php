<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\OrderKerjaModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderKerja extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::order_kerja.'; // Lokasi views untuk modul ini
    }

    function index()
    {
        $d = [];
        // Data untuk filter di index (misalnya daftar Jadwal PM dan Aset)
        // Gabungkan asset_nm, no_seri, lokasi_nm untuk dropdown yang informatif
        $sqlAsset = "SELECT a.asset_id, a.asset_nm, a.no_seri, l.lokasi_nm FROM asset a LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id WHERE a.deleted_st = 0 AND a.active_st = 1";
        $d['all_asset'] = DbModel::rawData('result_array', $sqlAsset);

        $d['all_jadwal_pm'] = DbModel::allData('jadwal_pm', ['deleted_st' => '0', 'active_st' => '1']); // Untuk filter jika dibutuhkan

        $d['nav_sess'] = session(request('n'));

        // Hapus filter jenis dari sesi jika tidak ada di request atau di defaultnya
        $s = session(request('n'));
        if (!isset($s['search']['data']['jenis_filter'])) {
            $s['search']['data']['jenis_filter'] = ''; // Clear jika tidak diset dari UI
            session([request('n') => $s]);
        }
        $d['nav_sess'] = session(request('n')); // Update nav_sess untuk view

        return $this->renderView($this->template . 'index', $d);
    }

    // --- HAPUS: Metode pemeliharaan() dan perbaikan() ---
    // function pemeliharaan() { ... } // DIHAPUS
    // function perbaikan() { ... } // DIHAPUS


    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('order_kerja', ['order_kerja_id' => $id]);

        // Ambil Jadwal PM dengan info aset untuk dropdown
        $sqlJadwalPm = "SELECT jp.jadwal_pm_id, jp.frekuensi, jp.jenis as jadwal_jenis,
                        ast.asset_id, ast.asset_nm, ast.no_seri, loc.lokasi_nm
                        FROM jadwal_pm jp
                        LEFT JOIN asset ast ON jp.asset_id = ast.asset_id
                        LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                        WHERE jp.deleted_st = 0 AND jp.active_st = 1";
        $d['all_jadwal_pm'] = DbModel::rawData('result_array', $sqlJadwalPm);

        // Ambil Permintaan Komplain dengan info aset untuk dropdown
        $sqlPermintaan = "SELECT pk.permintaan_id, pk.deskripsi, pk.status as permintaan_status,
                          ast.asset_id, ast.asset_nm, ast.no_seri, loc.lokasi_nm
                          FROM permintaan_komplain pk
                          LEFT JOIN asset ast ON pk.asset_id = ast.asset_id
                          LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                          WHERE pk.deleted_st = 0 AND pk.status = 'baru'"; // Hanya permintaan baru
        $d['all_permintaan_komplain'] = DbModel::rawData('result_array', $sqlPermintaan);


        // Tentukan jenis default dari sesi filter jika ada
        $nav_sess = session(request('n'));
        $d['default_jenis_order'] = @$nav_sess['search']['data']['jenis_filter'];


        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    function save($id = null)
    {
        $d = _post();

        // Validasi Sumber (hanya salah satu jadwal_pm_id atau permintaan_id yang boleh ada)
        if (!empty($d['jadwal_pm_id']) && !empty($d['permintaan_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Hanya boleh memilih Jadwal PM atau Permintaan Komplain, tidak keduanya!']));
        }
        if (empty($d['jadwal_pm_id']) && empty($d['permintaan_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Jadwal PM atau Permintaan Komplain wajib dipilih!']));
        }

        // Validasi ID Jadwal PM jika dipilih
        if (!empty($d['jadwal_pm_id']) && !DbModel::validId('jadwal_pm', 'jadwal_pm_id', $d['jadwal_pm_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Jadwal PM tidak valid!']));
        }
        // Validasi ID Permintaan Komplain jika dipilih
        if (!empty($d['permintaan_id']) && !DbModel::validId('permintaan_komplain', 'permintaan_id', $d['permintaan_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Permintaan Komplain tidak valid!']));
        }

        // Format tanggal (DD-MM-YYYY dari form) ke DB (YYYY-MM-DD)
        if (isset($d['tgl_dibuat']) && $d['tgl_dibuat'] != '') {
            try {
                $d['tgl_dibuat'] = (new \DateTime($d['tgl_dibuat']))->format('Y-m-d');
            } catch (\Exception $e) {
                $d['tgl_dibuat'] = null;
                Log::error('Date conversion failed for tgl_dibuat: ' . $e->getMessage());
            }
        } else {
            $d['tgl_dibuat'] = null;
        }

        if (isset($d['tgl_target_selesai']) && $d['tgl_target_selesai'] != '') {
            try {
                $d['tgl_target_selesai'] = (new \DateTime($d['tgl_target_selesai']))->format('Y-m-d');
            } catch (\Exception $e) {
                $d['tgl_target_selesai'] = null;
                Log::error('Date conversion failed for tgl_target_selesai: ' . $e->getMessage());
            }
        } else {
            $d['tgl_target_selesai'] = null;
        }

        // Set jenis order berdasarkan pilihan sumber
        if (!empty($d['jadwal_pm_id'])) {
            $d['jenis'] = 'pemeliharaan';
        } else if (!empty($d['permintaan_id'])) {
            $d['jenis'] = 'perbaikan';
        } else {
            $d['jenis'] = null; // Seharusnya tidak terjadi karena sudah divalidasi
        }

        // Jika estimasi biaya adalah string kosong, ubah menjadi 0.00
        if (empty($d['estimasi_biaya'])) {
            $d['estimasi_biaya'] = 0.00;
        }

        try {
            DB::beginTransaction();

            if ($id == null) {
                $d['order_kerja_id'] = DbModel::getId('order_kerja', 2, 12); // Generate ID baru
                if (empty($d['order_kerja_id'])) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Gagal membuat ID Order Kerja!']));
                }

                $result = DbModel::insertData('order_kerja', $d);
                if (!$result) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Data order kerja gagal disimpan!']));
                }

                // Update status jadwal PM menjadi 'diproses' jika order kerja dibuat dari Jadwal PM
                if (!empty($d['jadwal_pm_id'])) {
                    DbModel::updateData('jadwal_pm', ['status' => 'diproses'], ['jadwal_pm_id' => $d['jadwal_pm_id']]);
                }
                // Update status permintaan komplain menjadi 'diproses' jika order kerja dibuat dari Permintaan Komplain
                if (!empty($d['permintaan_id'])) {
                    DbModel::updateData('permintaan_komplain', ['status' => 'diproses'], ['permintaan_id' => $d['permintaan_id']]);
                }

                DB::commit();
                return response()->json(_response('01', $this->uri, $d));
            } else {
                // Untuk EDIT: Harus pertimbangkan perubahan sumber dan status terkait
                $oldData = DbModel::getData('order_kerja', ['order_kerja_id' => $id]);

                $result = DbModel::updateData('order_kerja', $d, ['order_kerja_id' => $id]);
                if (!$result) {
                    DB::rollBack();
                    return response()->json(_response('12', $this->uri, ['message' => 'Data order kerja gagal diubah!']));
                }

                // Logika update status jadwal PM/permintaan komplain juga perlu disesuaikan jika sumber berubah atau status OK berubah
                // Contoh: Jika sebelumnya dari JP, dan sekarang OK selesai, update JP status.
                // Atau jika Jadwal PM diubah, update status JP lama dan JP baru.

                DB::commit();
                return response()->json(_response('02', $this->uri, $d));
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error saving order kerja: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $dataToDelete = DbModel::getData('order_kerja', ['order_kerja_id' => $id]);
            if (!$dataToDelete) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data order kerja tidak ditemukan!']));
            }

            // Jika order kerja ini terkait Jadwal PM, kembalikan statusnya (jika perlu)
            if (!empty($dataToDelete['jadwal_pm_id'])) {
                // Periksa status OK, jika 'diproses' atau 'baru', mungkin kembalikan JP ke 'aktif'
                // Jika OK 'selesai' atau 'ditolak', mungkin biarkan JP seperti itu atau set ke 'selesai'
                DbModel::updateData('jadwal_pm', ['status' => 'aktif'], ['jadwal_pm_id' => $dataToDelete['jadwal_pm_id']]);
            }
            // Jika terkait Permintaan Komplain, kembalikan statusnya (jika perlu)
            if (!empty($dataToDelete['permintaan_id'])) {
                DbModel::updateData('permintaan_komplain', ['status' => 'baru'], ['permintaan_id' => $dataToDelete['permintaan_id']]);
            }

            $result = DbModel::deleteData('order_kerja', ['order_kerja_id' => $id]); // Logically delete
            if (!$result) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data order kerja gagal dihapus!']));
            }

            DB::commit();
            return response()->json(_response('03', $this->uri));
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting order kerja: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('13', $this->uri, ['message' => 'Terjadi kesalahan saat menghapus data: ' . $th->getMessage()]));
        }
    }

    public function ajax_datatables()
    {
        return OrderKerjaModel::loadDatatables();
    }
}
