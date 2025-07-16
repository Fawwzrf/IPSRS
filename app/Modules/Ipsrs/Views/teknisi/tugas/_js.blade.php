<script>
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

        // Handler submit for log-kerja form
        $(document).on('submit', '#form-log-kerja', function(e) {
            e.preventDefault(); // Mencegah redirect standar

            const form = $(this);
            const btn = form.find('button[type="submit"]');
            const url = form.attr('action');

            // Mengambil parameter 'n' dari URL utama browser
            const urlParams = new URLSearchParams(window.location.search);
            const navParam = urlParams.get('n');

            btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Menyimpan...');

            // Buat FormData dari form, yang wajib untuk upload file
            const formData = new FormData(this);

            // Tambahkan parameter 'n' langsung ke dalam FormData.
            if (navParam) {
                formData.append('n', navParam);
                console.log("Parameter 'n' (" + navParam + ") berhasil ditambahkan ke FormData.");
            } else {
                console.warn("Peringatan: Parameter 'n' tidak ditemukan. Ini mungkin gagal di middleware.");
            }

            $.ajax({
                type: "POST",
                url: url,
                data: formData, // formData sekarang sudah berisi 'n'
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res) {
                    const responseData = res.data || res;
                    if (responseData.code == '01' || responseData.code == '02') {
                        _toast('success', responseData.message || 'Data berhasil disimpan.');
                        _modalHide(1);
                        // Reload halaman atau tabel setelah sukses
                        if (typeof tabel !== 'undefined') {
                            tabel.ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        _toast('error', responseData.message || 'Gagal memproses data.');
                    }
                },
                error: function(xhr) {
                    const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr
                        .responseJSON.message : 'Terjadi kesalahan pada server.';
                    _toast('error', errorMsg);
                },
                complete: function() {
                    btn.prop('disabled', false).html(
                        '<i class="fas fa-save me-2"></i> Simpan Laporan');
                }
            });
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
                window.location.href = redirectUrl + (redirectUrl.includes('?') ? '&' : '?') + 'n=' + navParameter;
            } else {
                window.location.reload(); // Fallback jika URL tidak ada
            }
        }, 1000);
    }

    // Fungsi helper untuk menangani error
    function handleFailure(xhr, btn) {
        if(btn) btn.prop('disabled', false);
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
</script>
