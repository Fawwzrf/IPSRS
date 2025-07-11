<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        $('#filter-body .chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#filter-body')
        });

        tabel = $('#datatable-main').DataTable({
            "language": {
                url: _base_url + 'dist/libs/DataTables/id.json'
            },
            "processing": true,
            "serverSide": true,
            "ordering": true,
            "order": [
                [2, 'desc']
            ],
            "ajax": {
                "url": "{{ url($uri . '/ajax_datatables?n=' . request('n')) }}",
                "type": "POST",
                "data": function(d) {
                    d._token = _token;
                }
            },
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
                    "data": "prioritas"
                },
                {
                    "data": "status",
                    "className": "text-center",
                    "render": function(data) {
                        var badgeClass = {
                            'baru': 'bg-info',
                            'diproses': 'bg-warning',
                            'selesai': 'bg-success',
                            'ditugaskan': 'bg-primary'
                        };
                        return `<span class="badge ${badgeClass[data] || 'bg-secondary'}">${data ? data.charAt(0).toUpperCase() + data.slice(1) : ''}</span>`;
                    }
                }
            ]
        });
    });

    $(document).on('shown.bs.modal', '#my-modal-1', function (e) {
        var modal = $(this);
        modal.find('.chosen-select').select2({ theme: "bootstrap-5", dropdownParent: modal });
        modal.find('.datepicker-notauto').daterangepicker({ singleDatePicker: true, showDropdowns: true, locale: { format: 'DD-MM-YYYY' }});

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
        jadwalSelect.on('change', function(){
            toggleSource($(this), komplainSelect);
        });
        komplainSelect.on('change', function(){
            toggleSource($(this), jadwalSelect);
        });
        
        // ======================================================
        // PERBAIKAN KUNCI: Panggil fungsi toggle saat modal dibuka
        // untuk menangani state saat mode edit.
        // ======================================================
        toggleSource(jadwalSelect, komplainSelect);
        toggleSource(komplainSelect, jadwalSelect);
    });
</script>
