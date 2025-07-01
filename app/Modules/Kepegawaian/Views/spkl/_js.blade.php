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
        [2, 'desc']
      ],
      "ajax": {
        "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
        "type": "POST"
      },
      "deferRender": true,
      "aLengthMenu": _datatableLengthMenu,
      "pageLength": 50,
      "columns": [{
          "data": "spkl_id",
          "sortable": false,
          "render": function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          "data": "spkl_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
            var uri_cetak = '<?= $uri . '/cetak/' ?>' + data + '<?= '?n=' . request('n') ?>';
            var uri_delete = '<?= $uri . '/delete/' ?>' + data;
            return '' +
              '<div class="btn-list btn-sm flex-nowrap">' +
              '  <div class="dropdown"> ' +
              '     <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
              '          Aksi' +
              '     </button>' +
              '     <div class="dropdown-menu">' +
              '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_edit + '\', size: \'modal-xl\', position: \'normal\'})">' +
              '          <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah' +
              '      </a>' +
              '      <a class="dropdown-item p-1" target="_blank" href="' + uri_cetak + '">' +
              '          <i class="fas fa-print text-default me-2"></i> Cetak' +
              '      </a>' +
              '      <a class="dropdown-item p-1" href="javascript:void(0)" onclick=_delete("' + uri_delete + '")>' +
              '          <i class="fas fa-trash text-danger me-2"></i> Hapus' +
              '      </a>' +
              '   </div>' +
              ' </div>' +
              '</div>';
          }
        },
        {
          "data": "spkl_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "pembuat_nm",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "mulai_tgl",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "selesai_tgl",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "mulai_jam",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "selesai_jam",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "durasi",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "pekerjaan",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
        {
          "data": "pegawai_arr_nm",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var data = ifNull(data);
            var result = data;

            return result;
          }
        },
      ],
    });
    // tabel.draw();
  });
</script>