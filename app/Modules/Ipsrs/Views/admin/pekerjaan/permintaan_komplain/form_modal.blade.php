<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Permintaan Komplain</legend>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tanggal</label>
                <div class="col-lg-9">
                    <input type="text" name="tgl" class="form-control datepicker-notauto"
                        value="<?= @to_date(@$main['tgl'], '-', 'date') ?: date('d-m-Y') ?>" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Lokasi Komplain</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="lokasi_id" id="lokasi-select" required>
                        <option value="">- Pilih Lokasi Terlebih Dahulu -</option>
                        <?php
                            $selected_lokasi_id = '';
                            if (@$main['asset_id']) {
                                $asset = \App\Modules\App\Models\DbModel::getData('mst_asset', [
                                    'asset_id' => $main['asset_id'],
                                ]);
                                $selected_lokasi_id = @$asset['lokasi_id'];
                            }
                        ?>
                        <?php foreach ($all_lokasi as $lokasi): ?>
                            <option value="<?= $lokasi['lokasi_id'] ?>" data-denah-url="<?= $lokasi['denah_url'] ?? '' ?>"
                                <?php if ($selected_lokasi_id == $lokasi['lokasi_id']): ?>selected<?php endif; ?>>
                                <?= $lokasi['lokasi_nm'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Aset yang Dikomplain</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="asset_id" id="asset-select" required disabled>
                        <option value="">- Pilih Lokasi Terlebih Dahulu -</option>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Dibuat oleh</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="pegawai_id" required>
                        <option value="">- Pilih Pegawai -</option>
                        <?php foreach ($all_pegawai as $pegawai): ?>
                            <option value="<?= $pegawai['pegawai_id'] ?>"
                                <?php if (@$main['pegawai_id'] == $pegawai['pegawai_id']): ?>selected<?php endif; ?>>
                                <?= $pegawai['pegawai_nm'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Deskripsi Komplain</label>
                <div class="col-lg-9">
                    <textarea name="deskripsi" class="form-control" rows="4" required><?= @$main['deskripsi'] ?></textarea>
                </div>
            </div>

            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Status</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="status">
                        <option value="baru" <?php if (@$main == '' || @$main['status'] == 'baru'): ?>selected<?php endif; ?>>Baru</option>
                        <option value="diproses" <?php if (@$main['status'] == 'diproses'): ?>selected<?php endif; ?>>Diproses</option>
                        <option value="selesai" <?php if (@$main['status'] == 'selesai'): ?>selected<?php endif; ?>>Selesai</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Denah Lokasi (Otomatis)</legend>
            <div class="mb-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-hapus-anotasi"
                    style="display: none;">
                    <i class="fas fa-times me-2"></i> Hapus Tanda
                </button>
                <small class="form-hint ms-2">Klik pada denah untuk memberi tanda lokasi komplain.</small>
            </div>
            <div class="d-flex justify-content-center align-items-center bg-light"
                style="min-height: 450px; border: 1px dashed #ccc;">
                <canvas id="denah-canvas" style="display: none; max-width: 100%;"></canvas>
                <span id="canvas-placeholder">Pilih lokasi untuk menampilkan denah</span>
            </div>
            <input type="hidden" name="anotasi_url" id="anotasi_url" value="<?= @$main['anotasi_url'] ?>">
        </fieldset>

        <div class="row mt-3">
            <div class="col-lg-9 offset-lg-3">
                <button type="submit" class="btn btn-primary" onclick="window.drawCanvasForSave(); _save(event);"><i
                        class="fas fa-save me-2"></i> Simpan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>

<script>
    window.allAssets = <?= json_encode($all_asset) ?>;
    window.selectedAsset = '<?= @$main['asset_id'] ?>';
</script>
