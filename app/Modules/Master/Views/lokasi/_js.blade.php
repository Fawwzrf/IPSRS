<script type="text/javascript">
    if (typeof _loading === 'undefined') {
        window._loading = function(s, m) {
            console.warn("'_loading' undefined");
        };
    }
    if (typeof _modal === 'undefined') {
        window._modal = function() {
            alert("_modal undefined.");
        };
    }
    if (typeof _delete === 'undefined') {
        window._delete = function() {
            alert("_delete undefined.");
        };
    }
    if (typeof _save === 'undefined') {
        window._save = function() {
            alert("_save undefined.");
        };
    }
    if (typeof ifNull === 'undefined') {
        window.ifNull = function(v, f = '') {
            return (v === null || v === undefined) ? f : v;
        };
    }

    var tabel = null;
    $(document).ready(function() {
        $('.accordion-body .chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('.accordion-body')
        });

        $('.accordion-body .datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1900,
            maxYear: parseInt(moment().format('YYYY'), 10) + 5,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });

        tabel = $('#datatable-main').DataTable({
            "language": {
                url: _base_url + 'dist/libs/DataTables/id.json'
            },
            "stateSave": true,
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
                "type": "POST",
                "data": function(d) {
                    d.search_data = <?= json_encode(@$nav_sess['search']['data']) ?: '{}' ?>;
                    d._token = _token;
                }
            },
            "deferRender": true,
            "aLengthMenu": _datatableLengthMenu,
            "pageLength": 10,
            "createdRow": function(row, data, dataIndex) {
                if (data.active_st == 0) $(row).addClass('bg-pink');
            },
            "bFilter": false,
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

        $('#search').on('submit', function(e) {
            e.preventDefault();
            tabel.ajax.reload();
        });

        window._searchReset = function() {
            $('#search')[0].reset();
            $('.chosen-select').val('').trigger('change');
            tabel.ajax.reload();
        };
    });

    function stopTablerEvent(event) {
        // Hentikan event ini sepenuhnya, jangan biarkan listener lain (termasuk Tabler) menjalankannya.
        event.stopImmediatePropagation();
    }

    $(document).on('shown.bs.modal', '#my-modal-1', function(e) {
        var modalContent = $(this).find('.modal-body');
        var gallery = modalContent.find('#denah-gallery')[0];

        const setupViewer = () => {
            if (!gallery) return;

            if (gallery.viewerInstance) {
                gallery.viewerInstance.destroy();
            }

            gallery.viewerInstance = new Viewer(gallery, {
                inline: false,
                toolbar: {
                    zoomIn: 1,
                    zoomOut: 1,
                    oneToOne: 1,
                    reset: 1,
                    rotateLeft: 1,
                    rotateRight: 1,
                    flipHorizontal: 1,
                    flipVertical: 1
                },
            });
            gallery.removeEventListener('mousedown', stopTablerEvent, true);
            
            // Tambahkan listener baru di fase capture (parameter ketiga 'true')
            gallery.addEventListener('mousedown', stopTablerEvent, true);
        };

        setupViewer();

        modalContent.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#my-modal-1')
        });
        modalContent.find('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            }
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

        modalContent.find('#denah_url').off('change.viewer').on('change.viewer', function(event) {
            if (event.target.files && event.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    modalContent.find('#denah-preview').attr('src', e.target.result);
                    setupViewer();
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    });

    $(document).on('hidden.bs.modal', '#my-modal-1', function(e) {
        var gallery = $(this).find('#denah-gallery')[0];
        if (gallery && gallery.viewerInstance) {
            gallery.viewerInstance.destroy();
            gallery.viewerInstance = null;
        }
    });
</script>
