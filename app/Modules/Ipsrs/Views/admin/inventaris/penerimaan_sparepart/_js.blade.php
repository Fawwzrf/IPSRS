<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        // --- Inisialisasi Datatables ---
        tabel = $('#datatable-main').DataTable({
            "language": {
                url: _base_url + 'dist/libs/DataTables/id.json'
            },
            "processing": true,
            "serverSide": true,
            "ordering": true,
            "order": [
                [3, 'desc']
            ], // Default sort berdasarkan tanggal terbaru
            "ajax": {
                "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
                "type": "POST",
                "data": function(d) {
                    d._token = _token;
                }
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
                    "data": "penerimaan_id",
                    "orderable": false,
                    "className": "text-center",
                    "render": function(data) {
                        var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
                        var uri_delete = '<?= $uri . '/delete/' ?>' + data;
                        
                        return '<div class="btn-list btn-sm flex-nowrap">' +
                            '   <div class="dropdown"> ' +
                            '      <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">' +
                            '          Aksi' +
                            '      </button>' +
                            '      <div class="dropdown-menu">' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: \'' + uri_edit + '\', size: \'modal-lg\'})">' +
                            '             <i class="fas fa-pencil-alt text-warning me-2"></i> Ubah Data' +
                            '         </a>' +
                            '         <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_delete(\'' + uri_delete + '\')">' +
                            '             <i class="fas fa-trash text-danger me-2"></i> Hapus Data' +
                            '         </a>' +
                            '      </div>' +
                            '   </div>' +
                            '</div>';
                    }
                },
                {
                    "data": "penerimaan_id"
                },
                {
                    "data": "tgl",
                    "render": function(data) {
                        return data ? toDate(data) : '-';
                    }
                },
                {
                    "data": "sparepart_nm"
                },
                {
                    "data": "jumlah",
                    "className": "text-center"
                },
                {
                    "data": "harga_satuan",
                    "className": "text-end",
                    "render": function(data) {
                        return numId(data, true);
                    }
                },
                {
                    "data": "vendor",
                    "render": function(data) {
                        return ifNull(data);
                    }
                },
                {
                    "data": "no_faktur",
                    "render": function(data) {
                        return ifNull(data);
                    }
                }
            ]
        });
    });

    // Event handler untuk inisialisasi plugin di dalam modal
    $(document).on('shown.bs.modal', '#my-modal-1', function(e) {
        var modal = $(this);
        // Pastikan hanya modal dari modul ini yang diinisialisasi
        if (modal.find('#form-penerimaan-sparepart').length > 0) {
            // Inisialisasi Select2
            modal.find('.chosen-select').select2({
                theme: "bootstrap-5",
                dropdownParent: modal
            });
            
            // Inisialisasi DateRangePicker
            modal.find('.datepicker-notauto').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'DD-MM-YYYY'
                }
            });
            
            // Inisialisasi AutoNumeric untuk format harga
            modal.find('input[name="harga_satuan"]').autoNumeric('init', {
                aSep: '.',
                aDec: ',',
                mDec: '0',
                vMax: '999999999999'
            });
        }
    });

    // Handler untuk form submit - perbaikan untuk mencegah submit ganda
    $(document).off('click', 'button[onclick="_save(event)"]').on('click', 'button[onclick="_save(event)"]', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        // Validasi form
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }
        
        // Disable button untuk mencegah klik dobel
        var btn = $(this);
        btn.prop('disabled', true);
        
        // Submit form menggunakan AJAX
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.code === '01' || response.code === '02') {
                    _toast('success', 'Data berhasil disimpan.');
                    _modalHide();
                    
                    // Reload tabel
                    if (tabel) tabel.ajax.reload();
                } else {
                    _toast('error', response.message || 'Gagal menyimpan data.');
                }
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                _toast('error', msg);
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
        
        return false;
    });
</script>
