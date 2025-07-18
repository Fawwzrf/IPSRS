<div class="modal-header">
    <h5 class="modal-title">Tambah Log Kerja</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div id="form-message"></div>
    
    <form id="form-log-kerja">
        @csrf
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
    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-primary" onclick="submitLogKerja()">Simpan Log Kerja</button>
</div>

<script>
function submitLogKerja() {
    const form = document.getElementById('form-log-kerja');
    
    // Validasi form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Disable button untuk mencegah double submit
    const submitBtn = document.querySelector('.modal-footer .btn-primary');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    // Buat FormData dari form
    const formData = new FormData(form);
    const orderKerjaId = formData.get('order_kerja_id');
    
    // Ambil nilai-nilai untuk dikirim via URL parameter
    const diagnosa = formData.get('diagnosa');
    const tindakan = formData.get('tindakan');
    const hasil = formData.get('hasil');
    const durasi = formData.get('durasi_menit');
    const assetId = formData.get('asset_id');
    const nParam = formData.get('n');
    
    // Untuk debugging
    console.log("Form data:", {
        orderKerjaId, diagnosa, tindakan, hasil, durasi, assetId, nParam
    });
    
    // Kirim data dengan fetch API (GET method)
    fetch(`/ipsrs/teknisitugas/save_laporan/${orderKerjaId}?asset_id=${assetId}&diagnosa=${encodeURIComponent(diagnosa)}&tindakan=${encodeURIComponent(tindakan)}&hasil=${hasil}&durasi_menit=${durasi}&n=${nParam}`)
        .then(response => response.json())
        .then(data => {
            console.log("Response:", data);
            
            // Handle success
            if (data.status === true) {
                // Tampilkan pesan sukses
                if (typeof _toast === 'function') {
                    _toast('success', data.message || 'Log kerja berhasil disimpan');
                } else {
                    alert(data.message || 'Log kerja berhasil disimpan');
                }
                
                // Tutup modal
                $('.modal').modal('hide');
                
                // Redirect setelah simpan
                if (data.data && data.data.redirect_url) {
                    window.location.href = data.data.redirect_url;
                } else {
                    window.location.reload();
                }
            } else {
                // Tampilkan pesan error
                const errorMsg = data.message || 'Terjadi kesalahan';
                if (typeof _toast === 'function') {
                    _toast('error', errorMsg);
                } else {
                    document.getElementById('form-message').innerHTML = 
                        `<div class="alert alert-danger">${errorMsg}</div>`;
                }
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Simpan Log Kerja';
            }
        })
        .catch(error => {
            console.error("Error:", error);
            document.getElementById('form-message').innerHTML = 
                `<div class="alert alert-danger">Terjadi kesalahan: ${error.message}</div>`;
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Simpan Log Kerja';
        });
}
</script>
