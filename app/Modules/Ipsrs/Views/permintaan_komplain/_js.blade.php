<script type="text/javascript">
    // --- Placeholder _loading function (UNTUK MENGATASI ReferenceError) ---
    if (typeof _loading === 'undefined') {
        window._loading = function(show, message) {
            console.warn(
                "'_loading' function is not defined globally. Using a minimal fallback. No visual spinner will appear."
                );
            console.log("Loading state: " + show + ", Message: " + message);
        };
    }
    // --- AKHIR Placeholder _loading ---


    var tabel = null;
    $(document).ready(function() {
        // --- INISIALISASI UNTUK HALAMAN INDEX (DATATABLES FILTER) ---
        $('.accordion-body .chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('.accordion-body')
        });

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
                    "data": "permintaan_id", // Untuk nomor urut
                    "sortable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "permintaan_id", // Untuk Aksi
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
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' +
                            uri_edit + '\', size: \'modal-lg\', position: \'normal\'})">' +
                            '             <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah Data' +
                            '         </a>' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick=_delete("' +
                            uri_delete + '")>' +
                            '             <i class="fas fa-trash text-danger me-2"></i> Hapus Data' +
                            '         </a>' +
                            '      </div>' +
                            '   </div>' +
                            '</div>';
                    }
                },
                {
                    "data": "permintaan_id",
                    "className": "text-left"
                },
                {
                    "data": "tgl_komplain",
                    "className": "text-left",
                    "render": function(data) {
                        return toDate(data);
                    }
                },
                {
                    "data": "asset_nm",
                    "className": "text-left",
                    "render": function(data, type, row, meta) {
                        return ifNull(data) + (ifNull(data) && ifNull(row.asset_no_seri) ?
                            ' (SN: ' + row.asset_no_seri + ')' : '');
                    }
                },
                {
                    "data": "asset_lokasi_nm",
                    "className": "text-left",
                    "render": function(data, type, row, meta) {
                        return ifNull(data);
                    }
                },
                {
                    "data": "pembuat_komplain_nm",
                    "className": "text-left",
                    "render": function(data) {
                        return ifNull(data);
                    }
                },
                {
                    "data": "deskripsi",
                    "className": "text-left",
                    "render": function(data) {
                        return ifNull(data);
                    }
                },
                {
                    "data": "status",
                    "className": "text-center",
                    "render": function(data) {
                        var badgeClass = '';
                        if (data === 'baru') badgeClass = 'bg-info';
                        else if (data === 'diproses') badgeClass = 'bg-warning';
                        else if (data === 'selesai') badgeClass = 'bg-success';
                        else if (data === 'ditolak' || data === 'dibatalkan') badgeClass =
                            'bg-danger';
                        return '<span class="badge ' + badgeClass + '">' + titleCase(data) +
                            '</span>';
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

        // Memaksa DataTables untuk reload setelah filter disubmit
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

    $(document).on('shown.bs.modal', '#my-modal-1', function(e) {
        var formModalId = $(this).attr('id');
        var modalContent = $('#' + formModalId + ' .modal-body');

        // Inisialisasi Select2 untuk dropdown di dalam modal
        modalContent.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#' + formModalId)
        });

        // Inisialisasi Datepicker
        modalContent.find('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1900,
            maxYear: parseInt(moment().format('YYYY'), 10) + 5,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });

        // Inisialisasi Autonumeric
        modalContent.find('.autonumeric').autoNumeric({
            aSep: ".",
            aDec: ",",
            vMax: "999999999999999",
            vMin: "0"
        });

        modalContent.find("#form").validate({
            rules: {
                permintaan_id: {
                    required: true
                },
                tgl_komplain: {
                    required: true
                },
                asset_id: {
                    required: true
                },
                pegawai_id: {
                    required: true
                },
                deskripsi: {
                    required: true
                },
                status: {
                    required: true
                },
                active_st: {
                    required: true
                },
            },
            messages: {
                permintaan_id: {
                    required: "ID Permintaan wajib diisi."
                },
                tgl_komplain: {
                    required: "Tanggal Komplain wajib diisi."
                },
                asset_id: {
                    required: "Aset wajib dipilih."
                },
                pegawai_id: {
                    required: "Pembuat Komplain wajib dipilih."
                },
                deskripsi: {
                    required: "Deskripsi Komplain wajib diisi."
                },
                status: {
                    required: "Status Komplain wajib dipilih."
                },
                active_st: {
                    required: "Status Aktif wajib dipilih."
                },
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

        // PENTING: tgl_komplain harus punya nilai default jika kosong
        var tglKomplainInput = modalContent.find('#tgl_komplain');
        if (tglKomplainInput.val() === '' || tglKomplainInput.val() === null) {
            tglKomplainInput.val(moment().format('DD-MM-YYYY')); // Set tanggal hari ini
        }
    });
</script>
