<script type="text/javascript">
    $(document).ready(function() {
        // Inisialisasi Select2 untuk dropdown filter
        $('.chosen-select').select2({
            theme: "bootstrap-5"
        });

        // Inisialisasi DateRangePicker
        $('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });
    });

    // Fungsi untuk mencari data (handle form submit)
    function _search(e) {
        if (e) e.preventDefault();
        
        // Ambil form yang disubmit
        const form = e.target;
        if (!form) return false;
        
        // Submit form secara normal
        form.submit();
        return false;
    }

    // Fungsi untuk mereset pencarian
    function _searchReset() {
        // Reset semua field form
        $('select.chosen-select').val('').trigger('change');
        $('input[type="text"]').val('');
        
        // Submit form dengan nilai kosong
        $('#search').submit();
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
</script>