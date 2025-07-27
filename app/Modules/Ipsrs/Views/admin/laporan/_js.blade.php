<script type="text/javascript">
var tabel = null;
$(document).ready(function() {
    // Inisialisasi select2 dan datepicker
    $('.chosen-select').select2({ theme: "bootstrap-5" });
    $('.datepicker-notauto').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'DD-MM-YYYY' }
    });

    // Tentukan ajaxUrl dan columns sesuai laporan
    var ajaxUrl = '';
    var columns = [];
    var $wrapper = $('.page-wrapper');
    if ($('#datatable-main').length) {
        if ($wrapper.hasClass('laporan-kinerja-aset')) {
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjaaset';
            columns = [
                { data: null, orderable: false, className: "text-center", render: rowNumber },
                { data: "asset_nm" },
                { data: "merk" },
                { data: "kategori_asset_nm" },
                { data: "lokasi_nm" },
                { data: "jumlah_ok", className: "text-center" },
                { data: "jumlah_perbaikan", className: "text-center" },
                { data: "jumlah_pemeliharaan", className: "text-center" },
                { data: "terakhir_ditangani", className: "text-center" }
            ];
        } else if ($wrapper.hasClass('laporan-kinerja-tim')) {
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjatim';
            columns = [
                { data: null, orderable: false, className: "text-center", render: rowNumber },
                { data: "order_kerja_id" },
                { data: "jenis" },
                { data: "nama_teknisi" },
                { data: "nama_aset" },
                { data: "durasi_respon_admin", className: "text-center" },
                { data: "durasi_penerimaan_teknisi", className: "text-center" },
                { data: "durasi_pengerjaan", className: "text-center" },
                { data: "durasi_total", className: "text-center" }
            ];
        } else if ($wrapper.hasClass('laporan-kinerja-teknisi')) {
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/kinerjateknisi';
            columns = [
                { data: null, orderable: false, className: "text-center", render: rowNumber },
                { data: "nama_teknisi" },
                { data: "total_tugas" },
                { data: "tugas_selesai" },
                { data: "persentase_selesai" },
                { data: "rata_rata_durasi" }
            ];
        } else if ($wrapper.hasClass('laporan-biaya-pemeliharaan')) {
            ajaxUrl = _base_url + 'ipsrs/adminlaporan/biayapemeliharaan';
            columns = [
                { data: null, orderable: false, className: "text-center", render: rowNumber },
                { data: "tgl_dibuat" },
                { data: "order_kerja_id" },
                { data: "asset_nm" },
                { data: "jenis" },
                { data: "total_biaya_sparepart", className: "text-end" },
                { data: "biaya_lain", className: "text-end" },
                { data: "total_biaya_ok", className: "text-end" }
            ];
        }

        // Inisialisasi DataTable jika columns terisi
        if (columns.length > 0) {
            tabel = $('#datatable-main').DataTable({
                language: { url: _base_url + 'dist/libs/DataTables/id.json' },
                processing: true,
                autoWidth: false,
                serverSide: true,
                searching: true,
                ordering: true,
                responsive: true,
                ajax: {
                    url: ajaxUrl,
                    type: "POST",
                    data: function(d) {
                        d.n = $('input[name="n"]').val() || _getNavN();
                    }
                },
                columns: columns,
                dom: "<'row'<'col-sm-6'l><'col-sm-6'>>tr<'row'<'col-sm-12'p>>"
            });
        }
    }

    // Fungsi render nomor urut
    function rowNumber(data, type, row, meta) {
        return meta.row + meta.settings._iDisplayStart + 1;
    }

    // Handler tombol filter
    $('.btn-filter').on('click', function(e) {
        e.preventDefault();
        var searchData = {};
        $('.filter-form').find('input,select').each(function() {
            searchData[$(this).attr('name')] = $(this).val();
        });
        var n = $('input[name="n"]').val() || _getNavN();
        var url = window.location.pathname;
        var searchText = $('.filter-form input[name="search"]').val();
        $.post(url, {
            search_act: 'save',
            search: searchData,
            n: n
        }, function() {
            if (tabel) {
                tabel.search(searchText || '').draw();
            } else {
                _search(e);
            }
        });
    });

    // Handler tombol reset
    $('.btn-reset').on('click', function(e) {
        e.preventDefault();
        var n = $('input[name="n"]').val() || _getNavN();
        $.post(window.location.pathname, {
            search_act: 'reset',
            n: n
        }, function() {
            $('.filter-form').find('input,select').val('');
            $('.filter-form').find('select').trigger('change');
            if (tabel) {
                tabel.search('').draw();
                tabel.ajax.reload();
            } else {
                _searchReset();
            }
        });
    });

    // Hitung data pada draw
    if (tabel) {
        tabel.on('draw', function() {
            $('#count-data').text(tabel.data().count());
        });
    }

    // Periode filter handler
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

    // Datepicker untuk bulan dan tahun
    $('.monthpicker').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'MM-YYYY' },
        minViewMode: 1
    });
    $('.yearpicker').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'YYYY' },
        minViewMode: 2
    });

    // Export excel handler
    $(document).on('click', '.btn-export-excel', function(e) {
        e.preventDefault();
        var $form = $(this).closest('.card-body').find('form.filter-form');
        var action = $form.attr('action') || window.location.pathname;
        var params = $form.serialize();
        params += (params.length > 0 ? '&' : '') + 'export=excel';
        if (params.indexOf('n=') === -1) {
            params += '&n=' + (_getNavN() || $('input[name="n"]').val());
        }
        window.location.href = action + (action.indexOf('?') > -1 ? '&' : '?') + params;
    });

    // Print orientation handler
    let printOrientation = 'portrait';
    $(document).on('click', '.btn-print-orientation', function() {
        printOrientation = $(this).data('orientation');
        $('style#print-orientation').remove();
        var orientationCss = printOrientation === 'landscape'
            ? '@media print {@page {size: A4 landscape;}}'
            : '@media print {@page {size: A4 portrait;}}';
        $('<style id="print-orientation">' + orientationCss + '</style>').appendTo('head');
    });

    // Helper nav n
    function _getNavN() {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('n');
    }
});
</script>
