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
                    "data": "order_kerja_id", // Untuk nomor urut
                    "sortable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "order_kerja_id", // Untuk Aksi
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
                { "data": "order_kerja_id", "className": "text-left" },
                { "data": "jenis", "className": "text-left", "render": function(data, type, row, meta){ return titleCase(ifNull(data)); } }, // PERBAIKAN: Tambah parameter row, meta
                { "data": "asset_nm", "className": "text-left", "render": function(data, type, row, meta){ return ifNull(data) + (ifNull(data) && ifNull(row.asset_no_seri) ? ' (SN: ' + row.asset_no_seri + ')' : ''); } }, // PERBAIKAN: Tambah parameter type, row, meta
                { "data": "asset_lokasi_nm", "className": "text-left", "render": function(data, type, row, meta){ return ifNull(data); } }, // PERBAIKAN: Tambah parameter type, row, meta
                {
                    "data": "status",
                    "className": "text-center",
                    "render": function(data, type, row, meta) { // PERBAIKAN: Tambah parameter type, row, meta
                        var badgeClass = '';
                        if (data === 'baru') badgeClass = 'bg-info';
                        else if (data === 'ditugaskan') badgeClass = 'bg-primary';
                        else if (data === 'diproses') badgeClass = 'bg-warning';
                        else if (data === 'menunggu_sparepart') badgeClass = 'bg-secondary';
                        else if (data === 'selesai') badgeClass = 'bg-success';
                        else if (data === 'ditolak' || data === 'dibatalkan') badgeClass = 'bg-danger';
                        return '<span class="badge ' + badgeClass + '">' + titleCase(data) + '</span>';
                    }
                },
                { "data": "prioritas", "className": "text-left", "render": function(data, type, row, meta){ return titleCase(ifNull(data)); } }, // PERBAIKAN: Tambah parameter type, row, meta
                { "data": "tgl_dibuat", "className": "text-left", "render": function(data, type, row, meta){ return toDate(data); } }, // PERBAIKAN: Tambah parameter type, row, meta
                { "data": "tgl_target_selesai", "className": "text-left", "render": function(data, type, row, meta){ return toDate(data); } }, // PERBAIKan: Tambah parameter type, row, meta
                { "data": "estimasi_biaya", "className": "text-right", "render": function(data, type, row, meta){ return numId(data, true); } }, // PERBAIKAN: Tambah parameter type, row, meta
                {
                    "data": "active_st",
                    "className": "text-center",
                    "render": function(data, type, row, meta) { // PERBAIKAN: Tambah parameter type, row, meta
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

        // Inisialisasi Autonumeric (jika ada input numerik di form modal)
        modalContent.find('.autonumeric').autoNumeric({
            aSep: ".",
            aDec: ",",
            vMax: "999999999999999",
            vMin: "0"
        });

        modalContent.find("#form").validate({
            rules: {
                order_kerja_id: { required: true },
                jadwal_pm_id: {
                    required: function(element) {
                        return modalContent.find('#permintaan_id').val() === '';
                    }
                },
                permintaan_id: { // Jika nanti ditambahkan
                    required: function(element) {
                        return modalContent.find('#jadwal_pm_id').val() === '';
                    }
                },
                prioritas: { required: true },
                status: { required: true },
                active_st: { required: true },
            },
            messages: {
                order_kerja_id: { required: "ID Order Kerja wajib diisi." },
                jadwal_pm_id: { required: "Pilih Jadwal PM atau Permintaan Komplain." },
                permintaan_id: { required: "Pilih Jadwal PM atau Permintaan Komplain." },
                prioritas: { required: "Prioritas wajib dipilih." },
                status: { required: "Status Order wajib dipilih." },
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

        // Logika untuk memastikan hanya satu sumber yang dipilih
        var jadwalPmSelect = modalContent.find('#jadwal_pm_id');
        var permintaanSelect = modalContent.find('#permintaan_id'); // Ini perlu di-handle jika permintaan_id benar-benar ada di form

        // Jika permintaan_id tidak ada di form, maka kita tidak perlu menargetkannya
        if (permintaanSelect.length === 0) { // Cek apakah elemen ada
            // Sederhanakan aturan required jika hanya jadwalPmSelect yang ada
            modalContent.find("#form").validate().settings.rules.jadwal_pm_id.required = true;
            delete modalContent.find("#form").validate().settings.rules.permintaan_id;
            // Atau, lebih baik tambahkan fallback untuk permintaanSelect
            permintaanSelect = $(); // Empty jQuery object if not found
        }


        function validateSourceSelection() {
            var isJadwalPmSelected = jadwalPmSelect.val() !== '';
            var isPermintaanSelected = permintaanSelect.length > 0 && permintaanSelect.val() !== ''; // Hanya cek jika elemen ada

            if (isJadwalPmSelected && isPermintaanSelected) {
                $.toast({ heading: "Peringatan", text: "Hanya boleh memilih Jadwal PM atau Permintaan Komplain, tidak keduanya!", icon: "warning", position: "top-right" });
                return false;
            } else if (!isJadwalPmSelected && !isPermintaanSelected) {
                $.toast({ heading: "Peringatan", text: "Jadwal PM atau Permintaan Komplain wajib dipilih!", icon: "warning", position: "top-right" });
                return false;
            }
            return true;
        }

        // Panggil validasi saat submit (jQuery Validate submitHandler seharusnya menangani ini)
        // atau saat salah satu dropdown berubah
        // jadwalPmSelect.on('change', validateSourceSelection);
        // permintaanSelect.on('change', validateSourceSelection); // Hanya jika permintaanSelect ada

        // Atur nilai default jenis berdasarkan nav_sess jika ada
        var defaultJenis = '<?= @$default_jenis ?>';
        if (defaultJenis === 'pemeliharaan') {
             // Secara default, Jadwal PM akan menjadi required jika ini adalah OK Pemeliharaan
             // Permintaan Komplain tidak required
             modalContent.find("#form").validate().settings.rules.jadwal_pm_id.required = true;
             // Jika permintaan_id ada, set required ke false
             if (permintaanSelect.length > 0 && modalContent.find("#form").validate().settings.rules.permintaan_id) {
                modalContent.find("#form").validate().settings.rules.permintaan_id.required = false;
             }
        } else if (defaultJenis === 'perbaikan') {
             // Jika ini Order Kerja Perbaikan
             // Permintaan Komplain akan menjadi required
             // Jadwal PM tidak required
             // if (permintaanSelect.length > 0) { modalContent.find("#form").validate().settings.rules.permintaan_id.required = true; }
             // modalContent.find("#form").validate().settings.rules.jadwal_pm_id.required = false;
        } else { // Jika tidak ada default jenis (misal form_modal dipanggil tanpa filter jenis)
             // Biarkan jQuery Validate default (salah satu harus required)
        }
        
        // PENTING: Untuk form_modal, tgl_dibuat harus punya nilai default jika kosong
        var tglDibuatInput = modalContent.find('#tgl_dibuat');
        if (tglDibuatInput.val() === '' || tglDibuatInput.val() === null) {
            tglDibuatInput.val(moment().format('DD-MM-YYYY')); // Set tanggal hari ini
        }
    });
</script>