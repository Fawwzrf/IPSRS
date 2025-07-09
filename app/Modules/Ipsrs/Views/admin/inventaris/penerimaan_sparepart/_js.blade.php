<script type="text/javascript">
    // --- Placeholder _loading function (untuk mengatasi error jika tidak ada) ---
    if (typeof _loading === 'undefined') {
        window._loading = function(show, message) {
            console.log("Loading state: " + show);
        };
    }

    var tabel = null;
    $(document).ready(function() {
        console.log("✅ Document Ready. DataTable and other initializations are being set up.");
        
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
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
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
                            '       <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
                            '           Aksi' +
                            '       </button>' +
                            '       <div class="dropdown-menu">' +
                            '           <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_edit + '\', size: \'modal-lg\', position: \'normal\'})">' +
                            '               <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah Data' +
                            '           </a>' +
                            '           <a class="dropdown-item p-1" href="javascript:void(0)" onclick=_delete("' + uri_delete + '")>' +
                            '               <i class="fas fa-trash text-danger me-2"></i> Hapus Data' +
                            '           </a>' +
                            '       </div>' +
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

</script>