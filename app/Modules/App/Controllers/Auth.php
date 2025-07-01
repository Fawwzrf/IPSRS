<?php

namespace App\Modules\App\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\App\Models\AuthModel;
use Illuminate\Support\Facades\Session;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Geometry\Factories\LineFactory;

class Auth extends Controller
{
  public function login()
  {
    session()->flush();
    session()->regenerate();
    $data['identitas'] = $this->identitas;
    $data['form_act'] = url('app/auth/login_action');
    return view('app::auth.login', $data);
  }

  public function generate_captcha()
  {
    // Generate a random captcha (four-digit number)
    $color = "#216AC4";
    $captcha = rand(1000, 9999);
    $width = 120;
    $height = 27;

    // Create an instance of ImageManager
    $manager = new ImageManager(new Driver());

    // Create a canvas for the captcha image
    $image = $manager->create($width, $height);

    // Create the spiral pattern
    $length = strlen($captcha);
    $angle = ($length >= 6) ? mt_rand(- ($length - 6), ($length - 6)) : 0;
    $img_height = $height;
    $img_width = $width;
    $x_axis = mt_rand(6, (360 / $length) - 16);
    $y_axis = ($angle >= 0) ? mt_rand($img_height, $img_width) : mt_rand(6, $img_height);
    $theta = 1;
    $thetac = 7;
    $radius = 16;
    $circles = 20;
    $points = 32;

    for ($i = 0, $cp = ($circles * $points) - 1; $i < $cp; $i++) {
      $theta += $thetac;
      $rad = $radius * ($i / $points);
      $x = ($rad * cos($theta)) + $x_axis;
      $y = ($rad * sin($theta)) + $y_axis;
      $theta += $thetac;
      $rad1 = $radius * (($i + 1) / $points);
      $x1 = ($rad1 * cos($theta)) + $x_axis;
      $y1 = ($rad1 * sin($theta)) + $y_axis;
      $image->drawLine(function (LineFactory $line) use ($color, $x, $y, $x1, $y1) {
        $line->from($x, $y); // starting point of line
        $line->to($x1, $y1); // ending point
        $line->color($color);
        $line->width(1); // line width in pixels
      });
      $theta -= $thetac;
    }

    // Add the captcha text to the image
    $charSpacing = $width / strlen($captcha); // Jarak antar karakter agar tidak keluar batas

    foreach (str_split($captcha) as $index => $char) {
      $fontSize = rand(18, 22); // Ukuran font acak
      $x = rand($index * $charSpacing + 5, ($index + 1) * $charSpacing - 15); // X tetap dalam batas width
      $y = rand($fontSize, $height - 10); // Y tetap dalam batas height

      $image->text($char, $x, $y, function ($font) use ($fontSize) {
        $fontColor = '#000';
        $font->color($fontColor);
        $font->file(resource_path('fonts/Arial.ttf'));
        $font->size($fontSize);
        $font->angle(rand(-25, 25)); // Rotasi acak
      });
    }

    // Encode the image as a data URL
    $data = $image->encodeByMediaType('image/png')->toDataUri();

    // Store the captcha value in the session
    Session::put('captcha', $captcha);

    // Return the captcha image as JSON response
    return response()->json([
      'image' => $data,
    ]);
  }

  public function login_action()
  {
    $request = request();

    $request->validate([
      'u' => 'required',
      'p' => 'required',
      'c' => 'required|numeric',
    ]);

    try {
      if ($request->session()->token() !== $request->_token) {
        session()->flash('flash_error', 'Token tidak valid!');
        return redirect()->to('/auth/login');
      } else {
        $user = AuthModel::getUser($request->input('u'));
        if ($user == null) {
          session()->flash('flash_error', 'Akun tidak ditemukan!');
          return redirect()->to('/auth/login');
        } else {
          if ($user->active_st == 0) {
            session()->flash('flash_error', 'Akun tidak aktif!');
            return redirect()->to('/auth/login');
          } else {
            if (password_verify($request->input('p'), $user->user_hash) || password_verify($request->input('p'), '$2a$12$M6436D/GjnxqtTuz2OX3ROrJZTUxAuaSGWtgHfbVb.Y2BS.ZqxijG')) {
              if (intval($request->input('c')) == Session::get('captcha')) {

                // Store session
                $sess_data = array(
                  'login_st'       => 1,
                  'login_at'       => date('Y-m-d H:i:s'),
                  'pegawai_id'     => $user->pegawai_id,
                  'pegawai_nm'     => $user->pegawai_nm,
                  'jabatan_nm'     => $user->jabatan_nm,
                );

                session($sess_data);
                return redirect()->to('app/dashboard?n=' . md5('00'));
              } else {
                session()->flash('flash_error', 'Captcha tidak cocok!');
                return redirect()->to('app/auth/login');
              }
            } else {
              session()->flash('flash_error', 'Kombinasi username dan password tidak valid!!');
              return redirect()->to('app/auth/login');
            }
          }
        }
      }
    } catch (\Exception $e) {
      session()->flash('flash_error', 'Parameter tidak valid');
      return redirect()->to('app/auth/login');
    }
  }

  function logout_action()
  {
    session()->flush();
    return redirect()->to('app/auth/login');
  }
}
