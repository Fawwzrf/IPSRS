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
        
        /* 
        // Cara 2: Jika cara 1 tidak berhasil, coba ini sebagai alternatif
        // Buka di jendela popup kecil (fallback)
        const popupWidth = 600;
        const popupHeight = 400;
        const left = (window.innerWidth - popupWidth) / 2;
        const top = (window.innerHeight - popupHeight) / 2;
        
        window.open(url, 'ScanBarcodeWindow', 
            `width=${popupWidth},height=${popupHeight},top=${top},left=${left},resizable=yes,scrollbars=yes`);
        */
    }

    // Wrap initialization code in a function that checks if elements exist first
    function initializeFormPlugins() {
        const formElement = $('#form-log-kerja');
        if (formElement.length === 0) return; // Exit if the form doesn't exist

        const modalElement = formElement.closest('.modal');

        modalElement.find('.autonumeric').autoNumeric('init', {
            aSep: '.',
            aDec: ',',
            mDec: '0'
        });

        modalElement.find('#sparepart-repeater').repeater({
            initEmpty: true,
            show: function() {
                $(this).slideDown();
                $(this).find('.repeater-select').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: modalElement
                });
            },
            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
            }
        });

        modalElement.find('.repeater-select').select2({
            theme: 'bootstrap-5',
            dropdownParent: modalElement
        });
    }

    // Call the initialization function when document is ready
    $(document).ready(function() {
        initializeFormPlugins();

        // Re-initialize when modals are shown (in case of dynamic content)
        $(document).on('shown.bs.modal', function() {
            initializeFormPlugins();
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
        if (!confirm('Apakah Anda yakin ingin melakukan tindakan ini?')) return;

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
                handleSuccess(res.message, res.redirect_url);
            } else {
                _toast('error', res.message || 'Gagal memproses permintaan.');
                $btn.prop('disabled', false);
            }
        }, 'json').fail(xhr => handleFailure(xhr, $btn));
    });

    // Ganti atau tambahkan handler form ajax untuk "terima tugas"
    $(document).off('click', '.btn-terima-tugas').on('click', '.btn-terima-tugas', function(e) {
        e.preventDefault();
        const btn = $(this);
        const url = btn.data('url');
        const penugasanId = btn.data('penugasan-id');
        const n_param = new URLSearchParams(window.location.search).get('n') || '';

        // Disable button dan tampilkan loading
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
                // Cek apakah response memiliki status atau bisa dianggap sukses
                const isSuccess = res.status === true || res.code === '01' || res.code === '02';

                // Tampilkan toast dengan warna yang sesuai
                _toast(isSuccess ? 'success' : 'error', res.message || (isSuccess ?
                    'Data berhasil diubah' : 'Terjadi kesalahan'));

                // Tutup modal jika sukses
                if (isSuccess) {
                    _modalHide();

                    // Redirect jika ada URL redirect
                    if (res.redirect_url) {
                        window.location.href = res.redirect_url;
                    } else {
                        // Reload halaman jika tidak ada redirect
                        window.location.reload();
                    }
                } else {
                    // Kembalikan tombol ke keadaan semula jika gagal
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Terima Tugas');
                }
            },
            error: function(xhr) {
                // Handle error response
                let errorMessage = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                _toast('error', errorMessage);
                btn.prop('disabled', false).html('<i class="fas fa-check"></i> Terima Tugas');
            }
        });
    });

    // Fungsi untuk batalkan penerimaan tugas
    function batalTerima(penugasanId) {
        const n_param = new URLSearchParams(window.location.search).get('n') || '';

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
                _toast(res.code === '01' || res.code === '02' ? 'success' : 'error', res.message);
                _modalHide();

                if (res.redirect_url) {
                    window.location.href = res.redirect_url;
                } else {
                    window.location.reload();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                _toast('error', errorMessage);
            }
        });
    }

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

    // Tambahkan handler untuk form tolak tugas
    $(document).off('submit', '#form-ajax').on('submit', '#form-ajax', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();

        // Validasi form
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }

        // Disable button dan tampilkan loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                const isSuccess = res.status === true || res.code === '01' || res.code === '02';

                _toast(isSuccess ? 'success' : 'error', res.message || (isSuccess ? 'Berhasil' :
                    'Terjadi kesalahan'));

                if (isSuccess) {
                    _modalHide();

                    if (res.redirect_url) {
                        window.location.href = res.redirect_url;
                    } else {
                        window.location.reload();
                    }
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                _toast('error', errorMessage);
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

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
        $(document).on('click', '.btn-tolak-tugas', function(e) {
            e.preventDefault();
            console.log("Tolak button clicked"); // Debug log

            const penugasanId = $(this).data('penugasan-id');
            const n = $(this).data('n') || '';

            // URL untuk modal form tolak
            const modalUrl = _base_url + 'ipsrs/teknisitugas/form_tolak_modal/' + penugasanId + '?n=' +
                n;

            // Buka modal secara manual jika _modal tidak berfungsi
            $.ajax({
                url: modalUrl + '&_is_ajax=true',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': _token
                },
                success: function(response) {
                    // Tutup modal yang ada jika ada
                    $('#my-modal-1').modal('hide');

                    // Tambahkan modal baru ke body
                    if ($('#tolak-modal').length) {
                        $('#tolak-modal').remove();
                    }

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

                    // Tampilkan modal
                    $('#tolak-modal').modal('show');

                    // Inisialisasi plugin form jika diperlukan
                    initializeFormPlugins();
                },
                error: function(xhr) {
                    console.error("Error loading modal:", xhr);
                    _toast('error', 'Gagal memuat form tolak tugas');
                }
            });
        });
    });

    // Handler untuk submit form tolak tugas
    $(document).on('submit', '#form-ajax', function(e) {
        e.preventDefault();
        console.log("Form tolak submitted"); // Debug log

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();

        // Validasi form
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }

        // Disable button dan tampilkan loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                console.log("Form response:", res); // Debug log

                const isSuccess = res.status === true || res.code === '01' || res.code === '02';

                _toast(isSuccess ? 'success' : 'error', res.message || (isSuccess ? 'Berhasil' :
                    'Terjadi kesalahan'));

                if (isSuccess) {
                    // Tutup semua modal
                    $('.modal').modal('hide');

                    // Redirect atau reload
                    if (res.redirect_url) {
                        window.location.href = res.redirect_url;
                    } else {
                        window.location.reload();
                    }
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function(xhr) {
                console.error("Error submitting form:", xhr); // Debug log

                let errorMessage = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                _toast('error', errorMessage);
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Handler untuk tombol "Ambil Kembali"
    $(document).off('click', '.btn-ambil-kembali').on('click', '.btn-ambil-kembali', function(e) {
        e.preventDefault();
        const btn = $(this);
        const url = btn.data('url');
        const penugasanId = btn.data('penugasan-id');
        const n_param = new URLSearchParams(window.location.search).get('n') || '';

        if (confirm('Apakah Anda yakin ingin mengambil kembali tugas ini?')) {
            // Disable button dan tampilkan loading
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
                    // Cek apakah response memiliki status atau bisa dianggap sukses
                    const isSuccess = res.status === true || res.code === '01' || res.code === '02';

                    // Tampilkan toast dengan warna yang sesuai
                    _toast(isSuccess ? 'success' : 'error', res.message || (isSuccess ?
                        'Data berhasil diubah' : 'Terjadi kesalahan'));

                    // Tutup modal jika sukses
                    if (isSuccess) {
                        _modalHide();

                        // Redirect jika ada URL redirect
                        if (res.redirect_url) {
                            window.location.href = res.redirect_url;
                        } else {
                            // Reload halaman jika tidak ada redirect
                            window.location.reload();
                        }
                    } else {
                        // Kembalikan tombol ke keadaan semula jika gagal
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-undo"></i> Ambil Kembali Tugas');
                    }
                },
                error: function(xhr) {
                    // Handle error response
                    let errorMessage = 'Terjadi kesalahan sistem';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    _toast('error', errorMessage);
                    btn.prop('disabled', false).html(
                        '<i class="fas fa-undo"></i> Ambil Kembali Tugas');
                }
            });
        }
    });


    // Inisialisasi komponen form
    $(document).ready(function() {
        // Inisialisasi repeater untuk sparepart
        if ($.fn.repeater) {
            $('#sparepart-repeater').repeater({
                initEmpty: false,
                show: function() {
                    $(this).slideDown();
                    // Reinisialisasi select jika menggunakan select2
                    $(this).find('.repeater-select').each(function() {
                        if ($.fn.select2) {
                            $(this).select2();
                        }
                    });
                },
                hide: function(deleteElement) {
                    if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                        $(this).slideUp(deleteElement);
                    }
                }
            });
        }
        
        // Inisialisasi autonumeric untuk input biaya
        if ($.fn.autoNumeric) {
            $('.autonumeric').autoNumeric('init', {
                aSep: '.',
                aDec: ',',
                mDec: '0'
            });
        }
    });
</script>
