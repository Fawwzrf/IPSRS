<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Jadwal PM</label>
            <div class="col-lg-4">
                <input type="text" name="jadwal_pm_id" id="jadwal_pm_id" class="form-control" value="<?= @$main['jadwal_pm_id'] ?>" <?= (@$main['jadwal_pm_id']) ? 'readonly' : '' ?> required>
                <small class="form-hint">ID jadwal PM harus unik. Contoh: JPM001</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Aset</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="asset_id" id="asset_id" required>
                    <option value="">- Pilih Aset -</option>
                    <?php foreach($all_asset as $asset) : ?>
                        <option value="<?= $asset['asset_id'] ?>" <?= (@$main['asset_id'] == $asset['asset_id']) ? 'selected' : '' ?>>
                            <?= $asset['asset_id'] ?> - <?= $asset['asset_nm'] ?> (Lokasi : <?= $asset['lokasi_nm'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Frekuensi</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="frekuensi" id="frekuensi" required>
                    <option value="">- Pilih Frekuensi -</option>
                    <option value="harian" <?= (@$main['frekuensi'] == 'harian') ? 'selected' : '' ?>>Harian</option>
                    <option value="mingguan" <?= (@$main['frekuensi'] == 'mingguan') ? 'selected' : '' ?>>Mingguan</option>
                    <option value="bulanan" <?= (@$main['frekuensi'] == 'bulanan') ? 'selected' : '' ?>>Bulanan</option>
                    <option value="kuartalan" <?= (@$main['frekuensi'] == 'kuartalan') ? 'selected' : '' ?>>Kuartalan</option>
                    <option value="tahunan" <?= (@$main['frekuensi'] == 'tahunan') ? 'selected' : '' ?>>Tahunan</option>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Jenis Pemeliharaan</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="jenis" id="jenis" required>
                    <option value="">- Pilih Jenis -</option>
                    <option value="kalibrasi" <?= (@$main['jenis'] == 'kalibrasi') ? 'selected' : '' ?>>Kalibrasi</option>
                    <option value="inspeksi" <?= (@$main['jenis'] == 'inspeksi') ? 'selected' : '' ?>>Inspeksi</option>
                    <option value="pembersihan" <?= (@$main['jenis'] == 'pembersihan') ? 'selected' : '' ?>>Pembersihan</option>
                    <option value="penggantian_part" <?= (@$main['jenis'] == 'penggantian_part') ? 'selected' : '' ?>>Penggantian Part</option>
                    <option value="penyesuaian" <?= (@$main['jenis'] == 'penyesuaian') ? 'selected' : '' ?>>Penyesuaian</option>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Deskripsi</label>
            <div class="col-lg-8 col-md-6">
                <textarea name="deskripsi" id="deskripsi" class="form-control"><?= @$main['deskripsi'] ?></textarea>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Estimasi (menit)</label>
            <div class="col-lg-2">
                <input type="number" name="estimasi_menit" id="estimasi_menit" class="form-control" value="<?= @$main['estimasi_menit'] ?>" min="0">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Tanggal Terakhir</label>
            <div class="col-lg-4">
                <input type="text" name="tgl_terakhir" id="tgl_terakhir" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_terakhir'], '-', 'date') ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Tanggal Berikutnya</label>
            <div class="col-lg-4">
                <input type="text" name="tgl_berikutnya" id="tgl_berikutnya" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_berikutnya'], '-', 'date') ?>" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Status Jadwal</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="status" id="status" required>
                    <option value="">- Pilih Status -</option>
                    <option value="aktif" <?= (@$main['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="ditunda" <?= (@$main['status'] == 'ditunda') ? 'selected' : '' ?>>Ditunda</option>
                    <option value="selesai" <?= (@$main['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                    <option value="dibatalkan" <?= (@$main['status'] == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
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