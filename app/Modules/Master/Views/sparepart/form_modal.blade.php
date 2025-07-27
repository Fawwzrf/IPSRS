<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    <?php echo csrf_field(); ?>
    <div class="card-body">

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Informasi Sparepart</legend>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">ID Sparepart</label>
                <div class="col-lg-5">
                    <input type="text" name="sparepart_id" id="sparepart_id" class="form-control" value="<?= @$main['sparepart_id'] ?>" <?php if(@$main) echo 'readonly'; ?> required>
                    <small class="form-hint">ID unik untuk sparepart. Contoh: SPRT001</small>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Nama Sparepart</label>
                <div class="col-lg-9">
                    <input type="text" name="sparepart_nm" id="sparepart_nm" class="form-control" value="<?= @$main['sparepart_nm'] ?>" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">No. Seri</label>
                <div class="col-lg-9">
                    <input type="text" name="no_seri" id="no_seri" class="form-control" value="<?= @$main['no_seri'] ?>">
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Merk</label>
                <div class="col-lg-9">
                    <input type="text" name="merk" id="merk" class="form-control" value="<?= @$main['merk'] ?>">
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail & Penyimpanan</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Satuan</label>
                <div class="col-lg-4">
                    <input type="text" name="satuan" id="satuan" class="form-control" value="<?= @$main['satuan'] ?>" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Harga (Rp)</label>
                <div class="col-lg-4">
                    <input type="text" name="harga" id="harga" class="form-control" value="<?= @$main['harga'] ?>">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Stok</label>
                <div class="col-lg-3">
                    <input type="number" name="stok" id="stok" class="form-control" value="<?= @$main['stok'] ?? 0 ?>" readonly>
                    <small class="form-hint">Stok dikelola otomatis oleh sistem.</small>
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Lokasi Penyimpanan</label>
                <div class="col-lg-9">
                    <input type="text" name="lokasi_penyimpanan" id="lokasi_penyimpanan" class="form-control" value="<?= @$main['lokasi_penyimpanan'] ?>">
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Status</legend>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Aktif?</label>
                <div class="col-lg-9 pt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="active_st" value="1" <?php if(@$main == '' || @$main['active_st'] == 1) echo 'checked'; ?>>
                        <span class="form-check-label">Aktif</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="active_st" value="0" <?php if(@$main != '' && @$main['active_st'] == 0) echo 'checked'; ?>>
                        <span class="form-check-label">Tidak Aktif</span>
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