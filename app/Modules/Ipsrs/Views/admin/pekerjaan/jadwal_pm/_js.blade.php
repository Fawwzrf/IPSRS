<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        // --- Inisialisasi plugin untuk form filter ---
        $('.accordion-body .chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('.accordion-body')
        });

        // --- Inisialisasi Datatables ---
        tabel = $('#datatable-main').DataTable({
            "language": {
                url: _base_url + 'dist/libs/DataTables/id.json',
            },
            "stateSave": true,
            "autoWidth": false,
            "processing": true,
            "responsive": true,
            "serverSide": true,
            "ordering": true,
            "order": [
                [0, 'asc'] // Default sort berdasarkan Nama Aset
            ],
            "ajax": {
                "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
                "type": "POST",
                "data": function(d) {
                    var searchData = <?= json_encode(@$nav_sess['search']['data']) ?: '{}' ?>;
                    d.search_data = searchData;
                    d._token = _token;
                }
            },
            "deferRender": true,
            "aLengthMenu": _datatableLengthMenu,
            "pageLength": 10,
            "createdRow": function(row, data, dataIndex) {
                if (data.active_st == 0) {
                    $(row).addClass('bg-pink');
                }
            },
            "bFilter": false,
            "columns": [{
                    "data": "jadwal_pm_id",
                    "sortable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "jadwal_pm_id",
                    "className": "text-left",
                    "render": function(data, type, row, meta) {
                        var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
                        var uri_delete = '<?= $uri . '/delete/' ?>' + data;
                        
                        return '' +
                            '<div class="btn-list btn-sm flex-nowrap">' +
                            '   <div class="dropdown"> ' +
                            '      <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
                            '          Aksi' +
                            '      </button>' +
                            '      <div class="dropdown-menu">' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_edit + '\', size: \'modal-lg\', position: \'normal\'})">' +
                            '             <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah Data' +
                            '         </a>' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick=_delete("' + uri_delete + '")>' +
                            '             <i class="fas fa-trash text-danger me-2"></i> Hapus Data' +
                            '         </a>' +
                            '      </div>' +
                            '   </div>' +
                            '</div>';
                    }
                },
                { "data": "asset_nm", "className": "text-left" },
                { "data": "frekuensi", "className": "text-left" },
                { "data": "jenis", "className": "text-left" },
                { "data": "tgl_terakhir", "render": function(data) { return data ? toDate(data) : '-'; }},
                { "data": "tgl_berikutnya", "render": function(data) { return data ? toDate(data) : '-'; }},
                { "data": "status", "className": "text-center", "render": function(data) {
                    var badgeClass = { 'aktif': 'bg-success', 'ditunda': 'bg-warning', 'selesai': 'bg-secondary' };
                    return '<span class="badge ' + (badgeClass[data] || 'bg-secondary') + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                }},
                { "data": "active_st", "className": "text-center", "render": function(data) {
                    return data == 1 ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>';
                }}
            ],
        });
        
        // --- Event handler untuk filter ---
        $('#search').on('submit', function(e) {
            e.preventDefault();
            tabel.ajax.reload();
        });

        window._searchReset = function() {
            $('#search')[0].reset();
            $('.chosen-select').val('').trigger('change');
            tabel.ajax.reload();
        };
    });

    // --- Inisialisasi plugin di dalam modal ---
    $(document).on('shown.bs.modal', '#my-modal-1', function (e) {
        var modalContent = $(this).find('.modal-body');
        modalContent.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $(this)
        });
        modalContent.find('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: { format: 'DD-MM-YYYY' }
        });
    });
</script>