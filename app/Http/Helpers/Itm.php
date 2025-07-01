<?php

use Illuminate\Support\Str;
// RESPONSE
if (!function_exists('_array')) {
  function _array($val)
  {
    return  json_decode(json_encode($val), true);
  }
}

if (!function_exists('_response')) {
  function _response($code = null, $uri = null, $data = null)
  {
    $response = array(
      // TRUE
      '00' => array(
        'status' => true,
        'message' => 'Data berhasil diproses.',
        'uri' => $uri,
        'data' => $data,
      ),
      '01' => array(
        'status' => true,
        'message' => 'Data berhasil disimpan.',
        'uri' => $uri,
        'data' => $data,
      ),
      '02' => array(
        'status' => true,
        'message' => 'Data berhasil diubah.',
        'uri' => $uri,
        'data' => $data,
      ),
      '03' => array(
        'status' => true,
        'message' => 'Data berhasil dihapus.',
        'uri' => $uri,
        'data' => $data,
      ),
      '04' => array(
        'status' => true,
        'message' => 'Tindak Lanjut berhasil dibatalkan.',
        'uri' => $uri,
        'data' => $data,
      ),
      // FALSE
      '10' => array(
        'status' => false,
        'message' => 'Data gagal diproses !',
        'uri' => $uri,
        'data' => $data,
      ),
      '11' => array(
        'status' => false,
        'message' => 'Data gagal disimpan !',
        'uri' => $uri,
        'data' => $data,
      ),
      '12' => array(
        'status' => false,
        'message' => 'Data gagal diubah !',
        'uri' => $uri,
        'data' => $data,
      ),
      '13' => array(
        'status' => false,
        'message' => 'Data gagal dihapus !',
        'uri' => $uri,
        'data' => $data,
      ),
      '14' => array(
        'status' => false,
        'message' => 'Tindak Lanjut gagal dibatalkan !',
        'uri' => $uri,
        'data' => $data,
      ),
      '15' => array(
        'status' => false,
        'message' => 'File gagal diunggah',
        'uri' => $uri,
        'data' => $data,
      ),
      '20' => array(
        'status' => false,
        'message' => 'Terjadi duplikasi kode !',
        'uri' => $uri,
        'data' => $data,
      ),
      '29' => array(
        'status' => false,
        'message' => @$data['message'],
        'uri' => $uri,
        'data' => $data,
      ),
    );
    return $response[$code];
  }
}

if (!function_exists('_post')) {
  function _post($key = null)
  {
    $request = request();
    if ($key == null) {
      $post = $request->post();
      unset($post['_token'], $post['_is_ajax']);
    } else {
      $post = $request->input($key);
    }

    return $post;
  };
}

if (!function_exists('list_tahun')) {
  function list_tahun($start = 2023, $end = null, $ord = 'asc')
  {
    if ($end == null) {
      $end = date("Y", strtotime(date("Y", strtotime(date('Y'))) . " + 5 year"));
    }
    $data = array();
    if ($ord == 'asc') {
      for ($i = $start; $i <= $end; $i++) {
        $data[$i] = $i;
      }
    }
    if ($ord == 'desc') {
      for ($i = $end; $i >= $start; $i--) {
        $data[$i] = $i;
      }
    }
    return $data;
  }
}

if (!function_exists('list_bulan')) {
  function list_bulan()
  {
    $data = array(
      '01' => 'Januari',
      '02' => 'Februari',
      '03' => 'Maret',
      '04' => 'April',
      '05' => 'Mei',
      '06' => 'Juni',
      '07' => 'Juli',
      '08' => 'Agustus',
      '09' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember',
    );
    return $data;
  }
}

