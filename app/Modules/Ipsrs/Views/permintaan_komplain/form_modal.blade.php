<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Permintaan</label>
            <div class="col-lg-4">
                <input type="text" name="permintaan_id" id="permintaan_id" class="form-control" value="<?= @$main['permintaan_id'] ?>" <?= (@$main['permintaan_id']) ? 'readonly' : '' ?> required>
                <small class="form-hint">ID permintaan harus unik. Contoh: KOM001</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Tanggal Komplain</label>
            <div class="col-lg-4">
                <input type="text" name="tgl" id="tgl" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl'], '-', 'date') ?>" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Aset</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="asset_id" id="asset_id" required>
                    <option value="">- Pilih Aset -</option>
                    <?php foreach($all_asset as $asset) : ?>
                        <option value="<?= $asset['asset_id'] ?>" <?= (@$main['asset_id'] == $asset['asset_id']) ? 'selected' : '' ?>>
                            <?= $asset['asset_id'] ?> - <?= $asset['asset_nm'] ?> (SN: <?= @$asset['no_seri'] ?>, Lokasi: <?= @$asset['lokasi_nm'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Pembuat Komplain</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="pegawai_id" id="pegawai_id" required>
                    <option value="">- Pilih Pembuat Komplain -</option>
                    <?php foreach($all_pegawai as $pegawai) : ?>
                        <option value="<?= $pegawai['pegawai_id'] ?>" <?= (@$main['pegawai_id'] == $pegawai['pegawai_id']) ? 'selected' : '' ?>>
                            <?= $pegawai['pegawai_nm'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Deskripsi Komplain</label>
            <div class="col-lg-8">
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" required><?= @$main['deskripsi'] ?></textarea>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Status Komplain</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="status" id="status" required>
                    <option value="">- Pilih Status -</option>
                    <option value="baru" <?= (@$main['status'] == 'baru' || @$main['status'] == '') ? 'selected' : '' ?>>Baru</option>
                    <option value="diproses" <?= (@$main['status'] == 'diproses') ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= (@$main['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= (@$main['status'] == 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                    <option value="dibatalkan" <?= (@$main['status'] == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Aktif?</label>
            <div class="col-lg-8">
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