<?php

namespace App\Modules\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DbModel extends Model
{
  static function allData($table, $params = array())
  {
    $result = DB::table($table)->where($params)->get()->all();
    return json_decode(json_encode($result), true);
  }

  static function getData($table, $params = array())
  {
    $result = DB::table($table)->where($params)->get()->first();
    return json_decode(json_encode($result), true);
  }

  static function validId($table, $field, $value)
  {
    $return = self::getData($table, [$field => $value]);
    if ($return != false) {
      return true;
    } else {
      return false;
    }
  }

  static function insertData($table, $d)
  {
    $d['created_at'] = date('Y-m-d H:i:s');
    $d['created_by'] = session('pegawai_nm');

    try {
      DB::table($table)->insert($d);
      return true;
    } catch (\Throwable $th) {
      Log::error('Terjadi kesalahan', [
        'message' => $th->getMessage(),
        'file' => $th->getFile(),
        'line' => $th->getLine(),
      ]);
      return false;
    }
  }

  static function updateData($table, $d, $where)
  {
    $d['updated_at'] = date('Y-m-d H:i:s');
    $d['updated_by'] = session('pegawai_nm');

    try {
      DB::table($table)->where($where)->update($d);
      return true;
    } catch (\Throwable $th) {
      Log::error('Terjadi kesalahan', [
        'message' => $th->getMessage(),
        'file' => $th->getFile(),
        'line' => $th->getLine(),
      ]);
      return false;
    }
  }

  static function deleteData($table, $where)
  {
    try {
      DB::table($table)->where($where)->delete();
      return true;
    } catch (\Throwable $th) {
      Log::error('Terjadi kesalahan', [
        'message' => $th->getMessage(),
        'file' => $th->getFile(),
        'line' => $th->getLine(),
      ]);
      return false;
    }
  }

  public static function rawData($init, $query, $params = [])
  {
    try {
      $result = null;
      switch ($init) {
        case 'result_array':
          $result = DB::select($query, $params);
          break;
        case 'result':
          $result = DB::select($query, $params);
          break;
        case 'row_array':
          $result = DB::selectOne($query, $params);
          break;
        case 'row':
          $result = DB::selectOne($query, $params);
          break;
        case 'num_rows':
          $result = DB::select($query, $params);
          break;
        default:
          $result = DB::selectOne($query, $params);
          break;
      }
      return json_decode(json_encode($result), true);
    } catch (\Throwable $th) {
      Log::error('Terjadi kesalahan', [
        'message' => $th->getMessage(),
        'file' => $th->getFile(),
        'line' => $th->getLine(),
      ]);
      return false;
    }
  }

