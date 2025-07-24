<div class="modal-header">
    <h5 class="modal-title">Tambah Log Kerja</h5>
</div>

<form id="form-log-kerja" action="{{ $form_act }}" method="post" enctype="multipart/form-data">
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
                        <option value="menunggu_sparepart">Menunggu Sparepart</option>
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

        <div class="mb-3">
            <label class="form-label">Bukti Foto Pekerjaan</label>
            <input type="file" name="fotos[]" class="form-control" multiple accept="image/*">
            <div class="form-text text-muted">Upload foto hasil pekerjaan (bisa lebih dari satu).</div>
        </div>
        @if (!empty($log_fotos))
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Foto Bukti yang Sudah Diunggah:</strong></p>
                        <div class="d-flex flex-wrap">
                            @foreach ($log_fotos as $foto)
                                <div class="me-2 mb-2">
                                    <img src="{{ $foto['foto_url'] }}" alt="Foto Bukti" class="img-thumbnail"
                                        style="height: 100px;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        <div class="mb-3">
            <label class="form-label">Sparepart yang Digunakan</label>
            <div id="sparepart-container">
                <div class="row mb-2 sparepart-row">
                    <div class="col-md-6">
                        <select name="sparepart_id[]" class="form-select">
                            <option value="">-- Pilih Sparepart --</option>
                            @foreach($all_sparepart as $sp)
                                <option value="{{ $sp['sparepart_id'] }}">{{ $sp['sparepart_nm'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="jumlah[]" class="form-control" min="1" value="1" required
                            placeholder="Jumlah">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-sparepart">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" id="btn-add-sparepart" class="btn btn-success btn-sm mt-2">
                <i class="fas fa-plus"></i> Tambah Sparepart
            </button>
            <div class="form-text text-muted">Tambah sparepart yang digunakan dalam pekerjaan ini.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Biaya tambahan (Rp)</label>
            <input type="number" name="total_biaya" class="form-control" min="0" step="100" value="0"
                required>
            <div class="form-text text-muted">Masukkan biaya tambahan (jika ada).</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" onclick="_save(event)">Simpan</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#btn-add-sparepart').on('click', function() {
        var row = $('#sparepart-container .sparepart-row:first').clone();
        row.find('select').val('');
        row.find('input').val(1);
        $('#sparepart-container').append(row);
    });

    // Hapus baris sparepart
    $('#sparepart-container').on('click', '.btn-remove-sparepart', function() {
        if ($('#sparepart-container .sparepart-row').length > 1) {
            $(this).closest('.sparepart-row').remove();
        }
    });
});
</script>
