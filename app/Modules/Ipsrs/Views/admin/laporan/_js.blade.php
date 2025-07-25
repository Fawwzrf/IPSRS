<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        $('.chosen-select').select2({
            theme: "bootstrap-5"
        });
        $('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });

        // Tentukan kolom DataTable sesuai halaman
        var columns = [];
        var ajaxUrl = '';
        if ($('#datatable-main').length) {
            if ($('.page-wrapper').hasClass('laporan-kinerja-aset')) {
                // Pastikan window.n selalu ada, jika tidak gunakan string acak atau session id
                var nVal = window.n || "{{ request('n') }}";
                ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjaaset/ajax?n=' + nVal;
                columns = [{
                        data: null,
                        orderable: false,
                        className: "text-center",
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: "asset_nm"
                    },
                    {
                        data: "merk"
                    },
                    {
                        data: "kategori_asset_nm"
                    },
                    {
                        data: "lokasi_nm"
                    },
                    {
                        data: "jumlah_ok",
                        className: "text-center"
                    },
                    {
                        data: "jumlah_perbaikan",
                        className: "text-center"
                    },
                    {
                        data: "jumlah_pemeliharaan",
                        className: "text-center"
                    },
                    {
                        data: "terakhir_ditangani",
                        className: "text-center"
                    }
                ];
            } else if ($('.page-wrapper').hasClass('laporan-kinerja-tim')) {
                ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjatim/ajax?n=' + (window.n ||
                    "{{ request('n') }}");
                columns = [{
                        data: null,
                        orderable: false,
                        className: "text-center",
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: "order_kerja_id"
                    },
                    {
                        data: "jenis"
                    },
                    {
                        data: "nama_teknisi"
                    },
                    {
                        data: "nama_aset"
                    },
                    {
                        data: "durasi_respon_admin",
                        className: "text-center"
                    },
                    {
                        data: "durasi_penerimaan_teknisi",
                        className: "text-center"
                    },
                    {
                        data: "durasi_pengerjaan",
                        className: "text-center"
                    },
                    {
                        data: "durasi_total",
                        className: "text-center"
                    }
                ];
            } else if ($('.page-wrapper').hasClass('laporan-kinerja-teknisi')) {
                ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjateknisi/ajax?n=' + (window.n ||
                    "{{ request('n') }}");
                columns = [{
                        data: null,
                        orderable: false,
                        className: "text-center",
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: "nama_teknisi"
                    },
                    {
                        data: "total_tugas"
                    },
                    {
                        data: "tugas_selesai"
                    },
                    {
                        data: "persentase_selesai"
                    },
                    {
                        data: "rata_rata_durasi"
                    }
                ];
            } else if ($('.page-wrapper').hasClass('laporan-biaya-pemeliharaan')) {
                ajaxUrl = _base_url + 'ipsrs/adminlaporan/biayapemeliharaan/ajax?n=' + (window.n ||
                    "{{ request('n') }}");
                columns = [{
                        data: null,
                        orderable: false,
                        className: "text-center",
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: "tgl_dibuat"
                    },
                    {
                        data: "order_kerja_id"
                    },
                    {
                        data: "asset_nm"
                    },
                    {
                        data: "jenis"
                    },
                    {
                        data: "total_biaya_sparepart",
                        className: "text-end"
                    },
                    {
                        data: "biaya_lain",
                        className: "text-end"
                    },
                    {
                        data: "total_biaya_ok",
                        className: "text-end"
                    },
                ];
            }

            // Inisialisasi DataTable jika kolom sudah ditentukan
            if (columns.length > 0) {
                tabel = $('#datatable-main').DataTable({
                    language: {
                        url: _base_url + 'dist/libs/DataTables/id.json'
                    },
                    processing: true,
                    autoWidth: false,
                    serverSide: true,
                    ordering: true,
                    responsive: true,
                    searching: false,
                    ajax: {
                        url: ajaxUrl,
                        type: "POST",
                        data: function(d) {
                            // Ambil data filter dari form jika ada
                            $('.filter-form').find('input,select').each(function() {
                                d[$(this).attr('name')] = $(this).val();
                            });
                            // Untuk DataTables, agar search global tetap sinkron:
                            d.search = {
                                value: $('input[name="search"]').val()
                            };
                        }
                    },
                    columns: columns
                });
            }
        }

        // Print handler dengan styling
        function printReport() {
            const headContent = $('head').html();
            const printContent = $('.page-wrapper').html();
            const windowUrl = 'about:blank';
            const uniqueName = new Date().getTime();

            const printWindow = window.open(windowUrl, uniqueName);

            printWindow.document.write('<html><head>');
            printWindow.document.write(headContent);
            printWindow.document.write('<style>');
            printWindow.document.write(`
            body { font-family: Arial, sans-serif; }
            .d-print-none { display: none !important; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
            th, td { padding: 0.5rem; border: 1px solid #dee2e6; }
            thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; }
            .text-end { text-align: right; }
            .text-center { text-align: center; }
            .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
            .container-xl { width: 100%; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto; }
            .page-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; }
            tfoot { font-weight: bold; }
        `);
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div class="container-xl">');
            printWindow.document.write(printContent);
            printWindow.document.write('</div>');
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.focus();

            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 250);
        }

        $('.btn-filter').on('click', function(e) {
            e.preventDefault();
            var periode = $('#periode-filter').val();
            if (periode === 'harian') {
                var tgl = $('input[name="tgl_single"]').val();
                $('input[name="tgl_start"]').val(tgl);
                $('input[name="tgl_end"]').val(tgl);
            }
            // Reset tgl_start dan tgl_end jika filter kosong
            if (!periode || periode === '') {
                $('input[name="tgl_start"]').val('');
                $('input[name="tgl_end"]').val('');
            }

            if (periode === 'bulanan') {
                var bulan = $('select[name="bulan"]').val();
                var tahun = $('select[name="tahun_bulan"]').val();
                if (bulan && tahun) {
                    $('input[name="tgl_start"]').val('01-' + bulan + '-' + tahun);
                    // Hitung hari terakhir bulan
                    var lastDay = new Date(tahun, bulan, 0).getDate();
                    $('input[name="tgl_end"]').val(lastDay + '-' + bulan + '-' + tahun);
                }
            } else if (periode === 'tahunan') {
                var tahun = $('select[name="tahun"]').val();
                if (tahun) {
                    $('input[name="tgl_start"]').val('01-01-' + tahun);
                    $('input[name="tgl_end"]').val('31-12-' + tahun);
                }
            } else if (periode === 'custom' || periode === 'harian') {
                var tglMulai = $('input[name="tgl_mulai"]').val();
                var tglAkhir = $('input[name="tgl_akhir"]').val();
                if (tglMulai && tglAkhir) {
                    $('input[name="tgl_start"]').val(tglMulai);
                    $('input[name="tgl_end"]').val(tglAkhir);
                }
            } else {
                // Jika tidak ada periode, kosongkan tgl_start dan tgl_end
                $('input[name="tgl_start"]').val('');
                $('input[name="tgl_end"]').val('');
            }
            if (typeof tabel !== 'undefined' && tabel) {
                tabel.ajax.reload();
            } else {
                _search(e);
            }
        });

        $('.btn-reset').on('click', function(e) {
            e.preventDefault();
            $('.filter-form').find('input,select').val('');
            $('.filter-form').find('select').trigger('change');
            if (typeof tabel !== 'undefined' && tabel) {
                tabel.ajax.reload();
            } else {
                _searchReset();
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        if (tabel) {
            tabel.on('draw', function() {
                $('#count-data').text(tabel.data().count());
            });
        }
    });
</script>
<script>
    $(document).ready(function() {
        function updatePeriodeFilter() {
            var val = $('#periode-filter').val();
            $('.filter-tgl-mulai, .filter-tgl-akhir, .filter-tgl-single, .filter-bulan, .filter-tahun').hide();
            if (val === 'custom') {
                $('.filter-tgl-mulai, .filter-tgl-akhir').show();
            } else if (val === 'harian') {
                $('.filter-tgl-single').show();
            } else if (val === 'bulanan') {
                $('.filter-bulan').show();
            } else if (val === 'tahunan') {
                $('.filter-tahun').show();
            }
        }
        $('#periode-filter').on('change', updatePeriodeFilter);
        updatePeriodeFilter();

        // Datepicker
        $('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });
        $('.monthpicker').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'MM-YYYY'
            },
            minViewMode: 1
        });
        $('.yearpicker').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'YYYY'
            },
            minViewMode: 2
        });
    });
