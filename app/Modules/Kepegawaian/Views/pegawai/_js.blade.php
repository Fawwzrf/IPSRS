<script type="text/javascript">
  var tabel = null;
  $(document).ready(function() {
    tabel = $('#datatable-main').DataTable({
      "language": {
        url: _base_url + 'dist/libs/DataTables/id.json',
      },
      "autoWidth": false,
      "processing": true,
      "responsive": true,
      "serverSide": true,
      "ordering": true,
      "order": [
        [0, 'asc']
      ],
      "ajax": {
        "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
        "type": "POST"
      },
      "createdRow": function(row, data, dataIndex) {
        if (data.active_st == 0) {
          $(row).addClass('bg-pink');
        }
      },
      "bFilter": false,
      "deferRender": true,
      "aLengthMenu": _datatableLengthMenu,
      "pageLength": 500,
      "columns": [{
          "data": "pegawai_id",
          "sortable": false,
          "render": function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          "data": "pegawai_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
            var uri_auth = '<?= $uri . '/form_auth_modal/' ?>' + data;
            var uri_permission = '<?= $uri . '/form_permission_modal/' ?>' + data;
            var uri_delete = '<?= $uri . '/delete/' ?>' + data;
            return '' +
              '<div class="btn-list btn-sm flex-nowrap">' +
              '  <div class="dropdown"> ' +
              '     <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
              '          Aksi' +
              '     </button>' +
              '     <div class="dropdown-menu">' +
              '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_edit + '\', size: \'modal-lg\', position: \'normal\'})">' +
              '          <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah' +
              '      </a>' +
              '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_auth + '\', size: \'modal-md\', position: \'normal\'})">' +
              '          <i class="fas fa-cog me-2"></i> Authentication' +
              '      </a>' +
              '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_permission + '\', size: \'modal-lg\', position: \'normal\'})">' +
              '          <i class="fas fa-cog me-2"></i> Permission' +
              '      </a>' +
              '   </div>' +
              ' </div>' +
              '</div>';
          }
        },
        {
          "data": "pegawai_id",
          "className": "text-left",
        },
        {
          "data": "pegawai_nm",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            if (row.sex_id == 'P') {
              return '<i>' + data + '</i>';
            } else {
              return data;
            }
          }
        },
        {
          "data": "sex_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            if (row.sex_id == 'P') {
              return '<i class="fas fa-venus text-pink me-2"></i>' + data;
            } else {
              return '<i class="fas fa-mars text-blue me-2"></i>' + data;
            }
          }
        },
        {
          "data": "divisi_nm",
          "className": "text-left",
        },
        {
          "data": "jabatan_nm",
          "className": "text-left",
        },
        {
          "data": "pegawai_tmt",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var result = '';
            if (data != '') {
              result = toDate(data);
            }
            return result;
          }
        },
        {
          "data": "pegawai_tat",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var result = '';
            if (data != '') {
              result = toDate(data);
            }
            return result;
          }
        },
        {
          "data": "pegawai_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var result = '';
            if (row.pegawai_tmt != '' && row.pegawai_tmt != null) {
              if (row.pegawai_tat != '' && row.pegawai_tat != null) {
                age = exactAge(row.pegawai_tmt, row.pegawai_tat);
              } else {
                age = exactAge(row.pegawai_tmt);
              }
              result = age['years'] + ' th ' + age['months'] + ' bl ' + age['days'] + ' hr ';
            }
            return result;
          }
        },
        {
          "data": "spkl_st",
          "className": "text-center",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;
            if (row['spkl_st'] == 1) {
              result = '<i class="fas fa-check-circle text-success "></i>';
            } else {
              result = '<i class="fas fa-times-circle text-danger"></i>';
            }
            return result;
          }
        },
        {
          "data": "active_st",
          "className": "text-center",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;
            if (row['active_st'] == 1) {
              result = '<i class="fas fa-check-circle text-success "></i>';
            } else {
              result = '<i class="fas fa-times-circle text-danger"></i>';
            }
            return result;
          }
        }
      ],
    });
    // tabel.draw();
  });
</script>