<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title><?= $identitas['aplikasi_singkatan'] . ' - ' . $identitas['perusahaan_nm'] ?></title>
  <link rel="shortcut icon" href="<?= $identitas['logo'] ?>" type="image/x-icon">
  <!-- Toast -->
  <link href="<?= asset('dist/libs/jquery-toast/jquery-toast.min.css') ?>" rel="stylesheet" />
  <!-- Fontawesome -->
  <link href="<?= asset('dist/libs/fontawesome/css/all.css') ?>" rel="stylesheet" />
  <!-- CSS files -->
  <link href="<?= asset('dist/css/tabler.min.css') ?>" rel="stylesheet" />
  <link href="<?= asset('dist/css/itm.css?') . time() ?>" rel="stylesheet" />
  <style>
    @import url('https://rsms.me/inter/inter.css');

    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }

    body {
      font-feature-settings: "cv03", "cv04", "cv11";
    }

    .bg-img {
      background: url("<?= $identitas['background'] ?>") no-repeat center center fixed;
      -webkit-background-size: cover;
      -moz-background-size: cover;
      -o-background-size: cover;
      background-size: cover;
    }

    #captcha {
      width: 100% !important;
    }

    @media screen and (max-width: 820px) {
      #informasi {
        visibility: hidden;
        display: none;
      }

      #login {
        padding: 0px !important;
        margin-top: 1rem;
      }

      #auth {
        margin-top: 4rem;
        max-width: 400px;
      }

      #title {
        text-align: center;
      }
    }
  </style>
  <!-- Libs JS -->
  <!-- JQuery -->
  <script src="<?= asset('dist/libs/jquery/jquery.min.js') ?>"></script>
  <!-- JQuery Validation -->
  <script src="<?= asset('dist/libs/jquery-validation/dist/jquery.validate.min.js') ?>"></script>
  <script src="<?= asset('dist/libs/jquery-validation/dist/localization/messages_id.min.js') ?>"></script>
  <!-- Toast -->
  <script src="<?= asset('dist/libs/jquery-toast/jquery.toast.min.js') ?>"></script>
  <!-- Tabler Core -->
  <script src="<?= asset('dist/js/tabler.min.js') ?>" defer></script>
  <script src="<?= asset('dist/js/demo.min.js') ?>" defer></script>
</head>

<body class="d-flex flex-column">
  @if(session('flash_error'))
  <div class="flash-error" data-flasherror="<?= session('flash_error') ?>"></div>
  @endif
  <div class="page page-center bg-img">
    <div id="header_container" class="container mb-2 mt-4" style="max-width: 1028px;">
      <div class="row">
        <div class="col-lg-1 col-md-2 text-center">
          <a href="." class="navbar-brand navbar-brand-autodark"><img src="<?= $identitas['logo'] ?>" height="60" alt=""></a>
        </div>
        <div id="title" class="col">
          <h2 class="p-0 m-0"><?= $identitas['aplikasi_nm'] ?></h2>
          <h2 class="p-0 m-0"><?= $identitas['perusahaan_nm'] ?></h2>
        </div>
      </div>
    </div>
    <div id="auth" class="container" style="max-width: 400px;">
      <div class="card py-3">
        <div class="card-body">
          <div class="row">
            <div id="login" class="col-lg-12">
              <div class="text-center mb-3">
                <i class="fas fa-user-shield fa-4x text-primary"></i>
              </div>
              <h2 class="h2 text-center mb-2">Akses Pengguna</h2>
              <form id="form" action="<?= $form_act ?>" method="POST" autocomplete="off" novalidate>
                @csrf
                <div class="input-group mb-2 mt-4">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                  <input class="form-control" type="text" id="u" name="u" placeholder="Nama Pengguna" autocomplete="off" required>
                </div>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  <input class="form-control" type="password" id="p" name="p" placeholder="Kata Sandi" autocomplete="off" required>
                  <span class="input-group-text">
                    <a href="javascript:void(0)" onclick="showPassword()" id="show_password" class="link-secondary">
                      <i class="fas fa-eye"></i>
                    </a>
                  </span>
                </div>
                <div class="mt-2">
                  <div class="row">
                    <div class="col-7">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-th"></i></span>
                        <input class="form-control" type="number" id="c" name="c" placeholder="Captcha" autocomplete="off" required>
                        <span class="input-group-text">
                          <a href="#" onclick="getCaptcha()" class="link-secondary">
                            <i class="fas fa-sync"></i>
                          </a>
                        </span>
                      </div>
                    </div>
                    <div class="col-5" id="divCaptcha">
                      <img class="border border-primary" id="imgCaptcha" src="" alt="">
                    </div>
                  </div>
                </div>
                <div class="form-footer mt-2">
                  <button class="btn btn-primary btn-submit w-100" type="submit">Masuk</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    $(document).ready(function() {
      // Generate Captcha
      getCaptcha();

      $("#form").validate({
        rules: {

        },
        messages: {

        },
        errorElement: "em",
        errorPlacement: function(error, element) {
          error.addClass("invalid-feedback");
          if (element.prop("type") === "checkbox") {
            error.insertAfter(element.next("label"));
          } else if ($(element).hasClass('select2')) {
            error.insertAfter(element.next(".select2-container"));
          } else {
            error.insertAfter(element);
          }
        },
        highlight: function(element, errorClass, validClass) {
          $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function(element, errorClass, validClass) {
          $(element).addClass("is-valid").removeClass("is-invalid");
        },
        submitHandler: function(form) {
          $(".btn-submit").html('<i class="fas fa-spin fa-spinner"></i> Proses');
          $(".btn-submit").attr("disabled", "disabled");
          $(".btn-cancel").attr("disabled", "disabled");
          form.submit();
        }
      });

      const flashError = $(".flash-error").data("flasherror");
      if (flashError) {
        $.toast({
          heading: "Kesalahan",
          text: flashError,
          icon: "error",
          position: "top-right",
        });
      }
    })

    function showPassword() {
      var x = document.getElementById("p");
      if (x.type === "password") {
        $("#show_password").html('<i class="fas fa-eye-slash"></i>');
        x.type = "text";
      } else {
        $("#show_password").html('<i class="fas fa-eye"></i>');
        x.type = "password";
      }
    }

    function getCaptcha() {
      $.get("<?= url('app/auth/generate_captcha') ?>", {
        _is_ajax: true,
      }, function(res) {
        $('#imgCaptcha').attr('src', res.image);
      }, "json");
    }
    if ('serviceWorker' in navigator) {
      let _base_url = '';
      window.addEventListener('load', function() {
        navigator.serviceWorker.register(_base_url + 'erp-sw.js');
      });
    }
  </script>
</body>

</html>