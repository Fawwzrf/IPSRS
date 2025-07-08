<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf {{-- Pastikan CSRF token ada --}}
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Nama Aset</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="asset_nm" id="asset_nm" class="form-control" value="<?= @$main['asset_nm'] ?>" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Jenis</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="jenis" id="jenis" class="form-control" value="<?= @$main['jenis'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">No. Seri / Barcode</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="no_seri" id="no_seri" class="form-control" value="<?= @$main['no_seri'] ?>" <?= (@$main['no_seri']) ? 'readonly' : '' ?>>
                <?php if (!@$main['no_seri']) : ?>
                <small class="form-hint">Nomor Seri akan menjadi identifikasi barcode utama.</small>
                <?php endif; ?>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Merk</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="merk" id="merk" class="form-control" value="<?= @$main['merk'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Model</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="model" id="model" class="form-control" value="<?= @$main['model'] ?>">
            </div>
        </div>
        <div class="border-dotted mb-2"></div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Tipe Perolehan</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="perolehan_tipe" id="perolehan_tipe" class="form-control" value="<?= @$main['perolehan_tipe'] ?>">
            </div>
        </div>
        {{-- Layout Tanggal Perolehan dan PM Berikutnya mengikuti pola Pegawai --}}
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Tanggal Perolehan</label>
            <div class="col-lg-4 col-md-6">
                <input type="text" name="perolehan_tgl" id="perolehan_tgl" class="form-control datepicker-notauto" value="<?= @to_date(@$main['perolehan_tgl'], '-', 'date') ?>">
            </div>
            <label class="col-lg-2 col-md-6 col-form-label">PM Berikutnya</label>
            <div class="col-lg-3 col-md-6">
                <input type="text" name="pm_berikutnya" id="pm_berikutnya" class="form-control datepicker-notauto" value="<?= @to_date(@$main['pm_berikutnya'], '-', 'date') ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Umur (Tahun)</label>
            <div class="col-lg-2 col-md-6">
                <input type="number" name="umur_tahun" id="umur_tahun" class="form-control" value="<?= @$main['umur_tahun'] ?>" min="0">
            </div>
        </div>
        <div class="border-dotted mb-2"></div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Lokasi</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="lokasi_id" id="lokasi_id">
                    <option value="">- Pilih Lokasi -</option>
                    <?php foreach($all_lokasi as $loc) : ?>
                        <option value="<?= $loc['lokasi_id'] ?>" <?= (@$main['lokasi_id'] == $loc['lokasi_id']) ? 'selected' : '' ?>>
                            <?= $loc['lokasi_id'] ?> - <?= $loc['lokasi_nm'] ?> (<?= $loc['tipe_lokasi'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Kategori Aset</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="kategori_asset_id" id="kategori_asset_id">
                    <option value="">- Pilih Kategori -</option>
                    <?php foreach($all_kategori_asset as $kat) : ?>
                        <option value="<?= $kat['kategori_asset_id'] ?>" <?= (@$main['kategori_asset_id'] == $kat['kategori_asset_id']) ? 'selected' : '' ?>>
                            <?= $kat['kategori_asset_id'] ?> - <?= $kat['kategori_asset_nm'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="border-dotted mb-2"></div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Status Aset</label>
            <div class="col-lg-8 col-md-6">
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" value="aktif" <?= (@$main['status'] == 'aktif' || @$main['status'] == '') ? 'checked' : '' ?>>
                    <span class="form-check-label">Aktif</span>
                </label>
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" value="perbaikan" <?= (@$main['status'] == 'perbaikan') ? 'checked' : '' ?>>
                    <span class="form-check-label">Perbaikan</span>
                </label>
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" value="nonaktif" <?= (@$main['status'] == 'nonaktif') ? 'checked' : '' ?>>
                    <span class="form-check-label">Nonaktif</span>
                </label>
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" value="dihapus" <?= (@$main['status'] == 'dihapus') ? 'checked' : '' ?>>
                    <span class="form-check-label">Dihapus</span>
                </label>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Aktif di Sistem?</label>
            <div class="col-lg-8 col-md-6">
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="active_st" value="1" <?= (@$main == '') ? 'checked' : ((@$main['active_st'] == 1) ? 'checked' : '') ?>>
                    <span class="form-check-label">Aktif</span>
                </label>
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="active_st" value="0" <?= (@$main != '') ? ((@$main['active_st'] == 0) ? 'checked' : '') : '' ?>>
                    <span class="form-check-label">Tidak Aktif</span>
                </label>
            </div>
        </div>
        <div class="border-dotted"></div>
        <div class="row mt-2">
            <div class="col-9 offset-3">
                <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
                <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Batal</button>
            </div>
        </div>
    </div>
</form>

