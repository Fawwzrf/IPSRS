<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Sparepart</label>
            <div class="col-lg-4">
                <input type="text" name="sparepart_id" id="sparepart_id" class="form-control" value="<?= @$main['sparepart_id'] ?>" <?= (@$main['sparepart_id']) ? 'readonly' : '' ?> required>
                <small class="form-hint">ID sparepart harus unik. Contoh: SPRT001</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Nama Sparepart</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="sparepart_nm" id="sparepart_nm" class="form-control" value="<?= @$main['sparepart_nm'] ?>" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">No. Seri</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="no_seri" id="no_seri" class="form-control" value="<?= @$main['no_seri'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Merk</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="merk" id="merk" class="form-control" value="<?= @$main['merk'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Satuan</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="satuan" id="satuan" class="form-control" value="<?= @$main['satuan'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Harga (Rp)</label>
            <div class="col-lg-4 col-md-6">
                <input type="text" name="harga" id="harga" class="form-control autonumeric" value="<?= @$main['harga'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Stok</label>
            <div class="col-lg-2 col-md-6">
                <input type="number" name="stok" id="stok" class="form-control" value="<?= @$main['stok'] ?>" min="0">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Lokasi Penyimpanan</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="lokasi_penyimpanan" id="lokasi_penyimpanan" class="form-control" value="<?= @$main['lokasi_penyimpanan'] ?>">
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