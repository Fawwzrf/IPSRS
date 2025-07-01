<script type="text/javascript">
  var tableMain = null;
  var tableVerifikasi = null;
  var tablePegawaiRekap = null;
  var storageName = '<?= $nav_id ?>';
  $(document).ready(function() {

    tabView();

    tableMain = $('#datatable-main').DataTable({
      "language": {
        url: _base_url + 'dist/libs/DataTables/id.json',
      },
      "autoWidth": false,
      "processing": true,
      "responsive": true,
      "serverSide": true,
      "ordering": true,
      "order": [
        [2, 'desc']
      ],
      "ajax": {
        "url": "<?= $uri . '/ajax_datatables/main?n=' . request('n') ?>",
        "type": "POST"
      },
      "bFilter": false,
      "deferRender": true,
      "aLengthMenu": _datatableLengthMenu,
      "pageLength": 500,
      "columns": [{
          "data": "lembur_id",
          "sortable": false,
          "render": function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          "data": "lembur_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
            var uri_delete = '<?= $uri . '/delete/' ?>' + data;

            var uri_detail = '<?= $uri . '/form_modal_detail/' ?>' + data;

            var sess_pegawai_id = '<?= session('pegawai_id') ?>';

            var result = '';

            if (row.pegawai_id == sess_pegawai_id) {
              result = '' +
                '<div class="btn-list btn-sm flex-nowrap">' +
                '  <div class="dropdown"> ' +
                '     <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
                '          Aksi' +
                '     </button>' +
                '     <div class="dropdown-menu">' +
                '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_detail + '\', title: \'' + row.lembur_id + '\', size: \'modal-xl\', position: \'normal\'})">' +
                '          <i class="fas fa-list text-success me-2"></i> Detail' +
                '      </a>' +
                '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_edit + '\', title: \'' + row.lembur_id + '\', size: \'modal-xl\', position: \'normal\'})">' +
                '          <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah' +
                '      </a>' +
                '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick=_delete("' + uri_delete + '")>' +
                '          <i class="fas fa-trash text-danger me-2"></i> Hapus' +
                '      </a>' +
                '   </div>' +
                ' </div>' +
                '</div>';
            } else {
              result = '' +
                '<div class="btn-list btn-sm flex-nowrap">' +
                '  <div class="dropdown"> ' +
                '     <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
                '          Aksi' +
                '     </button>' +
                '     <div class="dropdown-menu">' +
                '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_detail + '\', title: \'' + row.lembur_id + '\', size: \'modal-xl\', position: \'normal\'})">' +
                '          <i class="fas fa-list text-success me-2"></i> Detail' +
                '      </a>' +
                '   </div>' +
                ' </div>' +
                '</div>';
            }

            return result;
          }
        },
        {
          "data": "lembur_id",
          "className": "text-left",
        },
        {
          "data": "pegawai_nm",
          "className": "text-left",
        },
        {
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var date = moment(row.mulai_tgl);
            return dateDayname(date.day());
          }
        },
        {
          "data": "mulai_tgl",
          "className": "text-left",
        },
        {
          "data": "selesai_tgl",
          "className": "text-left",
        },
        {
          "data": "durasi",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            return data + " jam";
          }
        },
        {
          "data": "uraian",
          "className": "align-top text-left",
          "render": function(data, type, row, meta) {
            return '<pre class="text-black text-wrap m-0 p-0" style="background:inherit">' + data + '</pre>';
          }
        },
        {
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var result = "";

            var url_gambar_1 = '<?= $uri . '/form_modal_gambar/' ?>' + row.lembur_id + '/gambar_1';
            var url_gambar_2 = '<?= $uri . '/form_modal_gambar/' ?>' + row.lembur_id + '/gambar_2';
            var url_gambar_3 = '<?= $uri . '/form_modal_gambar/' ?>' + row.lembur_id + '/gambar_3';

            if (row.gambar_1 != "" && row.gambar_1 != null) {
              result += '<a href="javascript:void(0)" onclick="_modal(event, {uri: \'' + url_gambar_1 + '\', title: \'' + row.lembur_id + ' | ' + row.pegawai_nm + ' | ' + row.mulai_tgl + ' | ' + row.gambar_1 + '\', size: \'modal-xl\', position: \'normal\'})" class="btn btn-sm btn-default me-1"><i class="fas fa-image"></i></a>';
            }

            if (row.gambar_2 != "" && row.gambar_2 != null) {
              result += '<a href="javascript:void(0)" onclick="_modal(event, {uri: \'' + url_gambar_2 + '\', title: \'' + row.lembur_id + ' | ' + row.pegawai_nm + ' | ' + row.mulai_tgl + ' | ' + row.gambar_2 + '\', size: \'modal-xl\', position: \'normal\'})" class="btn btn-sm btn-default me-1"><i class="fas fa-image"></i></a>';
            }

            if (row.gambar_3 != "" && row.gambar_3 != null) {
              result += '<a href="javascript:void(0)" onclick="_modal(event, {uri: \'' + url_gambar_3 + '\', title: \'' + row.lembur_id + ' | ' + row.pegawai_nm + ' | ' + row.mulai_tgl + ' | ' + row.gambar_3 + '\', size: \'modal-xl\', position: \'normal\'})" class="btn btn-sm btn-default me-1"><i class="fas fa-image"></i></a>';
            }

            return result;
          }
        },
        {
          "data": "created_at",
          "className": "text-left",
        },
      ],
    });

    tableVerifikasi = $('#datatable-verifikasi').DataTable({
      "language": {
        url: _base_url + 'dist/libs/DataTables/id.json',
      },
      "autoWidth": false,
      "processing": true,
      "responsive": true,
      "serverSide": true,
      "ordering": true,
      "order": [
        [5, 'asc']
      ],
      "ajax": {
        "url": "<?= $uri . '/ajax_datatables/main?n=' . request('n') ?>",
        "type": "POST"
      },
      "bFilter": false,
      "deferRender": true,
      "aLengthMenu": _datatableLengthMenu,
      "pageLength": 500,
      "footerCallback": function(row, data, start, end, display) {
        let api = this.api();

        // Remove the formatting to get integer data for summation
        let intVal = function(i) {
          return typeof i === 'string' ?
            i.replace(/[\$,]/g, '') * 1 :
            typeof i === 'number' ?
            i :
            0;
        };

        // Total over all pages
        total_durasi = api
          .column(7)
          .data()
          .reduce((a, b) => intVal(a) + intVal(b), 0);

        api.column(7).footer().innerHTML = total_durasi.toFixed(2) + " jam";

        total_verifikasi_durasi = api
          .column(8)
          .data()
          .reduce((a, b) => intVal(a) + intVal(b), 0);

        api.column(8).footer().innerHTML = total_verifikasi_durasi.toFixed(2) + " jam";
      },
      "columns": [{
          "data": "lembur_id",
          "sortable": false,
          "render": function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          "data": "lembur_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var url_verifikasi = '<?= $uri . '/form_modal_verifikasi/' ?>' + data;
            return '<a class="btn btn-outline-info btn-sm" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + url_verifikasi + '\', size: \'modal-xl\', position: \'normal\'})"><i class="fas fa-check me-1"></i>Verifikasi</a>';
          }
        },
        {
          "data": "lembur_id",
          "className": "text-left",
        },
        {
          "data": "pegawai_nm",
          "className": "text-left",
        },
        {
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var date = moment(row.mulai_tgl);
            return dateDayname(date.day());
          }
        },
        {
          "data": "mulai_tgl",
          "className": "text-left",
        },
        {
          "data": "selesai_tgl",
          "className": "text-left",
        },
        {
          "data": "durasi",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            return data + " jam";
          }
        },
        {
          "data": "verifikasi_durasi",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            if (data != row.durasi) {
              return '<div class="text-warning"><b>' + data + " jam </b></div>";
            } else {
              return '<div class="text-success"><b>' + data + " jam </b></div>";
            }
          }
        },
        {
          "data": "uraian",
          "className": "align-top text-left",
          "render": function(data, type, row, meta) {
            return '<pre class="text-black text-wrap m-0 p-0" style="background:inherit">' + data + '</pre>';
          }
        },
        {
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var result = "";

            var url_gambar_1 = '<?= $uri . '/form_modal_gambar/' ?>' + row.lembur_id + '/gambar_1';
            var url_gambar_2 = '<?= $uri . '/form_modal_gambar/' ?>' + row.lembur_id + '/gambar_2';
            var url_gambar_3 = '<?= $uri . '/form_modal_gambar/' ?>' + row.lembur_id + '/gambar_3';

            if (row.gambar_1 != "" && row.gambar_1 != null) {
              result += '<a href="javascript:void(0)" onclick="_modal(event, {uri: \'' + url_gambar_1 + '\', title: \'' + row.lembur_id + ' | ' + row.pegawai_nm + ' | ' + row.mulai_tgl + ' | ' + row.gambar_1 + '\', size: \'modal-xl\', position: \'normal\'})" class="btn btn-sm btn-default me-1"><i class="fas fa-image"></i></a>';
            }

            if (row.gambar_2 != "" && row.gambar_2 != null) {
              result += '<a href="javascript:void(0)" onclick="_modal(event, {uri: \'' + url_gambar_2 + '\', title: \'' + row.lembur_id + ' | ' + row.pegawai_nm + ' | ' + row.mulai_tgl + ' | ' + row.gambar_2 + '\', size: \'modal-xl\', position: \'normal\'})" class="btn btn-sm btn-default me-1"><i class="fas fa-image"></i></a>';
            }

            if (row.gambar_3 != "" && row.gambar_3 != null) {
              result += '<a href="javascript:void(0)" onclick="_modal(event, {uri: \'' + url_gambar_3 + '\', title: \'' + row.lembur_id + ' | ' + row.pegawai_nm + ' | ' + row.mulai_tgl + ' | ' + row.gambar_3 + '\', size: \'modal-xl\', position: \'normal\'})" class="btn btn-sm btn-default me-1"><i class="fas fa-image"></i></a>';
            }

            return result;
          }
        },
        {
          "data": "created_at",
          "className": "text-left",
        },
      ],
    }, );

    tablePegawaiRekap = $('#datatable-pegawai-rekap').DataTable({
      "language": {
        url: _base_url + 'dist/libs/DataTables/id.json',
      },
      "autoWidth": false,
      "processing": true,
      "responsive": true,
      "serverSide": true,
      "ordering": true,
      "order": [
        [1, 'asc']
      ],
      "ajax": {
        "url": "<?= $uri . '/ajax_datatables/pegawai_rekap?n=' . request('n') ?>",
        "type": "POST"
      },
      "bFilter": false,
      "deferRender": true,
      "aLengthMenu": _datatableLengthMenu,
      "pageLength": 500,
      "columns": [{
          "sortable": false,
          "render": function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          "data": "pegawai_id",
          "className": "text-left py-1",
          "bSortable": false
        },
        {
          "data": "pegawai_nm",
          "className": "text-left py-1",
          "bSortable": false
        },
        {
          "data": "jabatan_nm",
          "className": "text-left py-1",
          "bSortable": false
        },
        {
          "data": "durasi",
          "className": "text-end py-1",
          "bSortable": false,
          "render": function(data, type, row, meta) {
            if (parseInt(data) == 0) {
              return '-';
            } else {
              if (data > 60) {
                return '<b class="text-pink">' + data + " jam</b>";
              } else {
                if (data > 30) {
                  return '<b class="text-warning">' + data + " jam</b>";
                } else {
                  return data + " jam";
                }
              }
            }
          }
        },
        {
          "data": "verifikasi_durasi",
          "className": "text-end py-1",
          "bSortable": false,
          "render": function(data, type, row, meta) {
            if (parseInt(data) == 0) {
              return '-';
            } else {
              return data + " jam";
            }
          }
        },
      ],
    });

    $('.nav-link').click(function() {
      var id = $(this).attr('id');
      var href = $(this).attr('href');

      localStorage.setItem(storageName, JSON.stringify({
        nav: '#' + id,
        href: href,
      }));
    })
  });

  function tabView() {
    var storageData = localStorage.getItem(storageName);
    if (storageData == null) {
      localStorage.setItem(storageName, JSON.stringify({
        nav: '#nav-data',
        tab: '#tabs-data'
      }));

      storageData = localStorage.getItem(storageName);
    }
    var data = JSON.parse(storageData);
    $('.nav-tabs a[href="' + data.href + '"]').tab('show');
  }
</script>