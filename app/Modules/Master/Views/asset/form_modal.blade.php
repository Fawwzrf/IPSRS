<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    <?php echo csrf_field(); ?>
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Informasi Dasar Aset</legend>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Nama Aset</label>
                <div class="col-lg-9">
                    <input type="text" name="asset_nm" id="asset_nm" class="form-control" value="<?= @$main['asset_nm'] ?>" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">No. Seri / Barcode</label>
                <div class="col-lg-9">
                    <input type="text" name="no_seri" id="no_seri" class="form-control" value="<?= @$main['no_seri'] ?>" <?php if(@$main['no_seri']) echo 'readonly'; ?>>
                    <?php if(!@$main['no_seri']): ?>
                        <small class="form-hint">Akan menjadi identifikasi barcode utama. Dibuat permanen setelah disimpan.</small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Jenis</label>
                <div class="col-lg-9">
                    <input type="text" name="jenis" id="jenis" class="form-control" value="<?= @$main['jenis'] ?>">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Merk</label>
                <div class="col-lg-9">
                    <input type="text" name="merk" id="merk" class="form-control" value="<?= @$main['merk'] ?>">
                </div>
            </div>
             <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Model</label>
                <div class="col-lg-9">
                    <input type="text" name="model" id="model" class="form-control" value="<?= @$main['model'] ?>">
                </div>
            </div>
        </fieldset>
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Klasifikasi & Lokasi</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Kategori Aset</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="kategori_asset_id" id="kategori_asset_id" required>
                        <option value="">- Pilih Kategori -</option>
                        <?php if(isset($all_kategori_asset) && is_array($all_kategori_asset)): ?>
                            <?php foreach($all_kategori_asset as $kat): ?>
                                <option value="<?= $kat['kategori_asset_id'] ?>" <?php if(@$main['kategori_asset_id'] == $kat['kategori_asset_id']) echo 'selected'; ?>>
                                    <?= $kat['kategori_asset_id'] ?> - <?= $kat['kategori_asset_nm'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Lokasi</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="lokasi_id" id="lokasi_id">
                        <option value="">- Pilih Lokasi -</option>
                        <?php foreach($all_lokasi as $loc): ?>
                            <option value="<?= $loc['lokasi_id'] ?>" <?php if(@$main['lokasi_id'] == $loc['lokasi_id']) echo 'selected'; ?>>
                                <?= $loc['lokasi_id'] ?> - <?= $loc['lokasi_nm'] ?> (<?= $loc['tipe_lokasi'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Informasi Perolehan & Jadwal</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Tipe Perolehan</label>
                <div class="col-lg-9">
                    <input type="text" name="perolehan_tipe" id="perolehan_tipe" class="form-control" value="<?= @$main['perolehan_tipe'] ?>">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Tanggal Perolehan</label>
                <div class="col-lg-5">
                    <input type="text" name="perolehan_tgl" id="perolehan_tgl" class="form-control datepicker-notauto" value="<?= @to_date(@$main['perolehan_tgl'], '-', 'date') ?>">
                </div>
                <label class="col-lg-2 col-form-label text-lg-end">Umur (Thn)</label>
                <div class="col-lg-2">
                    <input type="number" name="umur_tahun" id="umur_tahun" class="form-control" value="<?= @$main['umur_tahun'] ?>" min="0">
                </div>
            </div>
             <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">PM Berikutnya</label>
                <div class="col-lg-5">
                    <input type="text" name="pm_berikutnya" id="pm_berikutnya" class="form-control datepicker-notauto" value="<?= @to_date(@$main['pm_berikutnya'], '-', 'date') ?>">
                </div>
            </div>
        </fieldset>
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Status Aset</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Status</label>
                <div class="col-lg-9 pt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="aktif" <?php if(@$main['status'] == 'aktif' || @$main['status'] == '') echo 'checked'; ?>>
                        <span class="form-check-label">Aktif</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="perbaikan" <?php if(@$main['status'] == 'perbaikan') echo 'checked'; ?>>
                        <span class="form-check-label">Perbaikan</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="nonaktif" <?php if(@$main['status'] == 'nonaktif') echo 'checked'; ?>>
                        <span class="form-check-label">Nonaktif</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="dihapus" <?php if(@$main['status'] == 'dihapus') echo 'checked'; ?>>
                        <span class="form-check-label">Dihapus</span>
                    </div>
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Aktif di Sistem?</label>
                <div class="col-lg-9 pt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="active_st" value="1" <?php if(@$main['active_st'] === null || @$main['active_st'] == 1) echo 'checked'; ?>>
                        <span class="form-check-label">Ya</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="active_st" value="0" <?php if(@$main['active_st'] !== null && @$main['active_st'] == 0) echo 'checked'; ?>>
                        <span class="form-check-label">Tidak</span>
                    </div>
                </div>
            </div>
        </fieldset>
        <div class="row mt-3">
            <div class="col-lg-9 offset-lg-3">
                <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>