<script>
    // Fungsi untuk membuka modal scan - letakkan di bagian atas file
    function openScanModal(orderKerjaId, nParam) {
        console.log('openScanModal called with:', orderKerjaId, nParam); // Debug log

        // Buat URL dengan parameter yang benar
        let url = _base_url + 'ipsrs/teknisitugas/form_scan_modal/' + orderKerjaId;
        if (nParam) {
            url += '?n=' + nParam;
        }

        console.log('Modal URL:', url); // Debug log

        // Cara 1: Gunakan AJAX langsung untuk memuat konten modal
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                // Tambahkan modal ke body
                if ($('#scan-modal').length) {
                    $('#scan-modal').remove();
                }

                $('body').append('<div class="modal fade" id="scan-modal" tabindex="-1" role="dialog">' +
                    '<div class="modal-dialog modal-lg" role="document">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header">' +
                    '<h5 class="modal-title">Scan Barcode Aset</h5>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    response +
                    '</div>' +
                    '</div>' +
                    '</div>');

                // Tampilkan modal
                $('#scan-modal').modal('show');
            },
            error: function(xhr) {
                console.error('Error loading modal:', xhr);
                alert('Gagal memuat form scan barcode. Silakan coba lagi.');
            }
        });

    }

    // Wrap initialization code in a function that checks if elements exist first


    // Call the initialization function when document is ready
    $(document).ready(function() {

        // Re-initialize when modals are shown (in case of dynamic content)
        $(document).on('shown.bs.modal', function(e) {
            var modal = $(e.target);
            var repeater = modal.find('#sparepart-repeater');
            // Inisialisasi hanya jika belum pernah dan plugin tersedia
            if (repeater.length > 0 && typeof repeater.data('repeater-init') === 'undefined' &&
                typeof repeater.repeater === 'function') {
                repeater.repeater({
                    initEmpty: false,
                    show: function() {
                        $(this).slideDown();
                        $(this).find('.repeater-select').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: modal
                        });
                    },
                    hide: function(deleteElement) {
                        $(this).slideUp(deleteElement);
                    }
                });
                repeater.data('repeater-init', true); // Mark as initialized
                repeater.find('.repeater-select').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: modal
                });
            }
        });


    });

    // Fungsi helper untuk menangani respon sukses
    function handleSuccess(message, redirectUrl) {
        _modalHide(1); // Tutup semua modal
        _toast("success", message);

        // Ambil parameter 'n' dari URL saat ini
        const urlParams = new URLSearchParams(window.location.search);
        const navParameter = urlParams.get('n');

        // Arahkan halaman ke URL yang benar setelah 1 detik
        setTimeout(() => {
            if (redirectUrl) {
                // Tambahkan kembali parameter 'n' saat redirect
                window.location.href = redirectUrl + (redirectUrl.includes('?') ? '&' : '?') + 'n=' +
                    navParameter;
            } else {
                window.location.reload(); // Fallback jika URL tidak ada
            }
        }, 1000);
    }

    // Fungsi helper untuk menangani error
    function handleFailure(xhr, btn) {
        if (btn) btn.prop('disabled', false);
        const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ?
            xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
        _toast('error', errorMsg);
    }

    // Handler untuk tombol aksi (Terima, Batal Terima, Terima Kembali)
    $(document).on('click', '.btn-task-action', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const url = $btn.data('url');
        const id = $btn.data('id');

        if (!url || !id) {
            _toast('error', 'Data untuk aksi tidak lengkap.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Memproses...');

        // Ambil parameter 'n' dari URL saat ini
        const urlParams = new URLSearchParams(window.location.search);
        const navParameter = urlParams.get('n');

        // PERBAIKAN: Tambahkan parameter 'n' ke data yang dikirim
        const postData = {
            penugasan_id: id,
            _token: $('meta[name="csrf-token"]').attr('content'),
            n: navParameter // Tambahkan parameter navigasi
        };

        $.post(url, postData, function(res) {
            if (res.code === '02') {
                handleSuccess(res.message, res.redirect_url);
            } else {
                _toast('error', res.message || 'Gagal memproses permintaan.');
                $btn.prop('disabled', false).html($btn.data('original-text') || $btn.text());
            }
        }, 'json').fail(xhr => handleFailure(xhr, $btn));
    });

    // Handler untuk form AJAX (Form Tolak Tugas)
    // ...existing code...
    $(document).on('submit', '#form-ajax', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type=submit]');
        $btn.prop('disabled', true);

        // Ambil parameter 'n' dari URL saat ini
        const urlParams = new URLSearchParams(window.location.search);
        const navParameter = urlParams.get('n');

        // Ambil data dari form dan tambahkan parameter 'n'
        let formData = $form.serialize();
        formData += '&n=' + navParameter; // Tambahkan parameter navigasi

        $.post($form.attr('action'), formData, function(res) {
            if (res.code === '02') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                }).then(() => {
                    if (res.redirect_url) {
                        window.location.href = res.redirect_url;
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Gagal memproses permintaan.'
                });
                $btn.prop('disabled', false);
            }
        }, 'json').fail(function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON
                    .message : 'Terjadi kesalahan pada server.'
            });
            $btn.prop('disabled', false);
        });
    });
    // ...existing code...

    // Ganti atau tambahkan handler form ajax untuk "terima tugas"

    // Fungsi untuk batalkan penerimaan tugas

    // Tambahkan fungsi-fungsi utilitas jika belum ada
    function _toast(type, message) {
        // Gunakan toastr jika tersedia
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
            return;
        }

        // Fallback ke alert jika toastr tidak tersedia
        if (type === 'error') {
            alert('Error: ' + message);
        } else {
            alert(message);
        }
    }

    function _modalHide() {
        // Tutup semua modal yang terbuka
        if ($('.modal.show').length) {
            $('.modal.show').modal('hide');
        }
    }


    // Tambahkan fungsi untuk menampilkan modal tolak tugas
    function showTolakModal(penugasanId) {
        // Ambil parameter n dari URL saat ini
        const n_param = new URLSearchParams(window.location.search).get('n');

        // Buat URL modal dengan parameter yang benar
        const url = _base_url + 'ipsrs/teknisitugas/form_tolak_modal/' + penugasanId;

        // Gunakan fungsi _modal dengan URL yang diformat dengan benar
        _modal(null, {
            uri: url + (n_param ? '?n=' + n_param : ''),
            title: 'Form Tolak Tugas',
            size: 'modal-lg',
            buttons: [{
                text: 'Tutup',
                class: 'btn-secondary',
                click: function(modal) {
                    modal.hide();
                }
            }]
        });
    }

    // Tambahkan event handler untuk tombol tolak tugas
    $(document).ready(function() {
        // Log untuk debugging
        console.log("Document ready - binding tolak tugas button");

        // Gunakan delegasi event untuk menangani tombol dalam modal
    });

    // Hapus handler lama untuk .btn-terima-tugas, .btn-ambil-kembali, .btn-tolak-tugas, dan batalTerima
    // Pastikan hanya handler SweetAlert2 yang aktif

    // Handler tombol aksi dengan SweetAlert2
    $(document).off('click', '.btn-terima-tugas, .btn-ambil-kembali').on('click',
        '.btn-terima-tugas, .btn-ambil-kembali',
        function(e) {
            e.preventDefault();
            const btn = $(this);
            const url = btn.data('url');
            const penugasanId = btn.data('penugasan-id');
            const n_param = new URLSearchParams(window.location.search).get('n') || '';

            let actionText = btn.hasClass('btn-terima-tugas') ? 'Terima Tugas ini?' : 'Ambil Kembali Tugas ini?';

            Swal.fire({
                title: 'Konfirmasi',
                text: actionText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: _token,
                            penugasan_id: penugasanId,
                            n: n_param
                        },
                        dataType: 'json',
                        success: function(res) {
                            Swal.fire({
                                icon: res.code === '02' ? 'success' : 'error',
                                title: res.code === '02' ? 'Berhasil' : 'Gagal',
                                text: res.message || (res.code === '02' ?
                                    'Data berhasil diubah' : 'Terjadi kesalahan')
                            }).then(() => {
                                if (res.code === '02' && res.redirect_url) {
                                    window.location.href = res.redirect_url;
                                } else {
                                    btn.prop('disabled', false).html(btn.hasClass(
                                            'btn-terima-tugas') ?
                                        '<i class="fas fa-check"></i> Terima Tugas' :
                                        '<i class="fas fa-undo"></i> Ambil Kembali Tugas'
                                        );
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message ||
                                'Terjadi kesalahan sistem', 'error');
                            btn.prop('disabled', false).html(btn.hasClass('btn-terima-tugas') ?
                                '<i class="fas fa-check"></i> Terima Tugas' :
                                '<i class="fas fa-undo"></i> Ambil Kembali Tugas');
                        }
                    });
                }
            });
        });

    // Handler tombol batalkan penerimaan tugas dengan SweetAlert2
    function batalTerima(penugasanId) {
        const n_param = new URLSearchParams(window.location.search).get('n') || '';
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Batalkan penerimaan tugas ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${_base_url}ipsrs/teknisitugas/batal_terima`,
                    type: 'POST',
                    data: {
                        _token: _token,
                        penugasan_id: penugasanId,
                        n: n_param
                    },
                    dataType: 'json',
                    success: function(res) {
                        Swal.fire({
                            icon: res.code === '02' ? 'success' : 'error',
                            title: res.code === '02' ? 'Berhasil' : 'Gagal',
                            text: res.message
                        }).then(() => {
                            if (res.code === '02' && res.redirect_url) {
                                window.location.href = res.redirect_url;
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan sistem',
                            'error');
                    }
                });
            }
        });
    }

    // Handler tombol tolak tugas dengan SweetAlert2 sebelum tampilkan modal
    $(document).off('click', '.btn-tolak-tugas').on('click', '.btn-tolak-tugas', function(e) {
        e.preventDefault();
        const penugasanId = $(this).data('penugasan-id');
        const n = $(this).data('n') || '';
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Tolak tugas ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buka modal form tolak tugas
                const modalUrl = _base_url + 'ipsrs/teknisitugas/form_tolak_modal/' + penugasanId +
                    '?n=' + n + '&_is_ajax=true';
                $.ajax({
                    url: modalUrl,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': _token
                    },
                    success: function(response) {
                        $('#my-modal-1').modal('hide');
                        if ($('#tolak-modal').length) $('#tolak-modal').remove();
                        $('body').append(
                            '<div class="modal fade" id="tolak-modal" tabindex="-1" role="dialog">' +
                            '<div class="modal-dialog modal-md" role="document">' +
                            '<div class="modal-content">' +
                            '<div class="modal-header">' +
                            '<h5 class="modal-title">Tolak Tugas</h5>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                            '</div>' +
                            response +
                            '</div>' +
                            '</div>' +
                            '</div>');
                        $('#tolak-modal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal memuat form tolak tugas', 'error');
                    }
                });
            }
        });
    });
</script>