if (!function_exists('get_bulan')) {
  function get_bulan($bln)
  {
    switch ($bln) {
      case 1:
        return "Januari";
        break;
      case 2:
        return "Februari";
        break;
      case 3:
        return "Maret";
        break;
      case 4:
        return "April";
        break;
      case 5:
        return "Mei";
        break;
      case 6:
        return "Juni";
        break;
      case 7:
        return "Juli";
        break;
      case 8:
        return "Agustus";
        break;
      case 9:
        return "September";
        break;
      case 10:
        return "Oktober";
        break;
      case 11:
        return "November";
        break;
      case 12:
        return "Desember";
        break;
    }
  }
}

if (!function_exists('get_bulan_singkat')) {
  function get_bulan_singkat($bln)
  {
    switch ($bln) {
      case 1:
        return "Jan";
        break;
      case 2:
        return "Feb";
        break;
      case 3:
        return "Mar";
        break;
      case 4:
        return "Apr";
        break;
      case 5:
        return "Mei";
        break;
      case 6:
        return "Jun";
        break;
      case 7:
        return "Jul";
        break;
      case 8:
        return "Agus";
        break;
      case 9:
        return "Sep";
        break;
      case 10:
        return "Okt";
        break;
      case 11:
        return "Nov";
        break;
      case 12:
        return "Des";
        break;
    }
  }
}

if (!function_exists('to_date')) {
  function to_date($date = null, $sp = null, $tp = null, $sp2 = null)
  {
    if ($date != '' && $date != null) {
      if ($tp == 'date') {
        $arr_date = explode(' ', $date);
        $date = $arr_date[0];
      } elseif ($tp == 'full_date') {
        $arr_date = explode(' ', $date);
        $date = $arr_date[0];
        $time = $arr_date[1];
      } elseif ($tp == 'time') {
        $arr_date = explode(' ', $date);
        $time = $arr_date[1];
      } elseif ($tp == 'hour_minute') {
        $arr_date = explode(' ', $date);
        $time = $arr_date[1];
        $arr_time = explode(':', $time);
        $hour = @$arr_time[0];
        $minute = @$arr_time[1];
      } elseif ($tp == 'only_day_month_name') {
        $arr_date = explode(' ', $date);
        $date = $arr_date[0];
      } elseif ($tp == 'only_year') {
        $arr_date = explode(' ', $date);
        $date = $arr_date[0];
      } elseif ($tp == 'only_day') {
        $arr_date = explode(' ', $date);
        $date = $arr_date[0];
      } elseif ($tp == 'date_hour_minute') {
        $arr_date = explode(' ', $date);
        $date = $arr_date[0];
        $time = $arr_date[1];
        $arr_time = explode(':', $time);
        $hour = @$arr_time[0];
        $minute = @$arr_time[1];
      }
      $arr = explode('-', $date);
      if (@$arr[2] == '') {
        $arr = explode('/', $date);
      }
      if ($sp != '') {
        $result = $arr[2] . $sp . $arr[1] . $sp . $arr[0];
      } else {
        $result = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
      }
      if ($tp == 'full_date') {
        if ($sp2 != '') {
          $result .= $sp2 . $time;
        } else {
          $result .= ' ' . $time;
        }
      }
      if ($tp == 'time') {
        $result = $time;
      }
      if ($tp == 'hour_minute') {
        $result = $hour . ':' . $minute;
      }
      if ($tp == 'only_year') {
        $result = $arr[0];
      }
      if ($tp == 'only_month') {
        $result = $arr[1];
      }
      if ($tp == 'only_month_name') {
        $result = get_bulan($arr[1]);
      }
      if ($tp == 'only_month_short_name') {
        $result = get_bulan_singkat($arr[1]);
      }
      if ($tp == 'only_day') {
        $result = $arr[2];
      }
      if ($tp == 'only_day_month_name') {
        if ($sp != null) {
          $result = $arr[2] . $sp . get_bulan($arr[1]);
        } else {
          $result = $arr[2] . '-' . $arr[1];
        }
      }
      if ($tp == 'date_hour_minute') {
        $result .= ' ' . $hour . ':' . $minute;
      }
    } else {
      $result = null;
    }

    if ($result == '00-00-0000' || $result == '0000-00-00' || $result == '00-00-0000 00:00:00' || $result == '0000-00-00 00:00:00') {
      $result = null;
    }

    return $result;
  }
}

