<script type="text/javascript">
    var tabel = null;
    $(document).ready(function() {
        $('.accordion-body .chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('.accordion-body')
        });

        tabel = $('#datatable-main').DataTable({
            "language": {
                url: _base_url + 'dist/libs/DataTables/id.json',
            },
            "autoWidth": false,
            "processing": true,
            "responsive": true,
            "serverSide": true,
            "ordering": true,
            "order": [
                [0, 'asc']
            ],
            "ajax": {
                "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
                "type": "POST"
            },
            "createdRow": function(row, data, dataIndex) {
                if (data.active_st == 0) {
                    $(row).addClass('bg-pink');
                }
            },
            "bFilter": false,
            "deferRender": true,
            "aLengthMenu": _datatableLengthMenu,
            "pageLength": 10,
            "columns": [{
                    "data": "lokasi_id",
                    "sortable": false,
                    "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    "data": "lokasi_id",
                    "className": "text-left",
                    "render": function(data, type, row, meta) {
                        var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
                        var uri_delete = '<?= $uri . '/delete/' ?>' + data;
                        return `<div class="btn-list btn-sm flex-nowrap"><div class="dropdown"><button class="btn btn-outline-primary btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">Aksi</button><div class="dropdown-menu"><a class="dropdown-item p-1" href="javascript:void(0)" onclick="_modal(event, {uri: '${uri_edit}', size: 'modal-md', position: 'normal'})"><i class="fas fa-pencil-alt text-warning me-2"></i> Ubah Data</a><a class="dropdown-item p-1" href="javascript:void(0)" onclick=_delete("${uri_delete}")><i class="fas fa-trash text-danger me-2"></i> Hapus Data</a></div></div></div>`;
                    }
                },
                {
                    "data": "lokasi_id",
                    "className": "text-left"
                },
                {
                    "data": "parent_lokasi_id",
                    "className": "text-left"
                },
                {
                    "data": "lokasi_nm",
                    "className": "text-left"
                },
                {
                    "data": "tipe_lokasi",
                    "className": "text-left"
                },
                {
                    "data": "deskripsi",
                    "className": "text-left",
                    "render": data => ifNull(data)
                },
                {
                    "data": "active_st",
                    "className": "text-center",
                    "render": (data, type, row) => row['active_st'] == 1 ?
                        '<i class="fas fa-check-circle text-success"></i>' :
                        '<i class="fas fa-times-circle text-danger"></i>'
                }
            ],
        });

    });
    $('body').on('change', '#denah_url', function(event) {
        var modalBody = $(this).closest('.modal-body');
        var denahPreview = modalBody.find('#denah-preview');
        var denahLink = modalBody.find('#denah-link');

        if (event.target.files && event.target.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                var imageSrc = e.target.result;
                denahPreview.attr('src', imageSrc);
                denahLink.attr('href', imageSrc);
            };

            reader.readAsDataURL(event.target.files[0]);
        }
    });

    $(document).on('shown.bs.modal', '#my-modal-1', function(e) {
        var modalContent = $(this).find('.modal-body');
        
        modalContent.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#my-modal-1')
        });
        
        var tipeLokasiSelect = modalContent.find('#tipe_lokasi');
        if (tipeLokasiSelect.length) {
            var divParentLokasi = modalContent.find('#div_parent_lokasi');
            var parentLokasiSelect = modalContent.find('#parent_lokasi_id');
            var allOptions = parentLokasiSelect.find('option').clone();
            const toggleParentDropdown = (selectedTipe) => {
                if (selectedTipe === 'Lantai' || selectedTipe === 'Ruangan') {
                    parentLokasiSelect.empty().append(allOptions.filter('[value=""]'));
                    if (selectedTipe === 'Lantai') parentLokasiSelect.append(allOptions.filter((i, el) => $(
                        el).text().includes('(Gedung)')));
                    else if (selectedTipe === 'Ruangan') parentLokasiSelect.append(allOptions.filter((i,
                        el) => $(el).text().includes('(Lantai)')));
                    divParentLokasi.show();
                    parentLokasiSelect.prop('required', true);
                } else {
                    divParentLokasi.hide();
                    parentLokasiSelect.prop('required', false).val('').trigger('change');
                }
            };
            tipeLokasiSelect.off('change.toggleParent').on('change.toggleParent', function() {
                toggleParentDropdown($(this).val());
            });
            toggleParentDropdown(tipeLokasiSelect.val());
        }

        
    });


</script>
