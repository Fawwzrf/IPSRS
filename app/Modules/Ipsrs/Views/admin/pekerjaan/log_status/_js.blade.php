{{-- filepath: c:\laragon\www\ipsrs\app\Modules\Ipsrs\Views\admin\pekerjaan\log_status\_js.blade.php --}}
<script type="text/javascript">
    $(document).ready(function() {
        // Inisialisasi DataTable jika diperlukan
        if ($('#datatable-main').length) {
            $('#datatable-main').DataTable({
                "language": { url: _base_url + 'dist/libs/DataTables/id.json' },
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "ordering": true,
                "order": [ [2, 'desc'] ],
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
                    { 
                        "data": "tgl_perubahan", 
                        "render": function(data) { 
                            return data ? toDate(data) : '-'; 
                        }
                    },
                    { 
                        "data": "status_sebelumnya", 
                        "render": function(data) {
                            return '<span class="badge bg-secondary">' + 
                                   (data ? data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-') + 
                                   '</span>';
                        }
                    },
                    { 
                        "data": "status_baru", 
                        "render": function(data) {
                            if (!data) return '<span class="badge bg-secondary">-</span>';
                            
                            var badgeClass = { 
                                'menunggu': 'bg-warning', 
                                'diproses': 'bg-info', 
                                'selesai': 'bg-success',
                                'dibatalkan': 'bg-danger',
                                'menunggu_sparepart': 'bg-primary'
                            };
                            
                            return '<span class="badge ' + (badgeClass[data] || 'bg-secondary') + '">' + 
                                   data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + 
                                   '</span>';
                        }
                    },
                    { "data": "pegawai_nm" },
                    { 
                        "data": "order_kerja_id", 
                        "orderable": false,
                        "render": function(data, type, row) {
                            return '<button class="btn btn-sm btn-info" onclick="viewDetails(\'' + data + '\')"><i class="fas fa-eye"></i> Detail</button>';
                        }
                    }
                ]
            });
        }
    });

    // Fungsi untuk melihat detail riwayat status
    function viewDetails(order_kerja_id) {
        _modal(event, {
            title: 'Riwayat Status Order Kerja',
            uri: '<?= $uri ?>/form_modal/' + order_kerja_id,
            size: 'modal-lg'
        });
    }
</script>