<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        // Inisialisasi DataTable untuk log kerja admin
        tabel = $('#datatable-main').DataTable({
            "language": { url: _base_url + 'dist/libs/DataTables/id.json' },
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "ordering": true,
            "order": [ [1, 'desc'] ],
            "ajax": {
                "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
                "type": "POST"
            },
            "deferRender": true,
            "aLengthMenu": _datatableLengthMenu,
            "pageLength": 10,
            "bFilter": false,
            "columns": [
                { "data": null, "orderable": false, "className": "text-center", 
                  "render": function (data, type, row, meta) { 
                      return meta.row + meta.settings._iDisplayStart + 1; 
                  }
                },
                { "data": "order_kerja_id" },
                { "data": "teknisi_nm" },
                { 
                    "data": "tgl_selesai", 
                    "render": function(data) { 
                        return data ? toDate(data) : '-'; 
                    }
                },
                { 
                    "data": "hasil", 
                    "render": function(data) {
                        if (!data) return '<span class="badge bg-secondary">-</span>';
                        var badgeClass = { 
                            'berhasil': 'bg-success', 
                            'perlu_tindak_lanjut': 'bg-warning', 
                            'tidak_berhasil': 'bg-danger' 
                        };
                        return '<span class="badge ' + (badgeClass[data] || 'bg-secondary') + '">' + 
                               data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + 
                               '</span>';
                    }
                },
                { 
                    "data": "foto_count", 
                    "className": "text-center",
                    "render": function(data) {
                        return data > 0 ? 
                            '<span class="badge bg-primary">' + data + ' foto</span>' : 
                            '<span class="badge bg-secondary">Tidak ada</span>';
                    }
                },
                { 
                    "data": "log_kerja_id", 
                    "orderable": false,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-sm btn-info" onclick="viewDetails(\'' + data + '\')"><i class="fas fa-eye"></i> Lihat Detail</button>';
                    }
                }
            ]
        });

        $(document).on('shown.bs.modal', function(e) {
            var modal = $(e.target);
            var repeater = modal.find('#sparepart-repeater');
            // Inisialisasi hanya jika belum pernah dan plugin tersedia
            if (repeater.length > 0 && typeof repeater.data('repeater-init') === 'undefined' &&
                typeof repeater.repeater === 'function') {
                repeater.repeater({
                    initEmpty: false,
                    show: function() {
                        $(this).slideDown();
                        $(this).find('.repeater-select').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: modal
                        });
                    },
                    hide: function(deleteElement) {
                        $(this).slideUp(deleteElement);
                    }
                });
                repeater.data('repeater-init', true); // Mark as initialized
                repeater.find('.repeater-select').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: modal
                });
            }
        });

    });
    function _modalHide() {
        // Tutup semua modal yang terbuka
        if ($('.modal.show').length) {
            $('.modal.show').modal('hide');
        }
    }

    // Fungsi untuk melihat detail log kerja
    function viewDetails(orderKerjaId) {
        _modal(event, {
            uri: _base_url + 'ipsrs/adminorderkerja/hasil_teknisi_modal/' + orderKerjaId,
            title: 'Hasil Tugas Teknisi',
            size: 'modal-lg'
        });
    }
</script>