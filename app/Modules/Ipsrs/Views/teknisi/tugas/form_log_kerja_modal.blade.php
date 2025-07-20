<div class="modal-header">
    <h5 class="modal-title">Tambah Log Kerja</h5>
</div>

<form id="form-log-kerja" action="{{ $form_act }}" method="post">
    @csrf
    <div class="modal-body">
        <div id="form-message"></div>
        
        <input type="hidden" name="order_kerja_id" value="{{ $order_kerja_id }}">
        <input type="hidden" name="asset_id" value="{{ $asset_id }}">
        <input type="hidden" name="n" value="{{ $n_param ?? '' }}">
        
        <div class="mb-3">
            <label class="form-label required">Diagnosa</label>
            <textarea class="form-control" name="diagnosa" rows="3" required></textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label required">Tindakan</label>
            <textarea class="form-control" name="tindakan" rows="3" required></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label required">Hasil</label>
                    <select name="hasil" class="form-select" required>
                        <option value="">-- Pilih Hasil --</option>
                        <option value="berhasil">Berhasil</option>
                        <option value="perlu_tindak_lanjut">Perlu Tindak Lanjut</option>
                        <option value="tidak_berhasil">Tidak Berhasil</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Durasi (menit)</label>
                    <input type="number" name="durasi_menit" class="form-control" min="1" value="0">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('form-log-kerja').onsubmit = function(e) {
        e.preventDefault();
        
        // Disable button untuk mencegah double submit
        const submitBtn = document.querySelector('.modal-footer .btn-primary');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        
        _save(this, {
            onSuccess: function(res) {
                _toast('success', res.message || 'Log kerja berhasil disimpan');
                _modalHide();
                _reload();
            },
            onError: function(res) {
                _toast('error', res.message || 'Gagal menyimpan log kerja');
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Simpan';
            }
        });
    };
});

// Contoh handler submit AJAX
$(document).on('submit', '#form-log-kerja', function(e) {
    e.preventDefault();
    var $form = $(this);
    var formData = new FormData(this);

    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status) {
                _toast('success', res.message || 'Berhasil');
                _modalHide();
                if (res.data && res.data.redirect_url) {
                    window.location.href = res.data.redirect_url;
                } else if (res.uri) {
                    _page(res.uri);
                }
            } else {
                _toast('error', res.message || 'Gagal');
            }
        },
        error: function(xhr) {
            _toast('error', 'Terjadi kesalahan server');
        }
    });
    return false;
});
</script>
