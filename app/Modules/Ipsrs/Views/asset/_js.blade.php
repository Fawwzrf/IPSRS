<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        // --- INISIALISASI UNTUK HALAMAN INDEX (DATATABLES FILTER) ---
        // Inisialisasi Select2 untuk dropdown filter
        $('.accordion-body .chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('.accordion-body')
        });

        // Inisialisasi Datepicker untuk filter tanggal
        $('.accordion-body .datepicker-notauto').daterangepicker({ 
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1900,
            maxYear: parseInt(moment().format('YYYY'), 10) + 5,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });

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
                [0, 'asc']
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
                    "data": "asset_id",
                    "sortable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "asset_id",
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
                { "data": "asset_id", "className": "text-left" },
                { "data": "asset_nm", "className": "text-left" },
                { "data": "jenis", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "no_seri", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "merk", "className": "text-left", "render": function(data){ return ifNull(data); } },
                {
                    "data": "kategori_asset_nm",
                    "className": "text-left",
                    "render": function(data) { return ifNull(data); }
                },
                {
                    "data": "lokasi_nm",
                    "className": "text-left",
                    "render": function(data) { return ifNull(data); }
                },
                {
                    "data": "pm_berikutnya",
                    "className": "text-left",
                    "render": function(data, type, row, meta) {
                        var result = '';
                        if (data != '' && data != null) {
                            result = toDate(data);
                        }
                        return result;
                    }
                },
                {
                    "data": "status",
                    "className": "text-center",
                    "render": function(data) {
                        var badgeClass = '';
                        if (data === 'aktif') badgeClass = 'bg-success';
                        else if (data === 'perbaikan') badgeClass = 'bg-warning';
                        else if (data === 'nonaktif' || data === 'dihapus') badgeClass = 'bg-danger';
                        return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                    }
                },
                {
                    "data": "active_st",
                    "className": "text-center",
                    "render": function(data, type, row, meta) {
                        if (row['active_st'] == 1) {
                            return '<i class="fas fa-check-circle text-success "></i>';
                        } else {
                            return '<i class="fas fa-times-circle text-danger"></i>';
                        }
                    }
                },
            ],
        });
        
        
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

    $(document).on('shown.bs.modal', '#my-modal-1', function (e) { 
        var formModalId = $(this).attr('id'); 
        var modalContent = $('#' + formModalId + ' .modal-body');

        modalContent.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#' + formModalId)
        });

        // Perbaikan di sini: Tambahkan penutup kurawal untuk objek 'locale'
        modalContent.find('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1900,
            maxYear: parseInt(moment().format('YYYY'), 10) + 5,
            locale: {
                format: 'DD-MM-YYYY'
            }
        }); // <-- Tanda kurung kurawal } di sini yang hilang

        modalContent.find("#form").validate({
            rules: {
                asset_nm: { required: true },
                status: { required: true },
                active_st: { required: true },
                
            },
            messages: {
                asset_nm: { required: "Nama Aset wajib diisi." },
                status: { required: "Status Aset wajib dipilih." },
                active_st: { required: "Status Aktif wajib dipilih." },
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                error.addClass("invalid-feedback");
                if (element.prop("type") === "radio") {
                    error.insertAfter(element.closest('.row').find('label.col-form-label').last());
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
                _save(event, form);
            }
        });
    });
</script>