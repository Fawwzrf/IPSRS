<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
  <div class="card-body">
    <div class="mb-1 row">
      <label class="col-lg-3 col-md-6 col-form-label required">Pegawai Id</label>
      <div class="col-lg-4">
        <input type="text" name="pegawai_id" class="form-control" value="<?= @$main['pegawai_id'] ?>" <?= @$main ? 'required' : '' ?> readonly>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-3 col-md-6 col-form-label required">Nama Lengkap</label>
      <div class="col-lg-8 col-md-6">
        <input type="text" name="pegawai_nm" class="form-control" value="<?= @$main['pegawai_nm'] ?>" readonly>
      </div>
    </div>
    <div class="border-dotted my-2"></div>
    <table class="table table-vcenter card-table table-striped table-sm" id="datatable-permission">
      <thead>
        <tr>
          <th width="5%">Acc</th>
          <th width="5%">All</th>
          <th width="5%">No</th>
          <th width="7%">Nav ID</th>
          <th>Navigation</th>
          <th width="5%">Aktif?</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <div class="border-dotted my-2"></div>
    <div class="row mt-2">
      <div class="col-12">
        <div class="float-end">
          <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
          <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Batal</button>
        </div>
      </div>
    </div>
  </div>
</form>
<script type="text/javascript">
  var tablePermission = null;
  $(document).ready(function() {
    tablePermission = $('#datatable-permission').DataTable({
      "language": {
        url: _base_url + 'dist/libs/DataTables/id.json',
      },
      "autoWidth": false,
      "processing": true,
      "responsive": true,
      "serverSide": true,
      "ordering": true,
      "pageLength": 500,
      "bFilter": false,
      "lengthMenu": [
        [100, 500, 1000, 1500, 2000],
        [100, 500, 1000, 1500, 2000]
      ],
      "order": [
        [0, 'asc']
      ],
      "ajax": {
        "url": "<?= $uri . '/ajax_datatables_permissions/' . @$main['pegawai_id'] . '?n=' . request('n') ?>",
        "type": "POST"
      },
      "deferRender": true,
      "aLengthMenu": _datatableLengthMenu,
      "columns": [{
          "data": "nav_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var checked = '';
            if (row.checked_nav != null) {
              checked = 'checked';
            }

            return '' +
              '<input class="form-check-input m-0 align-middle" type="checkbox" name="nav_id[]" value="' + data + '" ' + checked + '>';
          }
        },
        {
          "data": "nav_id",
          "className": "text-left",
          "render": function(data, type, row, meta) {
            var checked = '';
            if (row.checked_all != '0' && row.checked_all != null) {
              checked = 'checked';
            }

            return '' +
              '<input class="form-check-input m-0 align-middle" type="checkbox" name="all_data_st[]" value="' + data + '" ' + checked + '>';
          }
        },
        {
          "data": "nav_id",
          "sortable": false,
          "render": function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          "data": "nav_id",
          "className": "text-left",
        },
        {
          "data": "nav_nm",
          "className": "text-left",
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
        },
      ],
    });
  });

  function check_all() {
    $(".form-check-input").attr('checked', true);
  }

  function uncheck_all() {
    $(".form-check-input").attr('checked', false);
  }
</script>