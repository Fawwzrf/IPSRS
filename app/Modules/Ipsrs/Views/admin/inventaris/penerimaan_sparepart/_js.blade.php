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
                    "data": "penerimaan_id",
                    "orderable": false,
                    "className": "text-center",
                    "render": function(data) {
                        var uri_edit = '{{ url($uri . '/form_modal/') }}' + data;
                        var uri_delete = '{{ url($uri . '/delete/') }}' + data;
                        return `<div class="btn-list flex-nowrap">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">Aksi</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: '${uri_edit}', size: 'modal-lg'})"><i class="fas fa-pencil-alt text-warning me-2"></i> Ubah Data</a>
                                        <a class="dropdown-item p-1" href="javascript:void(0)" onclick="_delete('${uri_delete}')"><i class="fas fa-trash text-danger me-2"></i> Hapus Data</a>
                                    </div>
                                </div>
                            </div>`;
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
            modal.find('.autonumeric').autoNumeric('init', {
                aSep: '.',
                aDec: ',',
                mDec: '0', // Tidak ada desimal
                vMax: '999999999999'
            });
        }
    });
</script>
