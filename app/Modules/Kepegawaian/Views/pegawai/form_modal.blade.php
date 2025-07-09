<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
  <div class="card-body">
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label required">Pegawai Id</label>
      <div class="col-lg-4">
        <input type="text" name="pegawai_id" class="form-control" value="<?= @$main['pegawai_id'] ?>" <?= @$main ? 'required' : '' ?>
        >
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label required">Nama Lengkap</label>
      <div class="col-lg-8 col-md-6">
        <input type="text" name="pegawai_nm" class="form-control" value="<?= @$main['pegawai_nm'] ?>" required>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label required">Jenis Kelamin</label>
      <div class="col-lg-8 col-md-6">
        <label class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="sex_id" value="L" <?= (@$main == '') ? 'checked' : ((@$main['sex_id'] == 'L') ? 'checked' : '') ?>>
          <span class="form-check-label">Laki - laki</span>
        </label>
        <label class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="sex_id" value="P" <?= (@$main != '') ? ((@$main['sex_id'] == 'P') ? 'checked' : '') : '' ?>>
          <span class="form-check-label">Perempuan</span>
        </label>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">TTL</label>
      <div class="col-lg-4 col-md-6">
        <input type="text" name="lahir_tmp" class="form-control" value="<?= @$main['lahir_tmp'] ?>">
      </div>
      <div class="col-lg-4 col-md-6">
        <input type="text" name="lahir_tgl" class="form-control datepicker-notauto" value="<?= @to_date($main['lahir_tgl'], '-', 'date') ?>">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">NIK</label>
      <div class="col-lg-4 col-md-6">
        <input type="text" name="nik" class="form-control" value="<?= @$main['nik'] ?>">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">NPWP</label>
      <div class="col-lg-4 col-md-6">
        <input type="text" name="npwp" class="form-control" value="<?= @$main['npwp'] ?>">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">Pendidikan</label>
      <div class="col-lg-6 col-md-6">
        <select class="form-select chosen-select" name="pendidikan_id">
          <option value="">- None -</option>
          <?php foreach ($all_pendidikan as $r) : ?>
            <option value="<?= $r['pendidikan_id'] ?>" <?= (@$main['pendidikan_id'] == $r['pendidikan_id']) ? 'selected' : '' ?>><?= $r['pendidikan_id'] ?> - <?= $r['pendidikan_nm'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">No. Telepon</label>
      <div class="col-lg-3 col-md-6">
        <input type="text" name="telp_no" class="form-control" value="<?= @$main['telp_no'] ?>">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">Email</label>
      <div class="col-lg-4 col-md-6">
        <input type="email" name="email" class="form-control" value="<?= @$main['email'] ?>">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">Alamat</label>
      <div class="col-lg-8 col-md-6">
        <textarea type="text" name="alamat" class="form-control" rows="3"><?= @$main['alamat'] ?></textarea>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">Tanda tangan</label>
      <div class="col-lg-4 col-md-6">
        <input type="file" name="ttd" class="form-control" value="">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label"></label>
      <div class="col-lg-6 col-md-6">
        <img src="<?= @$main['ttd'] ?>" alt="ttd" class="img-thumbnail" width="150">
      </div>
    </div>
    <div class="border-dotted my-2"></div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">TMT</label>
      <div class="col-lg-3 col-md-6">
        <input type="text" name="pegawai_tmt" class="form-control datepicker-notauto" value="<?= @to_date($main['pegawai_tmt'], '-', 'date') ?>">
      </div>
      <label class="col-lg-2 col-md-6 col-form-label">TAT</label>
      <div class="col-lg-3 col-md-6">
        <input type="text" name="pegawai_tat" class="form-control datepicker-notauto" value="<?= @to_date($main['pegawai_tat'], '-', 'date') ?>">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">Divisi</label>
      <div class="col-lg-6 col-md-6">
        <select class="form-select chosen-select" name="divisi_id">
          <option value="">- None -</option>
          <?php foreach ($all_divisi as $r) : ?>
            <option value="<?= $r['divisi_id'] ?>" <?= (@$main['divisi_id'] == $r['divisi_id']) ? 'selected' : '' ?>><?= $r['divisi_id'] ?> - <?= $r['divisi_nm'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label">Jabatan</label>
      <div class="col-lg-6 col-md-6">
        <select class="form-select chosen-select" name="jabatan_id">
          <option value="">- None -</option>
          <?php foreach ($all_jabatan as $r) : ?>
            <option value="<?= $r['jabatan_id'] ?>" <?= (@$main['jabatan_id'] == $r['jabatan_id']) ? 'selected' : '' ?>><?= $r['jabatan_id'] ?> - <?= $r['jabatan_nm'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label required">SPKL</label>
      <div class="col-lg-8 col-md-6">
        <label class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="spkl_st" value="1" <?= (@$main == '') ? 'checked' : ((@$main['spkl_st'] == 1) ? 'checked' : '') ?>>
          <span class="form-check-label">Ya</span>
        </label>
        <label class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="spkl_st" value="0" <?= (@$main != '') ? ((@$main['spkl_st'] == 0) ? 'checked' : '') : '' ?>>
          <span class="form-check-label">Tidak</span>
        </label>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-2 col-md-6 col-form-label required">Aktif?</label>
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
      <div class="col-10 offset-2">
        <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Batal</button>
      </div>
    </div>
  </div>
</form>