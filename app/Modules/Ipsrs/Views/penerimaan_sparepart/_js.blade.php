<script type="text/javascript">
    // --- Placeholder _loading function (UNTUK MENGATASI ReferenceError) ---
    if (typeof _loading === 'undefined') {
        window._loading = function(show, message) {
            console.warn("'_loading' function is not defined globally. Using a minimal fallback. No visual spinner will appear.");
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
                    "data": "penerimaan_id",
                    "sortable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "penerimaan_id",
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
                { "data": "penerimaan_id", "className": "text-left" },
                { "data": "tgl", "className": "text-left", "render": function(data){ return toDate(data); } },
                { "data": "sparepart_nm", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "vendor", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "no_faktur", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "jumlah", "className": "text-center", "render": function(data){ return ifNull(data); } },
                { "data": "harga_satuan", "className": "text-right", "render": function(data){ return numId(data, true); } },
                { "data": null, "className": "text-right", "render": function(data){ return numId(data.jumlah * data.harga_satuan, true); } }, // Total Harga
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

    $(document).on('shown.bs.modal', '#my-modal-1', function (e) { 
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

        // Inisialisasi Autonumeric untuk input harga
        modalContent.find('.autonumeric').autoNumeric({
            aSep: ".",
            aDec: ",",
            vMax: "999999999999999",
            vMin: "0"
        });

        modalContent.find("#form").validate({
            rules: {
                penerimaan_id: { required: true },
                tgl: { required: true },
                sparepart_id: { required: true },
                jumlah: { required: true, min: 1 },
                active_st: { required: true },
            },
            messages: {
                penerimaan_id: { required: "ID Penerimaan wajib diisi." },
                tgl: { required: "Tanggal Penerimaan wajib diisi." },
                sparepart_id: { required: "Sparepart wajib dipilih." },
                jumlah: { required: "Jumlah wajib diisi.", min: "Jumlah minimal 1." },
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

        // --- FITUR BARU: Default placeholder harga dari sparepart yang dipilih ---
        var sparepartSelect = modalContent.find('#sparepart_id');
        var hargaSatuanInput = modalContent.find('#harga');

        sparepartSelect.on('change', function() {
            var selectedOption = sparepartSelect.find(':selected');
            var price = selectedOption.data('price'); // Ambil harga dari data-price attribute

            if (typeof price !== 'undefined') {
                // Gunakan autoNumeric('set') untuk mengisi input yang sudah diinisialisasi autonumeric
                hargaSatuanInput.autoNumeric('set', price);
            } else {
                hargaSatuanInput.autoNumeric('set', 0); // Jika tidak ada harga, set ke 0
            }
        }).trigger('change'); // Trigger 'change' saat modal dibuka untuk mengisi harga awal jika sudah terpilih

        // Pastikan autonumeric terinisialisasi sebelum mencoba autoNumeric('set')
        hargaSatuanInput.autoNumeric({
            aSep: ".",
            aDec: ",",
            vMax: "999999999999999",
            vMin: "0"
        });
    });
</script>