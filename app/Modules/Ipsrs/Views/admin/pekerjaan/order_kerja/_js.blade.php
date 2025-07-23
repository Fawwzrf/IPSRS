<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        // Inisialisasi Select2 untuk filter
        $('#filter .chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#filter')
        });

        // Inisialisasi DataTables
        tabel = $('#datatable-main').DataTable({
            "language": {
                url: _base_url + 'dist/libs/DataTables/id.json'
            },
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "ordering": true,
            "order": [
                [2, 'desc']
            ],
            "ajax": {
                "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
                "type": "POST"
            },
            "deferRender": true,
            "aLengthMenu": _datatableLengthMenu,
            "pageLength": 10,
            "bFilter": false,
            "columns": [{
                    "data": null,
                    "orderable": false,
                    "className": "text-center",
                    "render": function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "order_kerja_id",
                    "orderable": false,
                    "className": "text-center",
                    "render": function(data, type, row, meta) {
                        var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
                        var uri_delete = '<?= $uri . '/delete/' ?>' + data;
                        var uri_update_status = '<?= $uri . '/update_status_form/' ?>' + data;
                        var uri_riwayat_status =
                            '<?= url('ipsrs/logstatusorderkerja/form_modal') ?>/' + data;
                        var uri_hasil_teknisi = '<?= $uri . '/hasil_teknisi_modal/' ?>' + data;

                        return '' +
                            '<div class="btn-list btn-sm flex-nowrap">' +
                            '   <div class="dropdown"> ' +
                            '      <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
                            '          Aksi' +
                            '      </button>' +
                            '      <div class="dropdown-menu">' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' +
                            uri_edit + '\', size: \'modal-lg\', position: \'normal\'})">' +
                            '             <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah Data' +
                            '         </a>' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' +
                            uri_update_status +
                            '\', title: \'Update Status Order Kerja\', size: \'modal-md\'})">' +
                            '             <i class="fas fa-sync-alt text-primary me-2"></i> Update Status' +
                            '         </a>' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' +
                            uri_riwayat_status +
                            '\', title: \'Riwayat Status Order Kerja\', size: \'modal-lg\'})">' +
                            '             <i class="fas fa-history text-info me-2"></i> Riwayat Status' +
                            '         </a>' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' +
                            uri_hasil_teknisi +
                            '\', title: \'Hasil Teknisi\', size: \'modal-lg\'})">' +
                            '             <i class="fas fa-user-cog text-success me-2"></i> Hasil Teknisi' +
                            '         </a>' +
                            '         <div class="dropdown-divider"></div>' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_delete(\'' +
                            uri_delete + '\')">' +
                            '             <i class="fas fa-trash text-danger me-2"></i> Hapus Data' +
                            '         </a>' +
                            '      </div>' +
                            '   </div>' +
                            '</div>';
                    }
                },
                {
                    "data": "order_kerja_id"
                },
                {
                    "data": "jenis",
                    "render": function(data) {
                        return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
                    }
                },
                {
                    "data": "asset_nm",
                    "render": function(data, type, row) {
                        return `<strong>${data || 'N/A'}</strong><br><small class="text-muted">${row.deskripsi_sumber || 'N/A'}</small>`;
                    }
                },
                {
                    "data": "tim_teknisi"
                },
                {
                    "data": "prioritas",
                    "className": "text-center",
                    "render": function(data, type, row) {
                        if (!data) {
                            return '<span class="badge bg-secondary">-</span>';
                        }

                        // Pemetaan prioritas ke warna dan ikon yang lebih sederhana
                        var badgeClass, badgeIcon, badgeText;

                        switch (data) {
                            case 'Normal':
                                badgeClass = 'bg-success';
                                badgeIcon = 'fa fa-check-circle';
                                badgeText = 'Normal';
                                break;
                            case 'Mendesak':
                                badgeClass = 'bg-warning text-dark';
                                badgeIcon = 'fa fa-exclamation-circle';
                                badgeText = 'Mendesak';
                                break;
                            case 'Darurat':
                                badgeClass = 'bg-danger';
                                badgeIcon = 'fa fa-exclamation-triangle';
                                badgeText = 'Darurat';
                                break;
                            default:
                                badgeClass = 'bg-secondary';
                                badgeIcon = 'fa fa-question-circle';
                                badgeText = data.charAt(0).toUpperCase() + data.slice(1);
                        }

                        // Pastikan HTML dirender dengan benar
                        return '<span class="badge ' + badgeClass + '"><i class="' + badgeIcon +
                            ' me-1"></i> ' + badgeText + '</span>';
                    }
                },
                {
                    "data": "status",
                    "className": "text-center",
                    "render": function(data) {
                        if (!data) {
                            return '<span class="badge bg-secondary">-</span>';
                        }

                        var badgeClass;
                        switch (data) {
                            case 'baru':
                                badgeClass = 'bg-info';
                                break;
                            case 'ditugaskan':
                                badgeClass = 'bg-primary';
                                break;
                            case 'diproses':
                                badgeClass = 'bg-warning text-dark';
                                break;
                            case 'selesai':
                                badgeClass = 'bg-success';
                                break;
                            case 'dibatalkan':
                                badgeClass = 'bg-danger';
                                break;
                            case 'menunggu_sparepart':
                                badgeClass = 'bg-secondary';
                                break;
                            default:
                                badgeClass = 'bg-secondary';
                        }

                        return '<span class="badge ' + badgeClass + '">' + data.replace(/_/g,
                                ' ').charAt(0).toUpperCase() + data.replace(/_/g, ' ').slice(
                            1) + '</span>';
                    }
                }
            ],
            "createdRow": function(row, data, dataIndex) {
                // Beri highlight pada baris dengan prioritas darurat
                if (data.prioritas == 'darurat') {
                    $(row).addClass('bg-danger-subtle');
                }
            }
        });
    });

    // Inisialisasi plugin di dalam modal
    $(document).on('shown.bs.modal', '#my-modal-1', function(e) {
        var modal = $(this);
        modal.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: modal
        });
        modal.find('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });

        var jadwalSelect = modal.find('#jadwal_pm_id');
        var komplainSelect = modal.find('#permintaan_id');

        // Fungsi untuk menonaktifkan dropdown lain
        function toggleSource(select1, select2) {
            if (select1.val()) {
                // Nonaktifkan select2 dan perbarui tampilannya
                select2.val('').prop('disabled', true).trigger('change.select2');
            } else {
                // Aktifkan kembali select2
                select2.prop('disabled', false).trigger('change.select2');
            }
        }

        // Pasang event handler
        jadwalSelect.on('change', function() {
            toggleSource($(this), komplainSelect);
        });
        komplainSelect.on('change', function() {
            toggleSource($(this), jadwalSelect);
        });

        // Panggil fungsi toggle saat modal dibuka
        // untuk menangani state saat mode edit
        toggleSource(jadwalSelect, komplainSelect);
        toggleSource(komplainSelect, jadwalSelect);
    });
</script>
