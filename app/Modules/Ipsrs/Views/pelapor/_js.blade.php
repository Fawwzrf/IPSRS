<script>
    (function() {
        var modal = $('#form-pelapor-komplain').closest('.modal');
        if (!modal.length) return;

        // Inisialisasi plugin dasar
        modal.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: modal
        });

        // Variabel untuk fitur dinamis
        const lokasiSelect = modal.find('#lokasi-select');
        const assetSelect = modal.find('#asset-select');
        const denahContainer = modal.find('#denah-container');
        const canvas = document.getElementById('denah-canvas');
        
        // Pastikan canvas ada sebelum mengakses context
        let ctx;
        if (canvas) {
            ctx = canvas.getContext('2d');
        }
        
        const placeholder = document.getElementById('canvas-placeholder');
        const anotasiUrlInput = document.getElementById('anotasi_url');
        let backgroundImage = new Image();

        let pinImage = new Image();
        // Beritahu browser untuk memuat gambar pin dengan aman
        pinImage.crossOrigin = "Anonymous";
        pinImage.src = 'https://img.icons8.com/plasticine/100/000000/marker.png';
        let pinPosition = null;

        // Fungsi untuk menggambar ulang kanvas
        function drawCanvas() {
            if (!canvas || !ctx || !backgroundImage.src || !backgroundImage.complete) return;
            
            canvas.width = backgroundImage.width;
            canvas.height = backgroundImage.height;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(backgroundImage, 0, 0);

            // Gambar pin jika posisinya sudah ada
            if (pinPosition) {
                ctx.drawImage(pinImage, pinPosition.x - 15, pinPosition.y - 30, 30, 30);
            }

            // Simpan gambar baru ke input tersembunyi
            if (anotasiUrlInput) {
                anotasiUrlInput.value = canvas.toDataURL('image/jpeg', 0.8);
            }
        }

        // Fungsi untuk memuat gambar denah
        function loadDenah(denahData) {
            if (!denahContainer) return;
            
            denahContainer.show();
            if (denahData && denahData.startsWith('data:image')) {
                backgroundImage.src = denahData;
                backgroundImage.onload = () => {
                    if (placeholder) placeholder.style.display = 'none';
                    if (canvas) {
                        canvas.style.display = 'block';
                        canvas.width = backgroundImage.width;
                        canvas.height = backgroundImage.height;
                        ctx.drawImage(backgroundImage, 0, 0);
                    }
                };
            } else {
                if (placeholder) placeholder.style.display = 'block';
                if (canvas) canvas.style.display = 'none';
            }
        }

        // Fungsi untuk memperbarui dropdown Aset
        function updateAssetDropdown(lokasiId) {
            if (!assetSelect) return;
            
            assetSelect.empty();
            if (!lokasiId) {
                assetSelect.append('<option value="">- Pilih Lokasi Terlebih Dahulu -</option>').prop('disabled', true);
            } else {
                // Pastikan window.allAssets ada
                if (!window.allAssets) {
                    console.error('Data aset tidak tersedia');
                    return;
                }
                
                const filteredAssets = window.allAssets.filter(asset => asset.lokasi_id == lokasiId);
                assetSelect.append('<option value="">- Pilih Aset -</option>').prop('disabled', false);
                if (filteredAssets.length > 0) {
                    filteredAssets.forEach(function(asset) {
                        assetSelect.append($('<option>', {
                            value: asset.asset_id,
                            text: `${asset.asset_nm} (SN: ${asset.no_seri || 'N/A'})`
                        }));
                    });
                } else {
                    assetSelect.append('<option value="">Tidak ada aset di lokasi ini</option>');
                }
            }
            assetSelect.trigger('change.select2');
        }

        // Event handler utama saat lokasi diubah
        if (lokasiSelect) {
            lokasiSelect.on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const lokasiId = selectedOption.val();
                const denahUrl = selectedOption.data('denah-url');

                updateAssetDropdown(lokasiId);
                loadDenah(denahUrl);
            });
        }
        
        // Hanya tambahkan event listener jika canvas ada
        if (canvas) {
            canvas.addEventListener('click', function(event) {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;

                // Simpan posisi klik
                pinPosition = {
                    x: (event.clientX - rect.left) * scaleX,
                    y: (event.clientY - rect.top) * scaleY
                };

                // Gambar ulang kanvas dengan pin di posisi baru
                drawCanvas();
            });
        }

        // Fungsi untuk handling form submission dan mencegah double submit
        window._save = function(e) {
            e.preventDefault();
            
            // Disable tombol submit
            const submitButton = $(e.target).closest('button');
            submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Proses...');
            
            // Ambil form dan URL
            const form = $('#form-pelapor-komplain');
            let url = form.attr('action');
            
            // Tambahkan token 'n' dari URL saat ini
            const currentToken = getParameterByName('n', window.location.href);
            if (currentToken) {
                url = appendTokenToUrl(url, currentToken);
            }
            
            // Submit form via AJAX
            $.ajax({
                url: url,
                method: 'POST',
                data: new FormData(form[0]),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        // Tampilkan pesan sukses
                        _toast('success', 'Laporan berhasil dikirim!');
                        
                        // PENTING: Tutup modal terlebih dahulu
                        const modal = $('#form-komplain-modal');
                        if (modal.length > 0) {
                            modal.modal('hide');
                            
                            // Tunggu sebentar sampai modal tertutup
                            setTimeout(function() {
                                // Jika ada tabel komplain, reload menggunakan AJAX
                                if ($('#table-komplain').length > 0) {
                                    loadTableData();
                                } else {
                                    // Jika tidak ada tabel spesifik, refresh halaman
                                    window.location.reload();
                                }
                            }, 500);
                        } else {
                            // Jika tidak ditemukan modal, reload halaman
                            window.location.reload();
                        }
                    } else {
                        // Tampilkan pesan error
                        _toast('error', response.message || 'Gagal menyimpan laporan');
                        submitButton.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Kirim Laporan');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error:', xhr.status, xhr.responseText);
                    _toast('error', 'Terjadi kesalahan pada server');
                    submitButton.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Kirim Laporan');
                }
            });
        };
        
        // Fungsi untuk reload data tabel
        function loadTableData() {
            const tableContainer = $('#table-komplain').closest('.table-container');
            
            // Tampilkan loading
            tableContainer.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</div>');
            
            // Ambil URL reload tabel dengan token yang sama
            let url = 'ipsrs/pelapor/get_table_data';
            const currentToken = getParameterByName('n', window.location.href);
            if (currentToken) {
                url = appendTokenToUrl(url, currentToken);
            }
            
            // Load tabel dengan AJAX
            $.ajax({
                url: url,
                method: 'GET',
                success: function(html) {
                    tableContainer.html(html);
                    // Inisialisasi ulang plugin jika diperlukan
                    if (typeof initDatatable === 'function') {
                        initDatatable();
                    }
                },
                error: function() {
                    tableContainer.html('<div class="alert alert-danger">Gagal memuat data</div>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            });
        }
        
        // Helper functions
        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }
        
        function appendTokenToUrl(url, token) {
            const separator = url.includes('?') ? '&' : '?';
            return `${url}${separator}n=${token}`;
        }
    })();
</script>
