<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
    <?php echo csrf_field(); ?>
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Sumber & Penugasan</legend>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Sumber dari Jadwal PM</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="jadwal_pm_id" id="jadwal_pm_id">
                        <option value="">- Pilih Jadwal PM (jika ada) -</option>
                        <?php if(isset($all_jadwal_pm) && !empty($all_jadwal_pm)): ?>
                            <?php foreach($all_jadwal_pm as $jadwal): ?>
                                <option value="<?= $jadwal['jadwal_pm_id'] ?>" <?php if(@$main['jadwal_pm_id'] == $jadwal['jadwal_pm_id']) echo 'selected'; ?>>
                                    <?= $jadwal['jadwal_pm_id'] ?> - <?= $jadwal['asset_nm'] ?> (<?= $jadwal['jenis'] ?> - <?= $jadwal['frekuensi'] ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="text-center my-2"><strong>ATAU</strong></div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Sumber dari Komplain</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="permintaan_id" id="permintaan_id">
                        <option value="">- Pilih dari Komplain (jika ada) -</option>
                        <?php foreach($all_komplain as $komplain): ?>
                            <option value="<?= $komplain['permintaan_id'] ?>" <?php if(@$main['permintaan_id'] == $komplain['permintaan_id']) echo 'selected'; ?>>
                                <?= $komplain['permintaan_id'] ?> - <?= $komplain['asset_nm'] ?> (<?= \Illuminate\Support\Str::limit($komplain['deskripsi'], 40) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tugaskan Teknisi</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="pegawai_ids[]" multiple required>
                        <?php foreach($all_teknisi as $teknisi): ?>
                            <option value="<?= $teknisi['pegawai_id'] ?>" <?php if(in_array($teknisi['pegawai_id'], $assigned_teknisi)) echo 'selected'; ?>>
                                <?= $teknisi['pegawai_nm'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>
        
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Pekerjaan</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tanggal Order</label>
                <div class="col-lg-9">
                    <input type="text" name="tgl_dibuat" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_dibuat'], '-', 'date') ?: date('d-m-Y') ?>" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Target Selesai</label>
                <div class="col-lg-9">
                    <input type="text" name="tgl_target_selesai" class="form-control datepicker-notauto" value="<?= @to_date(@$main['tgl_target_selesai'], '-', 'date') ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Prioritas</label>
                <div class="col-lg-9">
                   <select class="form-select chosen-select" name="prioritas" required>
                        <option value="normal" <?php if(strtolower(@$main['prioritas']) == 'normal') echo 'selected'; ?>>Normal</option>
                        <option value="mendesak" <?php if(strtolower(@$main['prioritas']) == 'mendesak') echo 'selected'; ?>>Mendesak</option>
                        <option value="darurat" <?php if(strtolower(@$main['prioritas']) == 'darurat') echo 'selected'; ?>>Darurat</option>
                   </select>
                </div>
            </div>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Estimasi Biaya</label>
                <div class="col-lg-9">
                    <input type="number" step="0.01" name="estimasi_biaya" class="form-control" value="<?= @$main['estimasi_biaya'] ?? 0 ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Catatan</label>
                <div class="col-lg-9">
                    <textarea name="catatan" class="form-control" rows="3"><?= @$main['catatan'] ?></textarea>
                </div>
            </div>

            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Status</label>
                <div class="col-lg-9">
                   <select class="form-select chosen-select" name="status" required>
                        <option value="baru" <?php if(@$main == '' || strtolower(@$main['status']) == 'baru') echo 'selected'; ?>>Baru</option>
                        <option value="diproses" <?php if(strtolower(@$main['status']) == 'diproses') echo 'selected'; ?>>Diproses</option>
                        <option value="selesai" <?php if(strtolower(@$main['status']) == 'selesai') echo 'selected'; ?>>Selesai</option>
                        <option value="ditolak" <?php if(strtolower(@$main['status']) == 'ditolak') echo 'selected'; ?>>Ditolak</option>
                   </select>
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
