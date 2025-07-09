<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Penerimaan</label>
            <div class="col-lg-4">
                <input type="text" name="penerimaan_id" id="penerimaan_id" class="form-control" value="<?= @$main['penerimaan_id'] ?>" <?= (@$main['penerimaan_id']) ? 'readonly' : '' ?> required>
                <small class="form-hint">ID penerimaan harus unik. Contoh: TRXSP001</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Tgl. Penerimaan</label>
            <div class="col-lg-4">
                <input type="text" name="tgl" id="tgl" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl'], '-', 'date') ?>" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Sparepart</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="sparepart_id" id="sparepart_id" required>
                    <option value="">- Pilih Sparepart -</option>
                    <?php foreach($all_sparepart as $sp) : ?>

                        <option value="<?= $sp['sparepart_id'] ?>" data-price="<?= $sp['harga'] ?>" <?= (@$main['sparepart_id'] == $sp['sparepart_id']) ? 'selected' : '' ?>>
                            <?= $sp['sparepart_id'] ?> - <?= $sp['sparepart_nm'] ?> (Stok: <?= $sp['stok'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Jumlah</label>
            <div class="col-lg-2">
                <input type="number" name="jumlah" id="jumlah" class="form-control" value="<?= @$main['jumlah'] ?>" min="1" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Harga Satuan (Rp)</label>
            <div class="col-lg-4">
                <input type="text" name="harga_satuan" id="harga_satuan" class="form-control autonumeric" value="<?= @$main['harga_satuan'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Vendor</label>
            <div class="col-lg-8">
                <input type="text" name="vendor" id="vendor" class="form-control" value="<?= @$main['vendor'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">No. Faktur</label>
            <div class="col-lg-8">
                <input type="text" name="no_faktur" id="no_faktur" class="form-control" value="<?= @$main['no_faktur'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Catatan</label>
            <div class="col-lg-8">
                <textarea name="catatan" id="catatan" class="form-control"><?= @$main['catatan'] ?></textarea>
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