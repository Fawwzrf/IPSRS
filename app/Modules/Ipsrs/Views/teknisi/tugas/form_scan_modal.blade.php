<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Scan barcode aset menggunakan kamera atau masukkan kode secara manual.
            </div>
        </div>

        <!-- Area Kamera -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Kamera Scanner</h5>
                </div>
                <div class="card-body">
                    <!-- Area video dari kamera -->
                    <div id="scanner-container" class="mb-2">
                        <video id="scanner-video"
                            style="width: 100%; height: 250px; border: 1px solid #ccc; background: #333;"></video>
                    </div>
                    <div class="btn-group w-100">
                        <button type="button" class="btn btn-success btn-sm" id="start-scanner">
                            <i class="fas fa-play"></i> Mulai Kamera
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="stop-scanner" disabled>
                            <i class="fas fa-stop"></i> Hentikan Kamera
                        </button>
                        <button type="button" class="btn btn-info btn-sm" id="switch-camera">
                            <i class="fas fa-sync"></i> Ganti Kamera
                        </button>
                    </div>
                    <div id="camera-status" class="small text-muted mt-2"></div>
                </div>
            </div>
        </div>

        <!-- Form Input Manual -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Input Manual</h5>
                </div>
                <div class="card-body">
                    <form id="form-scan-barcode"
                        action="{{ url('ipsrs/teknisitugas/verify_barcode') }}{{ isset($n_param) ? '?n=' . $n_param : '' }}"
                        method="post">
                        @csrf
                        <input type="hidden" name="order_kerja_id" value="{{ $order_kerja_id }}">

                        <div class="mb-3">
                            <label class="form-label required">Kode Barcode</label>
                            <input type="text" class="form-control" name="barcode" id="barcode-input" required
                                placeholder="Scan atau ketik barcode aset...">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Verifikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-3">
        <strong>Kamera tampil hitam? Coba hal berikut:</strong>
        <ul class="mb-0 mt-2">
            <li>Pastikan tidak ada aplikasi lain yang menggunakan kamera</li>
            <li>Periksa apakah webcam berfungsi di aplikasi lain</li>
            <li>Pastikan pencahayaan ruangan cukup terang</li>
            <li>Restart browser atau gunakan browser berbeda (Chrome/Firefox)</li>
            <li>Periksa izin kamera di pengaturan browser</li>
        </ul>
    </div>

    <hr>
    <div class="row">
        <div class="col-12 text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>
</div>

<!-- Script untuk kamera dan barcode scanner menggunakan Instascan -->
<script>
    // Hack untuk menghindari error babel-polyfill
    window._babelPolyfill = false;
