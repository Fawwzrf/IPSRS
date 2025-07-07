<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Log Kerja</label>
            <div class="col-lg-4">
                <input type="text" name="log_kerja_id" id="log_kerja_id" class="form-control" value="<?= @$main['log_kerja_id'] ?>" <?= (@$main['log_kerja_id']) ? 'readonly' : '' ?> required>
                <small class="form-hint">ID log kerja harus unik. Contoh: LK001</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Order Kerja</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="order_kerja_id" id="order_kerja_id" required>
                    <option value="">- Pilih Order Kerja -</option>
                    <?php foreach($all_orders as $ok) : ?>
                        <option value="<?= $ok['order_kerja_id'] ?>" <?= (@$main['order_kerja_id'] == $ok['order_kerja_id']) ? 'selected' : '' ?>>
                            <?= $ok['order_kerja_id'] ?> (<?= $ok['jenis']?>) - Aset: <?= @$ok['asset_nm'] ?> (Lokasi: <?= @$ok['asset_lokasi_nm'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Teknisi Pelaksana</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="pegawai_id" id="pegawai_id">
                    <option value="">- Pilih Teknisi -</option>
                    <?php foreach($all_pegawai as $pgw) : ?>
                        <option value="<?= $pgw['pegawai_id'] ?>" <?= (@$main['teknisi_pegawai_id'] == $pgw['pegawai_id']) ? 'selected' : '' ?>>
                            <?= $pgw['pegawai_nm'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Tanggal Mulai</label>
            <div class="col-lg-4">
                <input type="text" name="tgl_mulai_input" id="tgl_mulai_input" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_mulai'], '-', 'date') ?>" required>
            </div>
            {{-- HAPUS: Jam Mulai --}}
            {{-- <label class="col-lg-2 col-md-6 col-form-label">Jam Mulai</label>
            <div class="col-lg-3">
                <input type="text" name="jam_mulai_input" id="jam_mulai_input" class="form-control timepicker" value="<?= @to_time(@$main['tgl_mulai'], ':', 'hour_minute') ?>">
            </div> --}}
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Tanggal Selesai</label>
            <div class="col-lg-4">
                <input type="text" name="tgl_selesai_input" id="tgl_selesai_input" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_selesai'], '-', 'date') ?>">
            </div>
            {{-- HAPUS: Jam Selesai --}}
            {{-- <label class="col-lg-2 col-md-6 col-form-label">Jam Selesai</label>
            <div class="col-lg-3">
                <input type="text" name="jam_selesai_input" id="jam_selesai_input" class="form-control timepicker" value="<?= @to_time(@$main['tgl_selesai'], ':', 'hour_minute') ?>">
            </div> --}}
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Diagnosa</label>
            <div class="col-lg-8">
                <textarea name="diagnosa" id="diagnosa" class="form-control" rows="3"><?= @$main['diagnosa'] ?></textarea>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Tindakan</label>
            <div class="col-lg-8">
                <textarea name="tindakan" id="tindakan" class="form-control" rows="3"><?= @$main['tindakan'] ?></textarea>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Hasil</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="hasil" id="hasil">
                    <option value="">- Pilih Hasil -</option>
                    <option value="berhasil" <?= (@$main['hasil'] == 'berhasil') ? 'selected' : '' ?>>Berhasil</option>
                    <option value="perlu_tindak_lanjut" <?= (@$main['hasil'] == 'perlu_tindak_lanjut') ? 'selected' : '' ?>>Perlu Tindak Lanjut</option>
                    <option value="tidak_berhasil" <?= (@$main['hasil'] == 'tidak_berhasil') ? 'selected' : '' ?>>Tidak Berhasil</option>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Durasi (menit)</label>
            <div class="col-lg-2">
                <input type="number" name="durasi_menit" id="durasi_menit" class="form-control" value="<?= @$main['durasi_menit'] ?>" min="0">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Total Biaya (Rp)</label>
            <div class="col-lg-4">
                <input type="text" name="biaya_total" id="biaya_total" class="form-control autonumeric" value="<?= @$main['total_biaya'] ?>">
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