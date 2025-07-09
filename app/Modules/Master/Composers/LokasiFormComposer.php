<?php

namespace App\Modules\Master\Composers;

use Illuminate\View\View;
use App\Modules\App\Models\DbModel;

class LokasiFormComposer
{
    public function compose(View $view)
    {
        // Logika ini akan selalu berjalan setiap kali view 'form_modal' akan di-render
        $all_parent_lokasi = DbModel::rawData(
            'result_array',
            "SELECT lokasi_id, lokasi_nm, tipe_lokasi FROM mst_lokasi WHERE deleted_st = 0 AND active_st = 1"
        );

        // Mengirimkan variabel 'all_parent_lokasi' secara paksa ke view
        $view->with('all_parent_lokasi', $all_parent_lokasi);
    }
}