</script>
<script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
    // Script ini akan dijalankan ketika konten modal dimuat
    $(function() {
        console.log('Scan modal loaded with Instascan');

        // Variabel untuk menyimpan scanner dan kamera
        let scanner = null;
        let currentCameraIndex = 0;
        let cameras = [];
        let scannerActive = false;

        // Fungsi untuk memulai scanner
        function startScanner() {
            $('#camera-status').text('Memulai kamera...');

            // Pastikan instascan sudah dimuat
            if (typeof Instascan === 'undefined') {
                $('#camera-status').text('Error: Instascan tidak tersedia.');
                return;
            }

            // Cek apakah scanner sudah ada
            if (scannerActive) {
                $('#camera-status').text('Scanner sudah aktif!');
                return;
            }

            // Nonaktifkan tombol start
            $('#start-scanner').prop('disabled', true);

            // Inisialisasi scanner
            scanner = new Instascan.Scanner({
                video: document.getElementById('scanner-video'),
                scanPeriod: 5, // Scan setiap 5ms
                mirror: false // Jangan mirror (untuk kamera belakang)
            });

            // Handler ketika kode terdeteksi
            scanner.addListener('scan', function(content) {
                console.log('Barcode terdeteksi:', content);

                // Isi input dengan hasil scan
                $('#barcode-input').val(content);

                // Beri feedback
                $('#camera-status').html('<div class="alert alert-success">Barcode terdeteksi: ' +
                    content + '</div>');

                // Auto submit form setelah scan berhasil
                Swal.fire({
                    title: 'Barcode terdeteksi',
                    text: content + '\nVerifikasi sekarang?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Verifikasi',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#form-scan-barcode').submit();
                    }
                });
            });

            // Dapatkan daftar kamera
            Instascan.Camera.getCameras().then(function(availableCameras) {
                cameras = availableCameras;

                if (cameras.length === 0) {
                    $('#camera-status').text('Tidak ada kamera yang tersedia');
                    $('#start-scanner').prop('disabled', false);
                    return;
                }

                // Log info kamera
                console.log('Kamera tersedia:', cameras.length);
                cameras.forEach((camera, i) => {
                    console.log(`Kamera ${i}: ${camera.name || 'Tanpa Nama'}`);
                });

                // Secara default gunakan kamera belakang jika ada
                let selectedCamera = cameras[0]; // Default ke kamera pertama

                // Cari kamera belakang berdasarkan nama (biasanya mengandung "back" atau tidak mengandung "front")
                for (let i = 0; i < cameras.length; i++) {
                    const cameraName = (cameras[i].name || '').toLowerCase();
                    if (cameraName.includes('back') || (!cameraName.includes('front') && cameras
                            .length > 1)) {
                        selectedCamera = cameras[i];
                        currentCameraIndex = i;
                        break;
                    }
                }

                // Mulai scanner dengan kamera yang dipilih
                scanner.start(selectedCamera).then(function() {
                    console.log('Scanner dimulai dengan kamera:', selectedCamera.name ||
                        'Kamera ' + currentCameraIndex);
                    $('#camera-status').text('Kamera aktif: ' + (selectedCamera.name ||
                        'Kamera ' + currentCameraIndex));
                    $('#stop-scanner').prop('disabled', false);
                    scannerActive = true;
                }).catch(function(e) {
                    console.error('Error memulai scanner:', e);
                    $('#camera-status').text('Error memulai scanner: ' + e.toString());
                    $('#start-scanner').prop('disabled', false);
                });
            }).catch(function(e) {
                console.error('Error mendapatkan daftar kamera:', e);
                $('#camera-status').text('Error: ' + e.toString());
                $('#start-scanner').prop('disabled', false);
            });
        }

        // Fungsi untuk menghentikan scanner
        function stopScanner() {
            if (scanner) {
                scanner.stop();
                scannerActive = false;
                $('#camera-status').text('Scanner dihentikan');
                $('#start-scanner').prop('disabled', false);
                $('#stop-scanner').prop('disabled', true);
            }
        }

        // Fungsi untuk berganti kamera
        function switchCamera() {
            if (!scanner || cameras.length <= 1) {
                $('#camera-status').text('Tidak ada kamera lain yang tersedia');
                return;
            }

            // Hentikan scanner dulu
            scanner.stop();

            // Pilih kamera berikutnya
            currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
            let nextCamera = cameras[currentCameraIndex];

            // Mulai scanner dengan kamera baru
            scanner.start(nextCamera).then(function() {
                console.log('Beralih ke kamera:', nextCamera.name || 'Kamera ' + currentCameraIndex);
                $('#camera-status').text('Kamera aktif: ' + (nextCamera.name || 'Kamera ' +
                    currentCameraIndex));
            });
        }

        // Bind tombol-tombol
        $('#start-scanner').on('click', startScanner);
        $('#stop-scanner').on('click', stopScanner);
        $('#switch-camera').on('click', switchCamera);

        // Perbarui handler submit
        $('#form-scan-barcode').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html(
                '<i class="fas fa-spinner fa-spin"></i> Verifikasi...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status === true || res.code === '01' || res.code === '02') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Barcode terverifikasi!'
                        }).then(() => {
                            // Hentikan scanner jika aktif
                            if (typeof scanner !== 'undefined' && scanner) {
                                scanner.stop();
                            }
                            // Tutup modal scan
                            $('#scan-modal').modal('hide');
                            // Redirect ke URL yang diberikan server setelah modal tertutup
                            setTimeout(function() {
                                window.location.href = res.redirect_url;
                            }, 500);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Barcode tidak valid!'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memverifikasi barcode.'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
