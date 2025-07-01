<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
  <div class="card-body">
    <div class="row">
      <div class="col-6">
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Lembur Id</label>
          <div class="col-lg-4">
            <input type="text" name="lembur_id" class="form-control" value="<?= @$main['lembur_id'] ?>" readonly>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Pegawai</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" class="form-control" value="<?= (@$main) ? @$pegawai['pegawai_nm'] : _ses_get('pegawai_nm') ?>" readonly>
            <input type="hidden" value="<?= (@$main) ? @$pegawai['pegawai_id'] : _ses_get('pegawai_id') ?>">
          </div>
        </div>
        <?php if ($pegawai['spkl_st'] == 1) : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">SPKL</label>
            <div class="col-lg-9 col-md-6">
              <select class="form-select chosen-select" disabled>
                <option value="">-- Pilih --</option>
                <?php foreach ($all_spkl as $r) : ?>
                  <option value="<?= $r['spkl_id'] ?>" <?= (@$main['spkl_id'] == $r['spkl_id']) ? 'selected' : '' ?>><?= $r['spkl_id'] ?> - <?= $r['pekerjaan'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        <?php endif; ?>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Mulai</label>
          <div class="col-lg-4">
            <input type="text" id="mulai_tgl" class="form-control waktu" value="<?= @to_date($main['mulai_tgl'], '-', 'full_date') ?>" readonly>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Selesai</label>
          <div class="col-lg-4">
            <input type="text" id="selesai_tgl" class="form-control waktu" value="<?= @to_date($main['selesai_tgl'], '-', 'full_date') ?>" readonly>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Durasi</label>
          <div class="col-lg-4">
            <div class="input-group">
              <input type="text" id="durasi" class="form-control text-center" value="<?= @$main['durasi'] ?>" readonly>
              <span class="input-group-text">Jam</span>
            </div>
          </div>
        </div>
        <div class="border-dotted my-2"></div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Verifikasi Durasi</label>
          <div class="col-lg-4">
            <div class="input-group">
              <input type="text" name="verifikasi_durasi" id="verifikasi_durasi" class="form-control text-center" value="<?= @$main['verifikasi_durasi'] ?>">
              <span class="input-group-text">Jam</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Uraian</label>
          <div class="col-lg-9 col-md-6">
            <textarea class="form-control" id="uraian" rows="5" required readonly><?= @$main['uraian'] ?></textarea>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Link Commit Github</label>
          <div class="col-lg-9 col-md-6">
            <textarea class="form-control" id="link" rows="5" readonly><?= @$main['link'] ?></textarea>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Gambar 1</label>
        </div>
        <?php if (@$main['gambar_1'] != "") : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label"></label>
            <div class="col-lg-6 col-md-6">
              <a href="<?= site_url('app/get_file/lembur/' . @$main['gambar_1']) ?>" target="_blank">
                <img src="<?= site_url('app/get_file/lembur/' . @$main['gambar_1']) ?>" alt="Gambar 1" class="img-thumbnail" style="height:150px !important">
              </a>
            </div>
          </div>
        <?php endif; ?>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Gambar 2</label>
        </div>
        <?php if (@$main['gambar_2'] != "") : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label"></label>
            <div class="col-lg-6 col-md-6">
              <a href="">
                <img src="<?= site_url('app/get_file/lembur/' . @$main['gambar_2']) ?>" alt="Gambar 2" class="img-thumbnail" style="height:150px !important">
              </a>
            </div>
          </div>
        <?php endif; ?>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Gambar 3</label>
        </div>
        <?php if (@$main['gambar_3'] != "") : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label"></label>
            <div class="col-lg-6 col-md-6">
              <a href="">
                <img src="<?= site_url('app/get_file/lembur/' . @$main['gambar_3']) ?>" alt="Gambar 3" class="img-thumbnail" style="height:150px !important">
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="border-dotted"></div>
    <div class="row mt-2">
      <div class="col-12">
        <div class="float-end">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal"><?= _icon('cancel') ?> Batal</button>
          <button type="submit" class="btn btn-primary" onclick="_save(event)"><?= _icon('save') ?> Simpan</button>
        </div>
      </div>
    </div>
  </div>
</form>