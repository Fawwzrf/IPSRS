<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;

class AdminLogKerja extends MyController
{
    protected $model;
    
    public function __construct()
    {
        parent::__construct();
        $this->model = new LogKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.log_kerja.';
    }

    public function form_modal($order_kerja_id = null)
    {
        // Validasi input ID
        if (!$order_kerja_id || !is_numeric($order_kerja_id)) {
            return '<div class="alert alert-danger">Error: Order Kerja ID tidak valid.</div>';
        }
        
        // Validasi keberadaan order kerja
        $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
        if (!$order_kerja) {
            return '<div class="alert alert-warning">Order Kerja tidak ditemukan.</div>';
        }
        
        $d['order_kerja'] = $order_kerja;
        $d['log_kerja'] = DbModel::getData('log_kerja', ['order_kerja_id' => $order_kerja_id, 'deleted_st' => 0]);
        $d['form_act'] = url('ipsrs/adminlogkerja/save/' . $order_kerja_id);
        
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function form_view_log_modal($order_kerja_id = null)
    {
        // Validasi input ID
        if (!$order_kerja_id || !is_numeric($order_kerja_id)) {
            return '<div class="alert alert-danger">Error: Order Kerja ID tidak valid.</div>';
        }

        // Ambil data log kerja utama
        $d['log'] = $this->model->getLogByOrderId($order_kerja_id);

        if (!$d['log']) {
            return '<div class="alert alert-info">Laporan kerja untuk Order Kerja ini belum dibuat.</div>';
        }

        // Ambil data foto dan sparepart jika log ditemukan
        $d['photos'] = $this->model->getPhotosByLogId($d['log']['log_kerja_id']);
        $d['spareparts'] = $this->model->getSparepartsByLogId($d['log']['log_kerja_id']);

        return $this->renderView($this->template . 'view_log_modal', $d);
    }

    public function save($order_kerja_id)
    {
        try {
            // Validasi Order Kerja ID
            if (!$order_kerja_id || !is_numeric($order_kerja_id)) {
                return response()->json(_response('11', $this->uri, ['message' => 'Order Kerja ID tidak valid.']));
            }

            $d = _post();
            
            // Validasi input wajib
            $validation_errors = $this->validateInput($d);
            if (!empty($validation_errors)) {
                return response()->json(_response('11', $this->uri, ['message' => implode(', ', $validation_errors)]));
            }

            // Validasi keberadaan order kerja
            $order_kerja = DbModel::getData('order_kerja', ['order_kerja_id' => $order_kerja_id]);
            if (!$order_kerja) {
                return response()->json(_response('11', $this->uri, ['message' => 'Order Kerja tidak ditemukan.']));
            }

            // Validasi file upload jika ada
            if (request()->hasFile('foto_pekerjaan')) {
                $file_validation = $this->validateFileUpload(request()->file('foto_pekerjaan'));
                if (!$file_validation['status']) {
                    return response()->json(_response('11', $this->uri, ['message' => $file_validation['message']]));
                }
            }

            // Simpan data
            $result = $this->model->saveData($order_kerja_id, $d);
            
            if ($result['status']) {
                return response()->json(_response('01', $this->uri, ['message' => 'Laporan kerja berhasil disimpan.']));
            } else {
                return response()->json(_response('11', $this->uri, ['message' => $result['message']]));
            }
            
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error saving log kerja: ' . $e->getMessage());
            return response()->json(_response('11', $this->uri, ['message' => 'Terjadi kesalahan sistem. Silakan coba lagi.']));
        }
    }

    /**
     * Validasi input data
     */
    private function validateInput($data)
    {
        $errors = [];

        // Validasi tindakan
        if (empty($data['tindakan'])) {
            $errors[] = 'Tindakan wajib diisi';
        } elseif (strlen($data['tindakan']) < 10) {
            $errors[] = 'Tindakan minimal 10 karakter';
        }

        // Validasi hasil
        if (empty($data['hasil'])) {
            $errors[] = 'Hasil pekerjaan wajib dipilih';
        } elseif (!in_array($data['hasil'], ['selesai', 'perlu_tindak_lanjut', 'ditunda', 'gagal'])) {
            $errors[] = 'Hasil pekerjaan tidak valid';
        }

        // Validasi tanggal kerja
        if (empty($data['tgl_kerja'])) {
            $errors[] = 'Tanggal kerja wajib diisi';
        } elseif (!$this->validateDate($data['tgl_kerja'])) {
            $errors[] = 'Format tanggal kerja tidak valid';
        }

        // Validasi waktu mulai dan selesai
        if (!empty($data['waktu_mulai']) && !empty($data['waktu_selesai'])) {
            if (strtotime($data['waktu_mulai']) >= strtotime($data['waktu_selesai'])) {
                $errors[] = 'Waktu selesai harus lebih besar dari waktu mulai';
            }
        }

        // Validasi durasi kerja
        if (!empty($data['durasi_kerja']) && (!is_numeric($data['durasi_kerja']) || $data['durasi_kerja'] <= 0)) {
            $errors[] = 'Durasi kerja harus berupa angka positif';
        }

        return $errors;
    }

    /**
     * Validasi tanggal
     */
    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validasi file upload
     */
    private function validateFileUpload($files)
    {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // Validasi ukuran file
            if ($file->getSize() > $max_size) {
                return ['status' => false, 'message' => 'Ukuran file maksimal 5MB'];
            }

            // Validasi ekstensi file
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowed_extensions)) {
                return ['status' => false, 'message' => 'Format file harus JPG, JPEG, PNG, atau GIF'];
            }

            // Validasi MIME type
            $mime_type = $file->getMimeType();
            if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                return ['status' => false, 'message' => 'File harus berupa gambar'];
            }
        }

        return ['status' => true];
    }

    /**
     * Method untuk soft delete log kerja
     */
    public function delete($log_kerja_id)
    {
        try {
            if (!$log_kerja_id || !is_numeric($log_kerja_id)) {
                return response()->json(_response('11', $this->uri, ['message' => 'ID Log Kerja tidak valid.']));
            }

            $result = $this->model->deleteData($log_kerja_id);
            
            if ($result['status']) {
                return response()->json(_response('01', $this->uri, ['message' => 'Log kerja berhasil dihapus.']));
            } else {
                return response()->json(_response('11', $this->uri, ['message' => $result['message']]));
            }
            
        } catch (\Exception $e) {
            \Log::error('Error deleting log kerja: ' . $e->getMessage());
            return response()->json(_response('11', $this->uri, ['message' => 'Terjadi kesalahan sistem.']));
        }
    }
}