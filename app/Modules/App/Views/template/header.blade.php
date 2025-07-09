<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title><?= $identitas['aplikasi_singkatan'] . ' - ' . $identitas['perusahaan_nm'] ?></title>
  <link rel="shortcut icon" href="<?= $identitas['logo'] ?>" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="57x57" href="<?= asset('assets/manifest_asset/ios/57.png') ?>">
  <link rel="apple-touch-icon" sizes="60x60" href="<?= asset('assets/manifest_asset/ios/60.png') ?>">
  <link rel="apple-touch-icon" sizes="72x72" href="<?= asset('assets/manifest_asset/ios/72.png') ?>">
  <link rel="apple-touch-icon" sizes="76x76" href="<?= asset('assets/manifest_asset/ios/76.png') ?>">
  <link rel="apple-touch-icon" sizes="114x114" href="<?= asset('assets/manifest_asset/ios/114.png') ?>">
  <link rel="apple-touch-icon" sizes="120x120" href="<?= asset('assets/manifest_asset/ios/120.png') ?>">
  <link rel="apple-touch-icon" sizes="144x144" href="<?= asset('assets/manifest_asset/ios/144.png') ?>">
  <link rel="apple-touch-icon" sizes="152x152" href="<?= asset('assets/manifest_asset/ios/152.png') ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('assets/manifest_asset/ios/180.png') ?>">
  <link rel="icon" type="image/png" sizes="512x512" href="<?= asset('assets/manifest_asset/android/android-launchericon-512-512.png') ?>">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= asset('assets/manifest_asset/android/android-launchericon-192-192.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css" />
  <!-- Toast -->
  <link href="<?= asset('dist/libs/jquery-toast/jquery-toast.min.css') ?>" rel="stylesheet" />
  <!-- Fontawesome -->
  <link href="<?= asset('dist/libs/fontawesome/css/all.css') ?>" rel="stylesheet" />
  <!-- SweetAlert -->
  <link href="<?= asset('dist/libs/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" />
  <!-- Datatables -->
  <link href="<?= asset('dist/libs/DataTables/datatables.min.css') ?>" rel="stylesheet" />
  <link href="<?= asset('dist/libs/DataTables/DataTables-1.13.4/css/dataTables.bootstrap5.min.css') ?>" rel="stylesheet" />
  <!-- GridTable -->
  <link href="<?= asset('dist/libs/grid-table/gridTable.css?v=1.0.1') ?>" rel="stylesheet" />
  <!-- CSS files -->
  <link href="<?= asset('dist/css/tabler.css?1684106062') ?>" rel="stylesheet" />
  <link href="<?= asset('dist/css/tabler-flags.min.css?1684106062') ?>" rel="stylesheet" />
  <link href="<?= asset('dist/css/tabler-payments.min.css?1684106062') ?>" rel="stylesheet" />
  <link href="<?= asset('dist/css/demo.min.css?1684106062') ?>" rel="stylesheet" />
  <link href="<?= asset('dist/css/itm.css?v= time()') ?>?>" rel="stylesheet" />
  <!-- Select2 -->
  <link href="<?= asset('dist/libs/select2/css/select2.min.css') ?>" rel="stylesheet" />
  <link href="<?= asset('dist/libs/select2/css/select2-bootstrap-5-theme.min.css') ?>" rel="stylesheet" />
  <!-- Daterangepicker -->
  <link href="<?= asset('dist/libs/daterangepicker/css/daterangepicker.css') ?>" rel="stylesheet" />
  <!-- Codemirror -->
  <link href="<?= asset('dist/libs/codemirror/lib/codemirror.css') ?>" rel="stylesheet" />
  <!-- Chat -->
  <link href="<?= asset('dist/css/chat.css') ?>" rel="stylesheet" />
  <!-- Chart.js -->
  <link href="<?= asset('dist/libs/Chart.js/Chart.min.css') ?>" rel="stylesheet" />
  <!-- Json Viewer -->
  <link href="<?= asset('dist/libs/json-viewer/jquery.json-viewer.css') ?>" rel="stylesheet" />
  <!-- Fullcalendar -->
  <link href="<?= asset('dist/libs/fullcalendar/main.css') ?>" rel="stylesheet" />
  <!-- Calendar -->
  <link href="<?= asset('dist/libs/calendar/calendar.css') ?>" rel="stylesheet" />
  
  <style>
    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }

    body {
      font-feature-settings: "cv03", "cv04", "cv11";
    }

    .form-disabled {
      pointer-events: none !important;
    }
  </style>
  <style>
    #form_dokumen {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
    }

    #form_dokumen input {
      border: 0;
    }

    #form_dokumen input::placeholder {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
    }

    #form_dokumen input {
      font-family: courier, courier new, serif;
    }

    #form_dokumen textarea {
      border: 0;
    }

    #form_dokumen textarea {
      font-family: courier, courier new, serif;
    }

    #form_dokumen textarea::placeholder {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
    }

    .ui-autocomplete {
      position: absolute;
      top: 0;
      left: 0;
      cursor: default;
    }

    .table-diagonal td {
      position: relative;
      overflow: hidden;
    }

    .line {
      position: absolute;
      height: 45px;
      top: 40px;
      bottom: 0;
      margin: auto;
      width: 100%;
      border-top: 1px solid #ccc;
      -webkit-transform: rotate(-20deg);
      -ms-transform: rotate(-20deg);
      transform: rotate(-20deg);
    }

    .diagonal {
      width: 150px;
      height: 40px;
    }

    .diagonal span.lb {
      position: absolute;
      top: 2px;
      left: 2px;
    }

    .diagonal span.rt {
      position: absolute;
      bottom: 2px;
      right: 2px;
    }

    div:where(.swal2-container) {
      z-index: 1160 !important;
    }
  </style>
  <!-- Libs JS -->
  <script src="<?= asset('dist/libs/jquery/jquery.min.js') ?>"></script>
  <!-- JQuery Validation -->
  <script src="<?= asset('dist/libs/jquery-validation/dist/jquery.validate.js') ?>"></script>
  <script src="<?= asset('dist/libs/jquery-validation/dist/localization/messages_id.min.js') ?>"></script>
  <!-- SweetAlert -->
  <script src="<?= asset('dist/libs/sweetalert2/sweetalert2.all.min.js') ?>"></script>
  <!-- Datatables-->
  <script src="<?= asset('dist/libs/DataTables/datatables.min.js') ?>"></script>
  <!-- Toast -->
  <script src="<?= asset('dist/libs/jquery-toast/jquery.toast.min.js') ?>"></script>
  <!-- Tabler Core -->
  <script src="<?= asset('dist/js/tabler.min.js?1684106062') ?>"></script>
  <script src="<?= asset('dist/js/demo.min.js?1684106062') ?>"></script>
  <!-- Select2 -->
  <script src="<?= asset('dist/libs/select2/js/select2.full.min.js') ?>"></script>
  <!-- Chained -->
  <script src="<?= asset('dist/libs/jquery_chained/jquery.chained.min.js') ?>"></script>
  <script src="<?= asset('dist/libs/jquery_chained/jquery.chained.remote.js') ?>"></script>
  <!-- Daterangepicker -->
  <script src="<?= asset('dist/libs/momentjs/js/moment.min.js') ?>"></script>
  <script src="<?= asset('dist/libs/daterangepicker/js/daterangepicker.min.js') ?>"></script>
  <!-- Autonumeric -->
  <script src="<?= asset('dist/libs/autonumeric/autonumeric.js') ?>"></script>
  <!-- GridTable -->
  <script src="<?= asset('dist/libs/grid-table/gridTable.js?v=1.1.0') ?>"></script>
  <!-- TinyMCE -->
  <script src="<?= asset('/dist/libs/tinymce/tinymce.min.js?1684106062') ?>" defer></script>
  <!-- Autocomplete -->
  <script src="<?= asset('dist/js/autocomplete.js') ?>"></script>
  <!-- Chart.js -->
  <script src="<?= asset('dist/libs/Chart.js/Chart.min.js') ?>"></script>
  <script src="<?= asset('dist/libs/Chart.js/chartjs-plugin-labels.js') ?>"></script>
  <!-- Codemirror -->
  <script src="<?= asset('dist/libs/codemirror/lib/codemirror.js') ?>"></script>
  <script src="<?= asset('dist/libs/codemirror/addon/edit/matchbrackets.js') ?>"></script>
  <script src="<?= asset('dist/libs/codemirror/addon/comment/continuecomment.js') ?>"></script>
  <script src="<?= asset('dist/libs/codemirror/addon/comment/comment.js') ?>"></script>
  <script src="<?= asset('dist/libs/codemirror/mode/javascript/javascript.js') ?>"></script>
  <script src="<?= asset('dist/libs/codemirror/mode/sql/sql.js') ?>"></script>
  <script src="<?= asset('dist/js/inputmask.min.js') ?>"></script>
  <script src="<?= asset('dist/js/chartjs.min.js') ?>"></script>
  <script src="<?= asset('dist/js/chartjs-datalabels.min.js') ?>"></script>
  <!-- Chart -->
  <script src="<?= asset('dist/libs/apexcharts/dist/apexcharts.min.js?1684106062') ?>" defer></script>
  <!-- Json Viewer -->
  <script src="<?= asset('dist/libs/json-viewer/jquery.json-viewer.js') ?>"></script>
  <!-- hotkeys -->
  <script src="<?= asset('dist/js/hotkeys.min.js') ?>"></script>
  <!-- Fullcalendar -->
  <script src="<?= asset('dist/libs/fullcalendar/main.min.js') ?>"></script>

  

  <!-- Custom -->
  <script type="text/javascript">
    const _token = '<?= csrf_token() ?>';
    const _asset = '';
    const _base_url = '<?= url('/') ?>/';
    const _this_uri = '';
    const _n = '<?= request('n') ?>'
    const _datatableLengthMenu = [
      [10, 50, 100, 500],
      [10, 50, 100, 500]
    ];
    $(window).bind("popstate", function() {
      window.location = location.href
    });

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var jqxhrAjax = {
      abort: function() {}
    };
  </script>

  <script src="<?= asset('dist/js/itm.js?v=time()') ?>"></script>
  <script src="<?= asset('dist/js/itm.jquery.js?v=time()') ?>"></script>
  <script src="<?= asset('dist/js/itm.load.js?v=time()') ?>"></script>
  <script src="<?= asset('dist/js/itm.canvas.js?v=time()') ?>"></script>
  <!-- RowGroup -->
  <script src="<?= asset('dist/js/dataTables.rowGroup.js') ?>"></script>

</head>

<body class="layout-fluid">
  @if(session('flash_success'))
  <div class="flash-success" data-flashsuccess="<?= session('flash_success') ?>"></div>
  @endif
  @if(session('flash_error'))
  <div class="flash-error" data-flasherror="<?= session('flash_error') ?>"></div>
  @endif
  <div class="page">