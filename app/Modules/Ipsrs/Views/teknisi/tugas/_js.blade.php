<script>
    // Ambil parameter 'n' dari URL saat ini
    const urlParams = new URLSearchParams(window.location.search);
    const navParameter = urlParams.get('n');

    // Fungsi terpusat untuk menangani aksi sukses
    function handleSuccess(message, redirectUrl) {
        _modalHide(1); // Tutup semua modal
        _toast("success", message);

        // Alihkan halaman ke URL yang benar setelah 1 detik
        setTimeout(() => {
            if (redirectUrl) {
                // Tambahkan kembali parameter 'n' saat redirect
                window.location.href = redirectUrl + '?n=' + navParameter;
            } else {
                window.location.reload(); // Fallback jika URL tidak ada
            }
        }, 1000);
    }

    // Fungsi terpusat untuk menangani error
    function handleFailure(xhr, btn) {
        if(btn) btn.prop('disabled', false);
        const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
        alert(errorMsg);
    }

    // Handler untuk tombol aksi langsung (Terima, Batal Terima)
    $(document).on('click', '.btn-task-action', function(e) {
        e.preventDefault();
        if (!confirm('Apakah Anda yakin ingin melanjutkan aksi ini?')) return;

        const $btn = $(this);
        const url = $btn.data('url');
        const id = $btn.data('id');
        $btn.prop('disabled', true);

        // **PERBAIKAN UTAMA: Tambahkan parameter 'n' ke data yang dikirim**
        const postData = {
            penugasan_id: id,
            _token: '{{ csrf_token() }}',
            n: navParameter // <-- Tambahkan ini
        };

        $.post(url, postData, function(res) {
            if (res.code === '02') {
                handleSuccess(res.message, res.redirect_url);
            } else {
                alert(res.message || 'Gagal memproses.');
                $btn.prop('disabled', false);
            }
        }, 'json').fail(xhr => handleFailure(xhr, $btn));
    });

    // Handler untuk form AJAX (Form Tolak Tugas)
    $(document).on('submit', '#form-ajax', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type=submit]');
        $btn.prop('disabled', true);

        // Ambil data dari form dan tambahkan parameter 'n'
        let formData = $form.serialize();
        formData += '&n=' + navParameter; // <-- Tambahkan ini

        $.post($form.attr('action'), formData, function(res) {
            if (res.code === '02') {
                handleSuccess(res.message, res.redirect_url);
            } else {
                alert(res.message || 'Gagal memproses.');
                $btn.prop('disabled', false);
            }
        }, 'json').fail(xhr => handleFailure(xhr, $btn));
    });
</script>