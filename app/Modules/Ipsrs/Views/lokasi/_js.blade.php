<script type="text/javascript">
    // --- Placeholder _loading function (UNTUK MENGATASI ReferenceError) ---
    // Idealnya, fungsi ini sudah didefinisikan di itm.js atau itm.jquery.js Anda.
    // Jika error ini terus muncul, perlu investigasi mengapa itm.js/itm.jquery.js tidak termuat dengan benar.
    if (typeof _loading === 'undefined') {
        window._loading = function(show, message) {
            console.warn("'_loading' function is not defined globally. Using a minimal fallback. No visual spinner will appear.");
            console.log("Loading state: " + show + ", Message: " + message);
            // Anda bisa menambahkan logika visual loading spinner sederhana di sini jika diinginkan
            // Misalnya: var loadingSpinner = $('#my-simple-spinner-element');
            // if (show) loadingSpinner.show(); else loadingSpinner.hide();
        };
    }
    // --- AKHIR Placeholder _loading ---


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
                    "data": "lokasi_id",
                    "sortable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "lokasi_id",
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
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_edit + '\', size: \'modal-md\', position: \'normal\'})">' +
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
                { "data": "lokasi_id", "className": "text-left" },
                { "data": "parent_lokasi_id", "className": "text-left" },
                { "data": "lokasi_nm", "className": "text-left" },
                { "data": "tipe_lokasi", "className": "text-left" },
                { "data": "deskripsi", "className": "text-left", "render": function(data) { return ifNull(data); } },
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

        modalContent.find('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1900,
            maxYear: parseInt(moment().format('YYYY'), 10) + 5,
            locale: {
                format: 'DD-MM-YYYY' 
            }
        }); 

        modalContent.find("#form").validate({
            rules: {
                lokasi_nm: { required: true },
                tipe_lokasi: { required: true },
                active_st: { required: true },
            },
            messages: {
                lokasi_nm: { required: "Nama Lokasi wajib diisi." },
                tipe_lokasi: { required: "Tipe Lokasi wajib dipilih." },
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

        var tipeLokasiSelectElement = modalContent.find('#tipe_lokasi');
        var divParentLokasi = modalContent.find('#div_parent_lokasi');
        var parentLokasiSelectElement = modalContent.find('#parent_lokasi');

        tipeLokasiSelectElement.on('change', function() {
            var tipe = $(this).val();
            if (tipe === 'Gedung') {
                divParentLokasi.hide();
                parentLokasiSelectElement.val('').trigger('change');
            } else {
                divParentLokasi.show();
            }
            modalContent.find('input[name="lokasi_id"]').val('');
        }).trigger('change');

        if (modalContent.find('input[name="lokasi_id"]').val() != '') {
            modalContent.find('button[onclick="generateLokasiId()"]').hide();
        }

        window.generateLokasiId = function() {
            var tipe_lokasi = modalContent.find('#tipe_lokasi').val();
            var parent_lokasi_id = modalContent.find('#parent_lokasi').val();

            if (!tipe_lokasi) {
                $.toast({
                    heading: "Peringatan",
                    text: "Pilih Tipe Lokasi terlebih dahulu.",
                    icon: "warning",
                    position: "top-right",
                });
                return;
            }

            if (tipe_lokasi !== 'Gedung' && !parent_lokasi_id) {
                $.toast({
                    heading: "Peringatan",
                    text: "Untuk Lantai dan Ruangan, Lokasi Induk wajib dipilih.",
                    icon: "warning",
                    position: "top-right",
                });
                return;
            }

            _loading(true, 'Membuat ID Lokasi...');
            $.ajax({
                url: _base_url + 'ipsrs/lokasi/generate_id',
                type: 'POST',
                data: {
                    _token: _token,
                    tipe_lokasi: tipe_lokasi,
                    parent_lokasi_id: parent_lokasi_id
                },
                success: function(res) {
                    _loading(false);
                    if (res.status) {
                        modalContent.find('input[name="lokasi_id"]').val(res.data.new_id);
                        $.toast({
                            heading: "Berhasil",
                            text: "ID Lokasi berhasil dibuat.",
                            icon: "success",
                            position: "top-right",
                        });
                    } else {
                        $.toast({
                            heading: "Kesalahan",
                            text: res.message,
                            icon: "error",
                            position: "top-right",
                        });
                    }
                },
                error: function(xhr, status, error) {
                    _loading(false);
                    $.toast({
                        heading: "Error",
                        text: "Terjadi kesalahan saat generate ID.",
                        icon: "error",
                        position: "top-right",
                    });
                    console.error(xhr.responseText);
                }
            });
        };
    });
</script>