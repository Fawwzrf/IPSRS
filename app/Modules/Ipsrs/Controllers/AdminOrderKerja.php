<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\OrderKerjaModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;
use App\Modules\Ipsrs\Models\LogStatusOrderKerjaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminOrderKerja extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.order_kerja.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);

        // Pindahkan ke model
        $d['all_teknisi'] = $this->model->getAllActiveTeknisi();
        $d['data'] = $this->model->getAllOrderKerja();

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d = [];

        if ($id == null) {
            $d['all_jadwal_pm'] = $this->model->getAvailableJadwalPm();
            $d['all_komplain'] = $this->model->getAvailableKomplain();
            $d['assigned_teknisi'] = [];
        } else {
            $d['main'] = $this->model->getById($id);
            $d['all_jadwal_pm'] = !empty($d['main']['jadwal_pm_id'])
                ? $this->model->getJadwalPmById($d['main']['jadwal_pm_id'])
                : [];
            $d['all_komplain'] = !empty($d['main']['permintaan_id'])
                ? $this->model->getKomplainById($d['main']['permintaan_id'])
                : [];
            $d['assigned_teknisi'] = $this->model->getAssignedTeknisi($id);
        }

        $d['all_teknisi'] = $this->model->getAllActiveTeknisi();
        $d['form_act'] = $this->uri . '/save' . $id;

        return $this->renderView($this->template . 'form_modal', $d);
    }

    /**
     * Menyimpan data order kerja atau log kerja
     */
    public function save($id = null)
    {
        try {
            $d = request()->all();

            $fieldsToRemove = ['_token', '_is_ajax', 'n', 'action'];
            foreach ($fieldsToRemove as $field) {
                if (isset($d[$field])) unset($d[$field]);
            }

            \Log::info('AdminOrderKerja::save input setelah dibersihkan', array_keys($d));

            if (isset($d['action']) && $d['action'] == 'save_log_kerja') {
                $logKerjaModel = new LogKerjaModel();
                $result = $logKerjaModel->saveData($id, $d);

                if ($result['status']) {
                    return response()->json(_response('01', $this->uri, [
                        'message'      => 'Laporan kerja berhasil disimpan.',
                        'log_kerja_id' => $result['log_kerja_id'] ?? null
                    ]));
                } else {
                    return response()->json(_response('11', $this->uri, [
                        'message' => $result['message'] ?? 'Gagal menyimpan laporan kerja.'
                    ]));
                }
            } else {
                if (isset($d['pegawai_ids']) && !empty($d['pegawai_ids'])) {
                    $d['teknisi'] = $d['pegawai_ids'];
                    unset($d['pegawai_ids']);
                }

                if (isset($d['tgl_dibuat'])) {
                    $d['tgl_dibuat'] = to_date($d['tgl_dibuat'], '-', '-', '-');
                }
                if (isset($d['tgl_target_selesai'])) {
                    $d['tgl_target_selesai'] = to_date($d['tgl_target_selesai'], '-', '-', '-');
                }

                if (empty($d['catatan']) && empty($d['deskripsi'])) {
                    if (!empty($d['jadwal_pm_id'])) {
                        $jadwal = $this->model->getJadwalPmById($d['jadwal_pm_id']);
                        if ($jadwal) {
                            $d['catatan'] = 'Pemeliharaan: ' . $jadwal['jenis'];
                            $d['jenis'] = 'pemeliharaan';
                        }
                    } elseif (!empty($d['permintaan_id'])) {
                        $permintaan = $this->model->getKomplainById($d['permintaan_id']);
                        if ($permintaan) {
                            $d['catatan'] = $permintaan['deskripsi'] ?? '';
                            $d['jenis'] = 'perbaikan';
                        }
                    }
                    if (empty($d['catatan'])) {
                        $d['catatan'] = 'Order kerja baru dibuat tanggal ' . date('d-m-Y');
                    }
                } elseif (!empty($d['deskripsi']) && empty($d['catatan'])) {
                    $d['catatan'] = $d['deskripsi'];
                    unset($d['deskripsi']);
                }

                $result = $this->model->saveData($id, $d);

                if ($result['status']) {
                    $response_code = ($result['mode'] == 'insert') ? '01' : '02';
                    $response_data = [
                        'order_kerja_id' => $result['order_kerja_id'] ?? $id,
                        'message'        => ($result['mode'] == 'insert')
                            ? 'Order kerja berhasil dibuat.'
                            : 'Order kerja berhasil diperbarui.'
                    ];
                    return response()->json(_response($response_code, $this->uri, $response_data));
                } else {
                    return response()->json(_response('11', $this->uri, [
                        'message' => $result['message'] ?? 'Gagal menyimpan order kerja.'
                    ]));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in AdminOrderKerja::save: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(_response('11', $this->uri, [
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]));
        }
    }

    public function delete($id)
    {
        $result = $this->model->deleteData($id);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return OrderKerjaModel::loadDatatables();
    }

    /**
     * Update status order kerja
     */
    public function updateStatus(Request $request)
    {
        $order_kerja_id = $request->input('order_kerja_id');
        $status_baru    = $request->input('status_baru');
        $keterangan     = $request->input('keterangan');

        try {
            DB::beginTransaction();

            $status_lama = OrderKerjaModel::getStatusById($order_kerja_id);
            $pegawai_id  = session('pegawai_id');

            $updateQuery = "UPDATE trx_order_kerja SET 
                                status = ?,
                                updated_at = ?,
                                updated_by = ?
                            WHERE order_kerja_id = ?";

            DB::update($updateQuery, [
                $status_baru,
                date('Y-m-d H:i:s'),
                session('user_id'),
                $order_kerja_id
            ]);

            $logStatusModel = new LogStatusOrderKerjaModel();
            $result = $logStatusModel->logPerubahanStatus(
                $order_kerja_id,
                $status_lama,
                $status_baru,
                $pegawai_id,
                $keterangan
            );

            if (!$result['status']) {
                DB::rollBack();
                return response()->json($result);
            }

            DB::commit();
            return response()->json(_response('02', $this->uri, ['message' => 'Status berhasil diperbarui']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(_response('01', $this->uri, [
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * Menampilkan form update status
     */
    public function update_status_form($order_kerja_id)
    {
        $d['order_kerja'] = $this->model->getOrderKerjaById($order_kerja_id);

        if (!$d['order_kerja']) {
            return '<div class="alert alert-danger">Data Order Kerja tidak ditemukan.</div>';
        }

        return $this->renderView('ipsrs::admin.pekerjaan.order_kerja.update_status_modal', $d);
    }

    public function hasil_teknisi_modal($order_kerja_id)
    {
        $d = $this->model->getHasilTeknisiModalData($order_kerja_id);
        return $this->renderView('ipsrs::admin.pekerjaan.order_kerja.hasil_teknisi_modal', $d);
    }
}
