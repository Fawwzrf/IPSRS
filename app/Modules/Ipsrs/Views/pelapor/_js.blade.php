<script>
    (function() {
        // Modal & Select2 Initialization
        const modal = $('#form-pelapor-komplain').closest('.modal');
        if (!modal.length) return;

        modal.find('.chosen-select').select2({
            theme: "bootstrap-5",
            dropdownParent: modal
        });

        // DOM Elements
        const lokasiSelect = modal.find('#lokasi-select');
        const assetSelect = modal.find('#asset-select');
        const denahContainer = modal.find('#denah-container');
        const canvas = document.getElementById('denah-canvas');
        const placeholder = document.getElementById('canvas-placeholder');
        const anotasiUrlInput = document.getElementById('anotasi_url');

        // Canvas & Images
        let ctx = canvas ? canvas.getContext('2d') : null;
        let backgroundImage = new Image();
        let pinImage = new Image();
        pinImage.crossOrigin = "Anonymous";
        pinImage.src = 'https://img.icons8.com/plasticine/100/000000/marker.png';
        let pinPosition = null;

        // Draw Canvas
        function drawCanvas() {
            if (!canvas || !ctx || !backgroundImage.src || !backgroundImage.complete) return;
            canvas.width = backgroundImage.width;
            canvas.height = backgroundImage.height;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(backgroundImage, 0, 0);

            if (pinPosition) {
                ctx.drawImage(pinImage, pinPosition.x - 15, pinPosition.y - 30, 30, 30);
            }

            if (anotasiUrlInput) {
                anotasiUrlInput.value = canvas.toDataURL('image/jpeg', 0.8);
            }
        }

        // Load Denah Image
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

        // Update Asset Dropdown
        function updateAssetDropdown(lokasiId) {
            if (!assetSelect) return;
            assetSelect.empty();

            if (!lokasiId) {
                assetSelect.append('<option value="">- Pilih Lokasi Terlebih Dahulu -</option>').prop('disabled',
                    true);
            } else {
                if (!window.allAssets) {
                    console.error('Data aset tidak tersedia');
                    return;
                }
                const filteredAssets = window.allAssets.filter(asset => asset.lokasi_id == lokasiId);
                assetSelect.append('<option value="">- Pilih Aset -</option>').prop('disabled', false);

                if (filteredAssets.length > 0) {
                    filteredAssets.forEach(asset => {
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

        // Lokasi Select Change Event
        if (lokasiSelect) {
            lokasiSelect.on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const lokasiId = selectedOption.val();
                const denahUrl = selectedOption.data('denah-url');
                updateAssetDropdown(lokasiId);
                loadDenah(denahUrl);
            });
        }

        // Canvas Click Event
        if (canvas) {
            canvas.addEventListener('click', function(event) {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                pinPosition = {
                    x: (event.clientX - rect.left) * scaleX,
                    y: (event.clientY - rect.top) * scaleY
                };
                drawCanvas();
            });
        }

        // Save Function
        window._save = function(e) {
            e.preventDefault();
            const submitButton = $(e.target).closest('button');
            submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Proses...');

            const form = $('#form-pelapor-komplain');
            let url = form.attr('action');
            const currentToken = getParameterByName('n', window.location.href);
            if (currentToken) url = appendTokenToUrl(url, currentToken);

            $.ajax({
                url: url,
                method: 'POST',
                data: new FormData(form[0]),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        _toast('success', 'Laporan berhasil dikirim!');
                        const modal = $('#form-komplain-modal');
                        if (modal.length > 0) {
                            modal.modal('hide');
                            setTimeout(function() {
                                if ($('#table-komplain').length > 0) {
                                    loadTableData();
                                } else {
                                    window.location.reload();
                                }
                            }, 500);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        _toast('error', response.message || 'Gagal menyimpan laporan');
                        submitButton.prop('disabled', false).html(
                            '<i class="fas fa-paper-plane me-2"></i> Kirim Laporan');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error:', xhr.status, xhr.responseText);
                    _toast('error', 'Terjadi kesalahan pada server');
                    submitButton.prop('disabled', false).html(
                        '<i class="fas fa-paper-plane me-2"></i> Kirim Laporan');
                }
            });
        };

        // Load Table Data
        function loadTableData() {
            const tableContainer = $('#table-komplain').closest('.table-container');
            tableContainer.html(
                '<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</div>'
                );

            let url = 'ipsrs/pelapor/get_table_data';
            const currentToken = getParameterByName('n', window.location.href);
            if (currentToken) url = appendTokenToUrl(url, currentToken);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(html) {
                    tableContainer.html(html);
                    if (typeof initDatatable === 'function') initDatatable();
                },
                error: function() {
                    tableContainer.html('<div class="alert alert-danger">Gagal memuat data</div>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            });
        }

        // Helper: Get Parameter By Name
        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        // Helper: Append Token To URL
        function appendTokenToUrl(url, token) {
            const separator = url.includes('?') ? '&' : '?';
            return `${url}${separator}n=${token}`;
        }

        function changePage(page) {
            let url = 'ipsrs/pelapor/get_table_data?page=' + page;
            const currentToken = getParameterByName('n', window.location.href);
            if (currentToken) url += '&n=' + currentToken;

            const tableContainer = $('#table-komplain').closest('.table-container');
            tableContainer.html(
                '<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</div>'
                );

            $.ajax({
                url: url,
                method: 'GET',
                success: function(html) {
                    tableContainer.html(html);
                },
                error: function() {
                    tableContainer.html('<div class="alert alert-danger">Gagal memuat data</div>');
                }
            });
        }

        // Helper: Get Parameter By Name (reuse from above)
        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }
    })();
</script>
