<form id="form-update-status" action="<?= url('ipsrs/admin_order_kerja/update_status') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="order_kerja_id" value="<?= $order_kerja['order_kerja_id'] ?>">
    
    <div class="mb-3">
        <label class="form-label">Status Saat Ini</label>
        <input type="text" class="form-control" value="<?= ucfirst($order_kerja['status']) ?>" readonly>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Status Baru</label>
        <select name="status_baru" class="form-select" required>
            <option value="">- Pilih Status -</option>
            <option value="menunggu" <?= $order_kerja['status'] == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
            <option value="diproses" <?= $order_kerja['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
            <option value="menunggu_sparepart" <?= $order_kerja['status'] == 'menunggu_sparepart' ? 'selected' : '' ?>>Menunggu Sparepart</option>
            <option value="selesai" <?= $order_kerja['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
            <option value="dibatalkan" <?= $order_kerja['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
        </select>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Catatan</label>
        <textarea name="keterangan" class="form-control" rows="3"></textarea>
        <div class="form-text">Berikan catatan tentang alasan perubahan status ini</div>
    </div>
    
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary ms-2">Simpan Perubahan</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#form-update-status').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Menyimpan...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    _toast('success', res.message);
                    _modalHide();
                    // Reload halaman setelah sukses
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    _toast('error', res.message);
                    btn.prop('disabled', false).html('Simpan Perubahan');
                }
            },
            error: function(xhr) {
                _toast('error', 'Terjadi kesalahan server');
                btn.prop('disabled', false).html('Simpan Perubahan');
            }
        });
    });
});
</script>
