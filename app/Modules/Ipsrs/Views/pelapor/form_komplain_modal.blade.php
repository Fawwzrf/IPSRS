@include('ipsrs::pelapor._js')

<form id="form-pelapor-komplain" action="<?= url('ipsrs/pelapor/save') ?>" method="post" autocomplete="off" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="pegawai_id" value="<?= $pegawai_id ?? session('pegawai_id') ?>">

    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Laporan Kerusakan</legend>
            
            <div class="mb-3">
                <label class="form-label required">Lokasi Kerusakan</label>
                <select class="form-select chosen-select" name="lokasi_id" id="lokasi-select" required>
                    <option value="">- Pilih Lokasi Terlebih Dahulu -</option>
                    <?php foreach($all_lokasi as $lokasi): ?>
                        <option value="<?= $lokasi['lokasi_id'] ?>" data-denah-url="<?= $lokasi['denah_url'] ?? '' ?>">
                            <?= $lokasi['lokasi_nm'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label required">Aset yang Bermasalah</label>
                <select class="form-select chosen-select" name="asset_id" id="asset-select" required disabled>
                    <option value="">- Pilih Lokasi Terlebih Dahulu -</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label required">Deskripsi Kerusakan</label>
                <textarea name="deskripsi" class="form-control" rows="4" required placeholder="Contoh: AC tidak dingin, terdengar suara berisik dari unit outdoor."></textarea>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Denah Lokasi</legend>
            <div id="denah-container" class="mb-3" style="display: none;">
                <label class="form-label">Tandai Lokasi di Denah (Opsional)</label>
                <div class="d-flex justify-content-center align-items-center bg-light" style="min-height: 250px; border: 1px dashed #ccc;">
                    <canvas id="denah-canvas" style="display: none; max-width: 100%;"></canvas>
                    <span id="canvas-placeholder">Denah tidak tersedia.</span>
                </div>
                <input type="hidden" name="anotasi_url" id="anotasi_url">
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-paper-plane me-2"></i> Kirim Laporan</button>
        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
    </div>
</form>

<script>
    window.allAssets = <?= json_encode($all_asset) ?>;
</script>