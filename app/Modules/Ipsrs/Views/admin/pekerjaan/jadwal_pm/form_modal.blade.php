<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    <?php echo csrf_field(); ?>
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Jadwal</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Aset</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="asset_id" required>
                        <option value="">- Pilih Aset -</option>
                        <?php foreach ($all_asset as $asset): ?>
                            <option value="<?= $asset['asset_id'] ?>" <?php if (@$main['asset_id'] == $asset['asset_id']) echo 'selected'; ?>>
                                <?= $asset['asset_nm'] ?> (<?= $asset['no_seri'] ?? $asset['asset_id'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Frekuensi</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="frekuensi" required>
                        <option value="">- Pilih Frekuensi -</option>
                        <option value="Harian" <?php if (@$main['frekuensi'] == 'Harian') echo 'selected'; ?>>Harian</option>
                        <option value="Mingguan" <?php if (@$main['frekuensi'] == 'Mingguan') echo 'selected'; ?>>Mingguan</option>
                        <option value="Bulanan" <?php if (@$main['frekuensi'] == 'Bulanan') echo 'selected'; ?>>Bulanan</option>
                        <option value="Kuartalan" <?php if (@$main['frekuensi'] == 'Kuartalan') echo 'selected'; ?>>Kuartalan (3 Bulan)
                        </option>
                        <option value="Tahunan" <?php if (@$main['frekuensi'] == 'Tahunan') echo 'selected'; ?>>Tahunan</option>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Jenis Pekerjaan</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="jenis" required>
                        <option value="">- Pilih Jenis -</option>
                        <option value="Pembersihan" <?php if (@$main['jenis'] == 'Pembersihan') echo 'selected'; ?>>Pembersihan
                        </option>
                        <option value="Inspeksi" <?php if (@$main['jenis'] == 'Inspeksi') echo 'selected'; ?>>Inspeksi</option>
                        <option value="Kalibrasi" <?php if (@$main['jenis'] == 'Kalibrasi') echo 'selected'; ?>>Kalibrasi</option>
                        <option value="PenggantianPart" <?php if (@$main['jenis'] == 'PenggantianPart') echo 'selected'; ?>>Penggantian
                            Part</option>
                        <option value="Penyesuaian" <?php if (@$main['jenis'] == 'Penyesuaian') echo 'selected'; ?>>Penyesuaian
                        </option>
                    </select>
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Deskripsi</label>
                <div class="col-lg-9">
                    <textarea name="deskripsi" class="form-control" rows="3"><?= @$main['deskripsi'] ?></textarea>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Penanggalan & Status</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tanggal Terakhir PM</label>
                <div class="col-lg-5">
                    <input type="text" name="tgl_terakhir" class="form-control datepicker-notauto"
                        value="<?= @to_date(@$main['tgl_terakhir'], '-', 'date') ?>" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Tanggal Berikutnya PM</label>
                <div class="col-lg-5">
                    <input type="text" name="tgl_berikutnya" class="form-control"
                        value="<?= @to_date(@$main['tgl_berikutnya'], '-', 'date') ?>" readonly
                        placeholder="Terisi otomatis setelah disimpan">
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Status Jadwal</label>
                <div class="col-lg-9 pt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="aktif"
                            <?php if (@$main == '' || @$main['status'] == 'aktif') echo 'checked'; ?>>
                        <span class="form-check-label">Aktif</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="dibatalkan"
                            <?php if (@$main['status'] == 'dibatalkan') echo 'checked'; ?>>
                        <span class="form-check-label">Dibatalkan</span>
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="row mt-3">
            <div class="col-lg-9 offset-lg-3">
                <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i>
                    Simpan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>
