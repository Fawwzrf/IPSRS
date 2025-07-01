<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class JabatanModel extends Model
{
  static function loadDatatables()
  {
    $query = "SELECT * FROM mst_jabatan a";
    $search = ['jabatan_id', 'jabatan_nm'];
    $where = null;
    $isWhere = null;

    $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
    return response()->json($result);
  }
}
