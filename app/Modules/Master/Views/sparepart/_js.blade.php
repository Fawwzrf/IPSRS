<script type="text/javascript">
    // --- Placeholder _loading function (untuk mengatasi ReferenceError) ---
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

        // Datepicker tidak ada di sini karena tidak ada filter tanggal di index sparepart

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
                    "data": "sparepart_id", // Untuk nomor urut
                    "sortable": false,
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "sparepart_id", // Untuk Aksi
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
                { "data": "sparepart_id", "className": "text-left" },
                { "data": "sparepart_nm", "className": "text-left" },
                { "data": "no_seri", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "merk", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "satuan", "className": "text-left", "render": function(data){ return ifNull(data); } },
                { "data": "harga", "className": "text-right", "render": function(data){ return numId(data, true); } },
                { "data": "stok", "className": "text-center", "render": function(data){ return ifNull(data); } },
                { "data": "lokasi_penyimpanan", "className": "text-left", "render": function(data){ return ifNull(data); } },
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

        // Inisialisasi Autonumeric untuk input harga
        modalContent.find('.autonumeric').autoNumeric({
            aSep: ".",
            aDec: ",",
            vMax: "999999999999999",
            vMin: "0"
        });
        
        // Datepicker tidak ada di sini karena tidak ada input tanggal di form sparepart

        
    });
</script>