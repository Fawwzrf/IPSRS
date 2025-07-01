<div class="page-wrapper">
  <div class="page-header d-print-none mt-2">
    <div class="container-xl">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            <?= $nav['nav_nm'] ?>
          </div>
          <h2 class="page-title">
            <?= $title ?>
          </h2>
        </div>
      </div>
    </div>
  </div>
  <div class="page-wrapper">
    <div class="page-body mt-2">
      <div class="container-xl">
        <form id="form" action="<?= $form_act ?>" method="post" autocomplete="off" onsubmit="_save(event)" enctype="multipart/form-data">
          <div class="row">
            <div class="col-lg-12 col-md-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-building"></i> Identitas Perusahaan</h3>
                </div>
                <div class="card-body">
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label required">Nama Aplikasi</label>
                    <div class="col-lg-6 col-md-6">
                      <input type="text" name="aplikasi_nm" class="form-control" value="<?= @$main['aplikasi_nm'] ?>" required>
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label required">Singkatan Aplikasi</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="text" name="aplikasi_singkatan" class="form-control" value="<?= @$main['aplikasi_singkatan'] ?>" required>
                    </div>
                  </div>
                  <div class="border-dotted mb-2"></div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label required">Nama Perusahaan</label>
                    <div class="col-lg-6 col-md-6">
                      <input type="text" name="perusahaan_nm" class="form-control" value="<?= @$main['perusahaan_nm'] ?>" required>
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label required">Kepala</label>
                    <div class="col-lg-4 col-md-6">
                      <select class="form-select chosen-select" name="kepala_id">
                        <option value="">- None -</option>
                        <?php foreach ($all_pegawai as $r) : ?>
                          <option value="<?= $r['pegawai_id'] ?>" <?= (@$main['kepala_id'] == $r['pegawai_id']) ? 'selected' : '' ?>><?= $r['pegawai_id'] ?> - <?= $r['pegawai_nm'] ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="border-dotted mb-2"></div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Telepon</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="text" name="telp_no" class="form-control" value="<?= @$main['telp_no'] ?>">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Fax</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="text" name="fax_no" class="form-control" value="<?= @$main['fax_no'] ?>">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Email</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="text" name="email" class="form-control" value="<?= @$main['email'] ?>">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Website</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="text" name="website" class="form-control" value="<?= @$main['website'] ?>">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Alamat Lengkap</label>
                    <div class="col-lg-8 col-md-6">
                      <input type="text" name="alamat" class="form-control" value="<?= @$main['alamat'] ?>">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Kode Pos</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="text" name="kode_pos" class="form-control" value="<?= @$main['kode_pos'] ?>">
                    </div>
                  </div>
                  <div class="border-dotted mb-2"></div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Logo</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="file" name="logo" class="form-control" value="">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label"></label>
                    <div class="col-lg-2 col-md-6">
                      <img src="<?= @$main['logo'] ?>" alt="logo" class="img-thumbnail" width="150">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Photo</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="file" name="photo" class="form-control" value="">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label"></label>
                    <div class="col-lg-2 col-md-6">
                      <img src="<?= @$main['photo'] ?>" alt="photo" class="img-thumbnail" width="150">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Latar Belakang</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="file" name="background" class="form-control" value="">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label"></label>
                    <div class="col-lg-2 col-md-6">
                      <img src="<?= @$main['background'] ?>" alt="background" class="img-thumbnail" width="150">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label">Kop Surat</label>
                    <div class="col-lg-3 col-md-6">
                      <input type="file" name="kop_surat" class="form-control" value="">
                    </div>
                  </div>
                  <div class="mb-1 row">
                    <label class="col-lg-2 col-md-6 col-form-label"></label>
                    <div class="col-lg-2 col-md-6">
                      <img src="<?= @$main['kop_surat'] ?>" alt="kop_surat" class="img-thumbnail" width="150">
                    </div>
                  </div>
                </div>
              </div>
              <div style="margin-bottom:80px"></div>
              <div class="fixed-bottom row">
                <div class="card">
                  <div class="card-body py-2">
                    <div class="text-end">
                      <button type="button" class="btn btn-default" onclick="_page('<?= $uri ?>')"><i class="fas fa-times me-2"></i> Batal</button>
                      <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>