<div class="modal-body">
    <div class="row">
        <div class="col-md-12 text-center">
            <p class="text-muted">Arahkan kamera ke barcode pada aset yang akan dikerjakan.</p>
            <video id="scanner-preview"
                style="width: 100%; max-width: 400px; border: 1px solid #ccc; border-radius: 5px;"></video>
            <div id="scan-loading" style="display: none;">
                <div class="spinner-border"></div>
                <p>Memvalidasi Aset...</p>
            </div>
        </div>
    </div>
    <hr>
    <div class="row align-items-end">
        <div class="col">
            <label for="no_seri_manual" class="form-label">Tidak bisa scan? Masukkan No. Seri manual:</label>
            <input type="text" class="form-control" id="no_seri_manual" placeholder="Ketik No. Seri Aset">
        </div>
        <div class="col-auto">
            <button class="btn btn-dark" id="btn-cek-manual">Cek Manual</button>
        </div>
    </div>
</div>

<script>
    (function() {
        // -------------------------------------------------------------------
        // PERUBAHAN 1: Ambil Order Kerja ID dari controller
        // -------------------------------------------------------------------
        const orderKerjaId = '{{ $order_kerja_id }}';
        const navParam = '{{ $n_param }}';

        // Inisialisasi variabel dan elemen
        let scanner = null;
        const loadingElement = $('#scan-loading');
        const manualCheckButton = $('#btn-cek-manual');
        const manualInput = $('#no_seri_manual');
        let activeCameras = [];

        /**
         * Fungsi untuk memvalidasi barcode/nomor seri aset.
         * Jika valid, akan membuka modal log kerja.
         */
        function validateBarcode(noSeri) {
            if (!noSeri) {
                _toast('warning', 'Nomor Seri tidak boleh kosong.');
                return;
            }

            loadingElement.show();
            if (scanner) scanner.stop();

            // Panggilan AJAX untuk memverifikasi aset ke server
            $.get('{{ url('master/asset') }}', {
                action: 'find_by_barcode',
                no_seri: noSeri,
                n: navParam
            }, function(response) {

                // Cek jika aset ditemukan dan valid
                if (response.code === '00' && response.data.asset) {

                    // -------------------------------------------------------------------
                    // PERUBAHAN 2: Alur baru setelah scan berhasil
                    // -------------------------------------------------------------------

                    _toast("success", "Aset terverifikasi! Membuka laporan kerja...");

                    // 1. Tutup modal scan saat ini (modal ke-2)
                    _modalHide(2);

                    // 2. Buat URI untuk memanggil modal log kerja teknisi
                    const logKerjaUri =
                        `{{ url('ipsrs/teknisitugas/form_log_kerja_modal') }}/${orderKerjaId}`;

                    // 3. Buka modal log kerja setelah jeda singkat
                    setTimeout(() => {
                        const fakeEvent = {
                            target: document.createElement('button')
                        };
                        _modal(fakeEvent, {
                            uri: logKerjaUri,
                            size: 'modal-xl', // Ukuran modal bisa disesuaikan
                            title: `Buat Laporan Kerja (OK: ${orderKerjaId})`
                        });
                    }, 250);

                } else {
                    // Jika aset tidak ditemukan, tampilkan error dan aktifkan kembali scanner
                    _toast("error", response.message ||
                        "Aset tidak ditemukan atau tidak cocok dengan data.");
                    loadingElement.hide();
                    startScanner();
                }
            }, 'json').fail(function() {
                _toast("error", "Gagal menghubungi server untuk validasi aset.");
                loadingElement.hide();
                startScanner();
            });
        }

        /**
         * Fungsi untuk memulai kamera scanner
         */
        function startScanner() {
            if (scanner && activeCameras.length > 0) {
                // Prioritaskan kamera belakang (jika ada)
                let selectedCam = activeCameras.find(c => c.name.toLowerCase().indexOf('back') !== -1) ||
                    activeCameras[0];
                scanner.start(selectedCam);
            }
        }

        // Inisialisasi library Instascan
        if (typeof Instascan !== 'undefined') {
            scanner = new Instascan.Scanner({
                video: document.getElementById('scanner-preview'),
                scanPeriod: 5,
                mirror: false
            });
            scanner.addListener('scan', content => validateBarcode(content));
            Instascan.Camera.getCameras().then(cameras => {
                if (cameras.length > 0) {
                    activeCameras = cameras;
                    startScanner();
                } else {
                    console.error('Tidak ada kamera yang ditemukan.');
                    _toast('error', 'Tidak ada kamera yang terdeteksi di perangkat ini.');
                }
            }).catch(e => {
                console.error(e);
                _toast('error', 'Tidak dapat mengakses kamera. Pastikan Anda memberikan izin.');
            });
        } else {
            console.error('Library Instascan tidak ditemukan.');
        }

        // Event listener untuk tombol cek manual
        manualCheckButton.on('click', () => validateBarcode(manualInput.val()));

        // Hentikan scanner ketika modal ditutup untuk menghemat resource
        $('#my-modal-2, #my-modal-1').on('hidden.bs.modal', () => {
            if (scanner) {
                scanner.stop();
            }
        });

    })();
</script>