if (!function_exists('str_replace_between')) {
  function str_replace_between($str, $needle_start, $needle_end, $replacement)
  {
    $pos = strpos($str, $needle_start);
    $start = $pos === false ? 0 : $pos + strlen($needle_start);

    $pos = strpos($str, $needle_end, $start);
    $end = $pos === false ? strlen($str) : $pos;

    return substr_replace($str, $replacement, $start, $end - $start);
  }
}

/**
 * FORM
 */
if (!function_exists('_frm_select')) {
  function _frm_select($field = null, $data = null, $val_key = null, $val_str = null, $val_selected = null, $val_empty = null, $attribute = null)
  {
    $html = '<select id="' . $field . '" name="' . $field . '" ' . $attribute . '>';
    $option = '';
    if ($val_empty != '') {
      $option .= '<option value="">' . $val_empty . '</option>';
    }
    foreach ($data as $r) {
      $_key = $r[$val_key];
      $_str = "";
      $_arr_str = explode(' - ', $val_str);
      if (count($_arr_str) > 0) {
        foreach ($_arr_str as $kstr => $vstr) {
          if ($kstr > 0) $_str .= " - ";
          $_str .= $r[$vstr];
        }
      } else {
        $_str = $r[$val_str];
      }
      $option .= '<option value="' . $_key . '" ' . (($_key == $val_selected) ? 'selected' : '') . '>' . $_str . '</option>';
    }
    $html .= $option;
    $html .= '</select>';
    return $html;
  }
}

if (!function_exists('folder_exist')) {
  function folder_exist($folder)
  {
    // Get canonicalized absolute pathname
    $path = realpath($folder);

    // If it exist, check if it's a directory
    if ($path !== false and is_dir($path)) {
      // Return canonicalized absolute pathname
      return $path;
    }

    // Path/folder does not exist
    return false;
  }
}

if (!function_exists('upload_base64')) {
  function upload_base64($field)
  {
    if (isset($_FILES[$field])) {
      if ($_FILES[$field]['name'] != "") {
        $tmp = $_FILES[$field]['tmp_name'];
        $type = $_FILES[$field]['type'];
        $data = file_get_contents($tmp);
        $base64 = 'data:' . $type . ';base64,' . base64_encode($data);
        return $base64;
      }
    } else {
      return null;
    }
  }
}

if (!function_exists('upload_file')) {
  function upload_file($folder, $field, $config = [])
  {
    $arr_folder = explode('/', $folder);
    $upload_path = public_path(env('UPLOAD_ROOT_FOLDER')) . "/";

    if (count($arr_folder) > 0) {
      for ($i = 0; $i < count($arr_folder); $i++) {
        $upload_path .= $arr_folder[$i] . '/';
        if (!folder_exist($upload_path)) {
          mkdir($upload_path);
        }
      }
    } else {
      $upload_path .= $folder . '/';
      if (!folder_exist($upload_path)) {
        mkdir($upload_path);
      }
    }

    if (request()->hasFile($field)) {
      // dd(request()->file($field));
      $file = request()->file($field); // Ambil file dari request
      $extension = $file->getClientOriginalExtension(); // Ambil ekstensi file

      // Generate nama file unik (gunakan UUID)
      $encryptedFilename = Str::uuid() . '.' . $extension;

      $destinationPath = $upload_path; // Path tujuan

      // Pindahkan file ke public/assets dengan nama terenkripsi
      $moved = $file->move($destinationPath, $encryptedFilename);

      if ($moved) {
        return [
          'status' => true,
          'data' => $encryptedFilename
        ];
      } else {
        return [
          'status' => false,
          'error' => 'File not moved'
        ];
      }
    } else {
      return [
        'status' => false,
        'error' => 'File not found'
      ];
    }
  }
}
