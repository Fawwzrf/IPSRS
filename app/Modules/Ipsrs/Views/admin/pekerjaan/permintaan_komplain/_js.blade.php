<script type="text/javascript">
    var tabel = null;
    
    // Blok ini dieksekusi saat halaman utama dimuat
    $(document).ready(function() {
        // --- Inisialisasi Plugin untuk Form Filter ---
        $('#filter-body .chosen-select').select2({ 
            theme: "bootstrap-5", 
            dropdownParent: $('#filter-body') 
        });

        // --- Inisialisasi Tabel Utama (DataTables) ---
        tabel = $('#datatable-main').DataTable({
            "language": { 
                url: _base_url + 'dist/libs/DataTables/id.json'
            },
            "processing": true,
            "serverSide": true,
            "ordering": true,
            "order": [ [3, 'desc'] ], // Default sort: Tgl Komplain terbaru
            "ajax": {
                "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
                "type": "POST"
            },
            "deferRender": true,
            "aLengthMenu": _datatableLengthMenu,
            "pageLength": 10,
            "bFilter": false,
            "columns": [
                { "data": null, "orderable": false, "className": "text-center", "render": function (data, type, row, meta) { 
                    return meta.row + meta.settings._iDisplayStart + 1; 
                }},
                { "data": "permintaan_id", "orderable": false, "className": "text-center", "render": function (data) {
                    var uri_edit = '<?= $uri . '/form_modal/' ?>' + data;
                    var uri_delete = '<?= $uri . '/delete/' ?>' + data;
                    
                    return '' +
                        '<div class="btn-list btn-sm flex-nowrap">' +
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
                }},
                { "data": "permintaan_id" },
                { "data": "tgl", "render": function(data) { return data ? toDate(data) : '-'; }},
                { "data": "asset_nm" },
                { "data": "pegawai_nm" },
                { "data": "deskripsi", "render": function(data) { 
                    return data && data.length > 50 ? `<span title="${data}">${data.substr(0, 50)}...</span>` : data; 
                }},
                { "data": "status", "className": "text-center", "render": function(data) {
                    if (!data) {
                        return '<span class="badge bg-secondary">-</span>';
                    }
                    
                    var badgeClass = { 
                        'baru': 'bg-info', 
                        'diproses': 'bg-warning', 
                        'selesai': 'bg-success' 
                    };
                    
                    return '<span class="badge ' + (badgeClass[data] || 'bg-secondary') + '">' + 
                           data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                }},
                { "data": "anotasi_url", "orderable": false, "className": "text-center", "render": function(data) {
                    return data ? '<i class="fas fa-map-marked-alt text-success" title="Ada denah"></i>' : 
                                  '<i class="fas fa-map text-muted" title="Tidak ada denah"></i>';
                }},
                { "data": "active_st", "className": "text-center", "render": function(data) {
                    return data == 1 ? '<i class="fas fa-check-circle text-success"></i>' : 
                                      '<i class="fas fa-times-circle text-danger"></i>';
                }}
            ],
            "createdRow": function(row, data, dataIndex) {
                if (data.active_st == 0) {
                    $(row).addClass('bg-pink');
                }
            }
        });
    });

    // Blok ini dieksekusi SETIAP KALI MODAL DITAMPILKAN
    $(document).on('shown.bs.modal', '#my-modal-1', function (e) {
        var modal = $(this);
        // Inisialisasi plugin dasar di dalam modal
        modal.find('.chosen-select').select2({ theme: "bootstrap-5", dropdownParent: modal });
        modal.find('.datepicker-notauto').daterangepicker({ 
            singleDatePicker: true, 
            showDropdowns: true, 
            locale: { format: 'DD-MM-YYYY' }
        });
        
        const lokasiSelect = modal.find('#lokasi-select');
        if (!lokasiSelect.length) return;

        // --- Variabel untuk fitur dinamis ---
        const assetSelect = modal.find('#asset-select');
        const canvas = document.getElementById('denah-canvas');
        const ctx = canvas.getContext('2d');
        const placeholder = document.getElementById('canvas-placeholder');
        const anotasiUrlInput = document.getElementById('anotasi_url');
        const btnHapusAnotasi = document.getElementById('btn-hapus-anotasi');

        let backgroundImage = new Image();
        let pinImage = new Image();
        pinImage.crossOrigin = "Anonymous";
        pinImage.src = 'https://img.icons8.com/plasticine/100/000000/marker.png';
        let pinPosition = null;

        // --- Fungsi Helper ---

        function drawCanvas() {
            if (!backgroundImage.src || !backgroundImage.complete) return;
            canvas.width = backgroundImage.width;
            canvas.height = backgroundImage.height;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(backgroundImage, 0, 0);
            if (pinPosition) {
                ctx.drawImage(pinImage, pinPosition.x - 15, pinPosition.y - 30, 30, 30);
            }
            anotasiUrlInput.value = canvas.toDataURL('image/jpeg', 0.8);
        }

        window.drawCanvasForSave = function() {
            const canvas = document.getElementById('denah-canvas');
            if (!canvas || !canvas.style.display || canvas.style.display === 'none') {
                // Jika canvas tidak ada atau tidak terlihat, jangan lakukan apa-apa
                return;
            }
            const anotasiUrlInput = document.getElementById('anotasi_url');
            anotasiUrlInput.value = canvas.toDataURL('image/jpeg', 0.8);
        };

        function loadDenah(denahData, callback) {
            if (denahData && denahData.startsWith('data:image')) {
                backgroundImage.src = denahData;
                backgroundImage.onload = () => {
                    placeholder.style.display = 'none';
                    canvas.style.display = 'block';
                    drawCanvas();
                    if (callback) callback(); // Jalankan callback setelah gambar dimuat
                };
                backgroundImage.onerror = () => {
                    placeholder.textContent = 'Gagal memuat data denah.';
                    placeholder.style.display = 'block';
                    canvas.style.display = 'none';
                };
            } else {
                placeholder.textContent = 'Denah untuk lokasi ini tidak tersedia.';
                placeholder.style.display = 'block';
                canvas.style.display = 'none';
                backgroundImage.src = '';
                anotasiUrlInput.value = '';
            }
        }

        function updateAssetDropdown(lokasiId, selectedAssetId) {
            assetSelect.empty();
            if (!lokasiId) {
                assetSelect.append('<option value="">- Pilih Lokasi Terlebih Dahulu -</option>').prop('disabled', true);
            } else {
                const filteredAssets = window.allAssets.filter(asset => asset.lokasi_id == lokasiId);
                assetSelect.append('<option value="">- Pilih Aset -</option>').prop('disabled', false);
                if (filteredAssets.length > 0) {
                    filteredAssets.forEach(function(asset) {
                        var option = $('<option>', {
                            value: asset.asset_id,
                            text: `${asset.asset_nm} (${asset.no_seri || asset.asset_id})`
                        });
                        if(asset.asset_id == selectedAssetId){
                            option.prop('selected', true);
                        }
                        assetSelect.append(option);
                    });
                } else {
                    assetSelect.append('<option value="">Tidak ada aset di lokasi ini</option>');
                }
            }
            assetSelect.trigger('change.select2');
        }

        // --- Event Handler Utama ---

        lokasiSelect.on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const lokasiId = selectedOption.val();
            const denahUrl = selectedOption.data('denah-url');
            
            // Perbarui dropdown aset tanpa memilih ulang aset (biarkan kosong)
            updateAssetDropdown(lokasiId, null); 
            // Muat denah default dari lokasi
            loadDenah(denahUrl);
        });

        // Fungsi khusus untuk menginisialisasi modal saat mode EDIT
        function initializeEditMode() {
            const initialLokasiId = lokasiSelect.val();
            if (initialLokasiId) {
                // 1. Baca dan simpan nilai anotasi asli SEBELUM melakukan apapun
                const originalAnnotation = anotasiUrlInput.value;

                // 2. Ambil ID aset yang seharusnya terpilih
                const selectedAssetId = window.selectedAsset; 

                // 3. Perbarui dropdown aset dengan ID aset yang benar
                updateAssetDropdown(initialLokasiId, selectedAssetId);

                // 4. Ambil denah default dari lokasi
                const denahUrl = lokasiSelect.find('option:selected').data('denah-url');
                
                // 5. Tentukan gambar mana yang akan dimuat
                //    Prioritaskan anotasi yang sudah ada. Jika tidak ada, baru gunakan denah default.
                const finalDenahData = originalAnnotation || denahUrl;
                
                loadDenah(finalDenahData);
            }
        }

        initializeEditMode();

        // Event listener untuk Canvas
        canvas.addEventListener('click', (event) => {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            pinPosition = { x: (event.clientX - rect.left) * scaleX, y: (event.clientY - rect.top) * scaleY };
            btnHapusAnotasi.style.display = 'inline-block';
            drawCanvas();
        });

        btnHapusAnotasi.addEventListener('click', () => {
            pinPosition = null;
            drawCanvas();
        });
    });
</script>