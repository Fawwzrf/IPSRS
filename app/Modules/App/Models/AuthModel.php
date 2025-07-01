<?php

namespace App\Modules\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuthModel extends Model
{
  static function getUser($user_nm)
  {
    $sql = "SELECT 
              a.*, 
              b.jabatan_nm 
            FROM mst_pegawai a
            LEFT JOIN mst_jabatan b ON a.jabatan_id = b.jabatan_id
            WHERE a.user_nm = '$user_nm'";
    $result = DB::selectOne($sql);
    return $result;
  }
}
