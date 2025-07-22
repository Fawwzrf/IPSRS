<script type="text/javascript">
var tabel = null;
$(document).ready(function() {
    $('.chosen-select').select2({ theme: "bootstrap-5" });
    $('.datepicker-notauto').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'DD-MM-YYYY' }
    });

    // Tentukan kolom DataTable sesuai halaman
    var columns = [];
    var ajaxUrl = '';
    if ($('#datatable-main').length) {
        if ($('.page-wrapper').hasClass('laporan-kinerja-aset')) {
            // Pastikan window.n selalu ada, jika tidak gunakan string acak atau session id
            var nVal = window.n || "{{ request('n') }}" ;
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjaaset/ajax?n=' + nVal;
            columns = [
                { data: null, orderable: false, className: "text-center",
                  render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
                },
                { data: "asset_nm" },
                { data: "lokasi_nm" },
                { data: "jumlah_ok", className: "text-center" },
                { data: "jumlah_perbaikan", className: "text-center" },
                { data: "jumlah_pemeliharaan", className: "text-center" },
                { data: "terakhir_ditangani", className: "text-center" }
            ];
        } else if ($('.page-wrapper').hasClass('laporan-kinerja-tim')) {
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjatim/ajax?n=' + (window.n || "{{ request('n') }}" );
            columns = [
                { data: null, orderable: false, className: "text-center",
                  render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
                },
                { data: "order_kerja_id" },
                { data: "jenis" },
                { data: "nama_teknisi" },
                { data: "nama_aset" },
                { data: "durasi_respon_admin", className: "text-center" },
                { data: "durasi_penerimaan_teknisi", className: "text-center" },
                { data: "durasi_pengerjaan", className: "text-center" },
                { data: "durasi_total", className: "text-center" }
            ];
        } else if ($('.page-wrapper').hasClass('laporan-kinerja-teknisi')) {
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjateknisi/ajax?n=' + (window.n || "{{ request('n') }}" );
            columns = [
                { data: null, orderable: false, className: "text-center",
                  render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
                },
                { data: "nama_teknisi" },
                { data: "total_tugas" },
                { data: "tugas_selesai" },
                { data: "persentase_selesai" },
                { data: "rata_rata_durasi" }
            ];
        } else if ($('.page-wrapper').hasClass('laporan-biaya-pemeliharaan')) {
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/biayapemeliharaan/ajax?n=' + (window.n || "{{ request('n') }}");
            columns = [
                { data: null, orderable: false, className: "text-center",
                  render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
                },
                { data: "tgl_dibuat" },
                { data: "order_kerja_id" },
                { data: "asset_nm" },
                { data: "jenis" },
                { data: "total_biaya_sparepart", className: "text-end" },
                { data: "biaya_lain", className: "text-end" },
                { data: "total_biaya_ok", className: "text-end" },
            ];
        }

        // Inisialisasi DataTable jika kolom sudah ditentukan
        if (columns.length > 0) {
            tabel = $('#datatable-main').DataTable({
                language: { url: _base_url + 'dist/libs/DataTables/id.json' },
                processing: true,
                serverSide: true,
                ordering: true,
                responsive: true,
                ajax: {
                    url: ajaxUrl,
                    type: "POST",
                    data: function(d) {
                        // Ambil data filter dari form jika ada
                        $('.filter-form').find('input,select').each(function() {
                            d[$(this).attr('name')] = $(this).val();
                        });
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
        if (typeof tabel !== 'undefined' && tabel) {
            tabel.ajax.reload();
        } else {
            _search(e); // fallback jika bukan datatables
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