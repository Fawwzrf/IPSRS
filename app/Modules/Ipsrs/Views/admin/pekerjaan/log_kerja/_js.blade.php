<script>
    // Inisialisasi plugin-plugin (Select2, Repeater, dll.)
    const modal = $('#form-log-kerja').closest('.modal');
    modal.find('.autonumeric').autoNumeric('init', {
        aSep: '.',
        aDec: ',',
        mDec: '0'
    });
    modal.find('#sparepart-repeater').repeater({
        initEmpty: true,
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
    modal.find('.repeater-select').select2({
        theme: 'bootstrap-5',
        dropdownParent: modal
    });

    // ====================== AWAL PERBAIKAN ======================
    // Mengambil parameter 'n' langsung dari PHP, seperti pada form_scan_modal
    // Ini hanya untuk referensi, kita menggunakan hidden field dalam form
    const n_param_global = '<?= request("n") ?>'; 
    
    $(document).off('submit', '#form-log-kerja').on('submit', '#form-log-kerja', function(e) {
        e.preventDefault();

        const form = $(this);
        const btn = form.find('button[type="submit"]');
        let url = form.attr('action');
        
        // Mengambil parameter 'n' dari hidden field di form
        const n_param = form.find('input[name="n"]').val();
        
        // Ambil ID aset dari URL atau hidden field jika tersedia
        const assetId = form.find('input[name="asset_id"]').val() || 
                        new URLSearchParams(window.location.search).get('asset_id');
                        
        console.log("Mengirim form dengan parameter 'n':", n_param, "dan asset ID:", assetId);
        
        btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Menyimpan...');
        
        // Buat FormData dari form
        const formData = new FormData(this);
        
        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                const responseData = res.data || res;
                
                if (responseData.code == '01' || responseData.code == '02' || res.status === true) {
                    _toast('success', responseData.message || res.message || 'Data berhasil disimpan.');
                    _modalHide(1);
                    
                    // PERBAIKAN: Redirect ke halaman detail aset dengan parameter n
                    if (assetId && n_param) {
                        // Buat URL detail aset dengan parameter n
                        const detailUrl = new URL(`${window.location.protocol}//${window.location.host}/master/asset/detail/${assetId}`);
                        detailUrl.searchParams.set('n', n_param);
                        
                        console.log("Redirect ke halaman detail aset:", detailUrl.toString());
                        window.location.href = detailUrl.toString();
                    } else if (res.uri) {
                        // Fallback: Gunakan URI dari server tapi tambahkan parameter n
                        if (n_param) {
                            try {
                                const redirectUrl = new URL(res.uri);
                                redirectUrl.searchParams.set('n', n_param);
                                window.location.href = redirectUrl.toString();
                            } catch (e) {
                                console.error("Error saat mengolah URI redirect:", e);
                                window.location.href = res.uri + (res.uri.includes('?') ? '&' : '?') + 'n=' + n_param;
                            }
                        } else {
                            window.location.href = res.uri;
                        }
                    } else {
                        // Jika tidak ada opsi lain, reload halaman dengan mempertahankan parameter 'n'
                        if (n_param) {
                            const currentUrl = new URL(window.location.href);
                            currentUrl.searchParams.set('n', n_param);
                            window.location.href = currentUrl.toString();
                        } else {
                            window.location.reload();
                        }
                    }
                } else {
                    _toast('error', responseData.message || 'Gagal memproses data.');
                    btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Laporan');
                }
            },
            error: function(xhr) {
                const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
                _toast('error', errorMsg);
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Laporan');
            }
        });
    });
    // ======================= AKHIR PERBAIKAN =======================
</script>