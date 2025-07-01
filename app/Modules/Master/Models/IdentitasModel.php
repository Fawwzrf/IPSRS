<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class IdentitasModel extends Model
{
  static function getData()
  {
    $sql = "SELECT 
              a.*, b.pegawai_nm as kepala_nm
            FROM mst_identitas a
            LEFT JOIN mst_pegawai b ON a.kepala_id = b.pegawai_id";
    $result = DbModel::rawData('row_array', $sql);
    return $result;
  }
}