  static function datatablesQuery($query, $keyword, $where, $iswhere = null)
  {
    // Params
    $d = _post();

    $_search_value = @$d['search']['value'];
    $_length = @$d['length'];
    $_start = @$d['start'];
    $_order_field = @$d['order'][0]['column'];
    $_order_ascdesc = @$d['order'][0]['dir'];

    // 
    // Ambil data yang di ketik user pada textbox pencarian
    $search = htmlspecialchars($_search_value);
    $search = strtolower($search);
    // 
    // Ambil data limit per page
    $limit = preg_replace("/[^a-zA-Z0-9.]/", '', "{$_length}");
    // 
    // Ambil data start
    $start = preg_replace("/[^a-zA-Z0-9.]/", '', "{$_start}");
    //
    // Lower Keywoard
    if (is_array($keyword)) {
      foreach ($keyword as $k => $v) {
        $keyword[$k] = "LOWER(" . $v . ")";
      }
    }

    $strWhere = " WHERE ";

    if ($iswhere != null) {
      if (strtolower(substr(@$iswhere, 0, 3)) == "and" || @$iswhere == "") {
        $strWhere .= '1 = 1 ';
      } else {
        $strWhere .= ' ';
      }

      $strWhere .= $iswhere;
    } else {
      $strWhere .= '1 = 1 ';
    }

    if ($where != null) {
      $setWhere = array();
      foreach ($where as $key => $value) {
        $setWhere[] = $key . "='" . $value . "'";
      }
      $fwhere = implode(' AND ', $setWhere);
      $strWhere .= " AND " . $fwhere;
    }

    // Untuk mengambil nama field yg menjadi acuan untuk sorting
    $strOrder = " ORDER BY " . @$d['columns'][$_order_field]['data'] . " " . $_order_ascdesc;

    $queryData = $query . $strWhere;
    $queryAllRecords = str_replace_between($queryData, 'SELECT', 'FROM', ' COUNT(1) AS count ');

    // Searching by keyword
    if ($keyword != null && @count($keyword) > 0) {
      $strWhereKeyword = $strWhere;
      $strKeyword = implode(" LIKE '%" . $search . "%' OR ", $keyword) . " LIKE '%" . $search . "%'";
      $strWhereKeyword .= " AND (" . $strKeyword . ") ";

      $queryData = $query . $strWhereKeyword . $strOrder;
      $queryFiltered = $query . $strWhereKeyword;
    } else {
      $queryData = $query . $strWhere . $strOrder;
      $queryFiltered = $query . $strWhere;
    }

    $queryData .= " LIMIT " . $limit . " OFFSET " . $start;

    $data = self::rawData('result_array', $queryData);
    $recordsTotal = self::rawData('row_array', $queryAllRecords)['count'];

    if ($keyword != null && @count($keyword) > 0) {
      $queryRecordsFiltered = str_replace_between($queryFiltered, 'SELECT', 'FROM', ' COUNT(1) AS count ');
      $recordsFiltered = self::rawData('row_array', $queryRecordsFiltered)['count'];
    } else {
      $recordsFiltered = $recordsTotal;
    }

    $callback = array(
      'draw' => $d['draw'],
      'recordsTotal' => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data' => $data
    );

    return $callback;
  }

  // GET ID
  public static function getId($modul = null, $type = 1, $length = 12)
  {
    $table = 'tmp_id';
    $pk = self::rawData('row_array', "SHOW KEYS FROM $modul WHERE Key_name = 'PRIMARY'");
    $pk_id = @$pk['Column_name'];
    if ($type == 1) {
      $id = self::getData($table, ['modul' => $modul, 'tgl_id' => date('Y-m-d')]);
      if (@$id['modul'] == '') {
        $result = date('ymd') . '000001';
      } else {
        $result = $id['no_id'] + 1;
      }

      $last = self::getData($modul, [$pk_id => strval($result)]);
      while ($last != null) {
        $result = $last[$pk_id] + 1;
        $last = self::getData($modul, [$pk_id => strval($result)]);
      }
    } else if ($type == 2) {
      $id = self::getData($table, ['modul' => $modul]);
      if (@$id['modul'] == '') {
        $result = str_pad('1', $length, '0', STR_PAD_LEFT);
      } else {
        $result = str_pad(intval($id['no_id']) + 1, $length, '0', STR_PAD_LEFT);
      }

      $last = self::getData($modul, [$pk_id => strval($result)]);
      while ($last != null) {
        $result = str_pad(intval($last[$pk_id]) + 1, $length, '0', STR_PAD_LEFT);
        $last = self::getData($modul, [$pk_id => strval($result)]);
      }
    } else {
      $id = self::getData($table, ['modul' => $modul]);
      if (@$id['modul'] == '') {
        $result = date('ymd') . '000001';
      } else {
        $last = substr($id['no_id'], 8, 99);
        $result = date('ymd') . str_pad(intval($last) + 1, 4, '0', STR_PAD_LEFT);
      }
    }
    // 
    // @auto update id
    $result_id = strval($result);
    self::updateId($modul, $result_id);
    // 
    return $result_id;
  }

  //  UPDATE ID
  public static function updateId($modul = null, $no_id = null)
  {
    $table = 'tmp_id';
    $check = self::getData($table, ['modul' => $modul, 'tgl_id' => date('Y-m-d')]);
    if (@$check['no_id'] == '') {
      $result = self::insertData($table, ['modul' => $modul, 'tgl_id' => date('Y-m-d'), 'no_id' => $no_id]);
    } else {
      $result = self::updateData($table, ['tgl_id' => date('Y-m-d'), 'no_id' => $no_id], ['modul' => $modul, 'tgl_id' => date('Y-m-d')]);
    }
    return $result;
  }
}