</script>
<script>
    $(document).on('click', '.btn-export-excel', function(e) {
        e.preventDefault();
        var $form = $(this).closest('.card-body').find('form.filter-form');
        var action = $form.attr('action') || window.location.pathname;
        var params = $form.serialize();

        // Jika parameter n belum ada, tambahkan dari URL
        if (params.indexOf('n=') === -1) {
            var n = (new URLSearchParams(window.location.search)).get('n');
            if (n) {
                params += (params.length > 0 ? '&' : '') + 'n=' + encodeURIComponent(n);
            }
        }

        // Tambahkan export=excel
        params += (params.length > 0 ? '&' : '') + 'export=excel';

        window.location.href = action + (action.indexOf('?') > -1 ? '&' : '?') + params;
    });
</script>
<script>
let printOrientation = 'portrait';
$(document).on('click', '.btn-print-orientation', function() {
    printOrientation = $(this).data('orientation');
    if (printOrientation === 'landscape') {
        $('style#print-orientation').remove();
        $('<style id="print-orientation">@media print {@page {size: A4 landscape;}}</style>').appendTo('head');
    } else {
        $('style#print-orientation').remove();
        $('<style id="print-orientation">@media print {@page {size: A4 portrait;}}</style>').appendTo('head');
    }
});
</script>
