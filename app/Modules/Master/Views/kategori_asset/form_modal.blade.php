<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Kategori</label>
            <div class="col-lg-4">
                <input type="text" name="kategori_asset_id" id="kategori_asset_id" class="form-control" value="<?= @$main['kategori_asset_id'] ?>" <?= (@$main['kategori_asset_id']) ? 'readonly' : '' ?> required>
                <small class="form-hint">ID kategori harus unik. Contoh: KAT001</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Nama Kategori</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="kategori_asset_nm" id="kategori_asset_nm" class="form-control" value="<?= @$main['kategori_asset_nm'] ?>" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Deskripsi</label>
            <div class="col-lg-8 col-md-6">
                <textarea name="deskripsi" id="deskripsi" class="form-control"><?= @$main['deskripsi'] ?></textarea>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Aktif?</label>
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