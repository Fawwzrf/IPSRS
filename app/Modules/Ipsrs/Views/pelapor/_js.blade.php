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
        const ctx = canvas.getContext('2d');
        const placeholder = document.getElementById('canvas-placeholder');
        const anotasiUrlInput = document.getElementById('anotasi_url');
        let backgroundImage = new Image();

        let pinImage = new Image();
        // Beritahu browser untuk memuat gambar pin dengan aman
        pinImage.crossOrigin = "Anonymous";
        pinImage.src = 'https://img.icons8.com/plasticine/100/000000/marker.png';
        let pinPosition = null;
        // ======================================================

        // Fungsi untuk menggambar ulang kanvas
        function drawCanvas() {
            if (!backgroundImage.src || !backgroundImage.complete) return;
            canvas.width = backgroundImage.width;
            canvas.height = backgroundImage.height;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(backgroundImage, 0, 0);

            // Gambar pin jika posisinya sudah ada
            if (pinPosition) {
                ctx.drawImage(pinImage, pinPosition.x - 15, pinPosition.y - 30, 30, 30); // Ukuran pin diperkecil
            }

            // Simpan gambar baru ke input tersembunyi
            anotasiUrlInput.value = canvas.toDataURL('image/jpeg', 0.8);
        }

        // Fungsi untuk memuat gambar denah
        function loadDenah(denahData) {
            denahContainer.show();
            if (denahData && denahData.startsWith('data:image')) {
                backgroundImage.src = denahData;
                backgroundImage.onload = () => {
                    placeholder.style.display = 'none';
                    canvas.style.display = 'block';
                    canvas.width = backgroundImage.width;
                    canvas.height = backgroundImage.height;
                    ctx.drawImage(backgroundImage, 0, 0);
                };
            } else {
                placeholder.style.display = 'block';
                canvas.style.display = 'none';
            }
        }

        // Fungsi untuk memperbarui dropdown Aset
        function updateAssetDropdown(lokasiId) {
            assetSelect.empty();
            if (!lokasiId) {
                assetSelect.append('<option value="">- Pilih Lokasi Terlebih Dahulu -</option>').prop('disabled',
                    true);
            } else {
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
        lokasiSelect.on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const lokasiId = selectedOption.val();
            const denahUrl = selectedOption.data('denah-url');

            updateAssetDropdown(lokasiId);
            loadDenah(denahUrl);
        });
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

    })();
</script>
