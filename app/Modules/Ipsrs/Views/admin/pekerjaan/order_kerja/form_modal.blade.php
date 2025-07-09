<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Order Kerja</label>
            <div class="col-lg-4">
                <input type="text" name="order_kerja_id" id="order_kerja_id" class="form-control" value="<?= @$main['order_kerja_id'] ?>" <?= (@$main['order_kerja_id']) ? 'readonly' : '' ?> required>
                <small class="form-hint">ID order kerja harus unik. Contoh: OKM001 atau OKR001</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Sumber Order</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="jadwal_pm_id" id="jadwal_pm_id">
                    <option value="">- Pilih Jadwal PM -</option>
                    <?php foreach($all_jadwal_pm as $jp) : ?>
                        <option value="<?= $jp['jadwal_pm_id'] ?>" <?= (@$main['jadwal_pm_id'] == $jp['jadwal_pm_id']) ? 'selected' : '' ?>>
                            <?= $jp['jadwal_pm_id'] ?> - Aset: <?= @$jp['asset_nm'] ?> (Jenis: <?= @$jp['jenis'] ?>, Freq: <?= @$jp['frekuensi'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                {{-- Anda bisa menambahkan dropdown untuk permintaan_id di sini juga, dan atur validasi JavaScript --}}
                {{-- <select class="form-select chosen-select" name="permintaan_id" id="permintaan_id"> --}}
                    {{-- ... --}}
                {{-- </select> --}}
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Tanggal Dibuat</label>
            <div class="col-lg-4">
                <input type="text" name="tgl_dibuat" id="tgl_dibuat" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_dibuat'], '-', 'date') ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Target Selesai</label>
            <div class="col-lg-4">
                <input type="text" name="tgl_target_selesai" id="tgl_target_selesai" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_target_selesai'], '-', 'date') ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Estimasi Biaya (Rp)</label>
            <div class="col-lg-4">
                <input type="text" name="estimasi_biaya" id="estimasi_biaya" class="form-control autonumeric" value="<?= @$main['estimasi_biaya'] ?>">
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Prioritas</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="prioritas" id="prioritas" required>
                    <option value="">- Pilih Prioritas -</option>
                    <option value="darurat" <?= (@$main['prioritas'] == 'darurat') ? 'selected' : '' ?>>Darurat</option>
                    <option value="mendesak" <?= (@$main['prioritas'] == 'mendesak') ? 'selected' : '' ?>>Mendesak</option>
                    <option value="normal" <?= (@$main['prioritas'] == 'normal') ? 'selected' : '' ?>>Normal</option>
                    <option value="rendah" <?= (@$main['prioritas'] == 'rendah') ? 'selected' : '' ?>>Rendah</option>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Status Order</label>
            <div class="col-lg-8">
                <select class="form-select chosen-select" name="status" id="status" required>
                    <option value="">- Pilih Status -</option>
                    <option value="baru" <?= (@$main['status'] == 'baru') ? 'selected' : '' ?>>Baru</option>
                    <option value="ditugaskan" <?= (@$main['status'] == 'ditugaskan') ? 'selected' : '' ?>>Ditugaskan</option>
                    <option value="diproses" <?= (@$main['status'] == 'diproses') ? 'selected' : '' ?>>Diproses</option>
                    <option value="menunggu_sparepart" <?= (@$main['status'] == 'menunggu_sparepart') ? 'selected' : '' ?>>Menunggu Sparepart</option>
                    <option value="selesai" <?= (@$main['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= (@$main['status'] == 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                    <option value="dibatalkan" <?= (@$main['status'] == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
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