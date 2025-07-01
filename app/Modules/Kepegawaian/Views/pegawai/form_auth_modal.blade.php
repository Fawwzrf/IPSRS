<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
  <div class="card-body">
    <div class="mb-1 row">
      <label class="col-lg-3 col-md-6 col-form-label required">Pegawai Id</label>
      <div class="col-lg-4">
        <input type="text" name="pegawai_id" class="form-control" value="<?= @$main['pegawai_id'] ?>" <?= @$main ? 'required' : '' ?> readonly>
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-3 col-md-6 col-form-label required">Nama Lengkap</label>
      <div class="col-lg-8 col-md-6">
        <input type="text" name="pegawai_nm" class="form-control" value="<?= @$main['pegawai_nm'] ?>" readonly>
      </div>
    </div>
    <div class="border-dotted my-2"></div>
    <div class="mb-1 row">
      <label class="col-lg-3 col-md-6 col-form-label required">Username</label>
      <div class="col-lg-8 col-md-6">
        <input type="text" name="user_nm" class="form-control" value="<?= @$main['user_nm'] ?>">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-3 col-md-6 col-form-label required">Password</label>
      <div class="col-lg-8 col-md-6">
        <input type="password" name="password" class="form-control" value="">
      </div>
    </div>
    <div class="mb-1 row">
      <label class="col-lg-3 col-md-6 col-form-label required">Repeat Password</label>
      <div class="col-lg-8 col-md-6">
        <input type="password" name="password_repeat" class="form-control" value="">
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