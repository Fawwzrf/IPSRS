<?php

namespace App\Modules\Master\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NavModel extends Model
{
  static function loadDatatables()
  {
    $query = "SELECT * FROM app_nav a";
    $search = ['nav_id', 'nav_nm'];
    $where = null;
    $isWhere = null;

    $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
    return response()->json($result);
  }

  static function fullAccess($id)
  {
    DB::beginTransaction();
    try {
      DbModel::deleteData('app_permission', ['nav_id' => $id]);
      $pegawai = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);
      foreach ($pegawai as $p) {
        $data = [
          'nav_id' => $id,
          'pegawai_id' => $p['pegawai_id'],
        ];
        DbModel::insertData('app_permission', $data);
      }
      DB::commit();
      return true;
    } catch (\Throwable $th) {
      DB::rollBack();
      Log::error('Terjadi kesalahan', [
        'message' => $th->getMessage(),
        'file' => $th->getFile(),
        'line' => $th->getLine(),
      ]);
      return false;
    }
  }
}
