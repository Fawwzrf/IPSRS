<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off">
  <div class="card-body">
    <div class="row">
      <div class="col-lg-6 col-md-12">
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Id</label>
          <div class="col-lg-4">
            <input type="text" name="spkl_id" class="form-control" value="<?= @$main['spkl_id'] ?>" readonly>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Tanggal Mulai</label>
          <div class="col-lg-4 col-md-6">
            <input type="text" name="mulai_tgl" class="form-control datepicker" value="<?= @to_date($main['mulai_tgl']) ?>" required>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Tanggal Selesai</label>
          <div class="col-lg-4 col-md-6">
            <input type="text" name="selesai_tgl" class="form-control datepicker" value="<?= @to_date($main['selesai_tgl']) ?>" required>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Jam Mulai</label>
          <div class="col-lg-4 col-md-6">
            <input type="text" name="mulai_jam" class="form-control timepicker" value="<?= @$main['mulai_jam'] ?>">
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Jam Selesai</label>
          <div class="col-lg-4 col-md-6">
            <input type="text" name="selesai_jam" class="form-control timepicker" value="<?= @$main['selesai_jam'] ?>">
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Durasi</label>
          <div class="col-lg-4 col-md-6">
            <input type="text" name="durasi" id="durasi" class="form-control" value="<?= @$main['durasi'] ?>">
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Lokasi</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" name="lokasi" class="form-control" value="<?= @$main['lokasi'] ?>" required>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Pekerjaan</label>
          <div class="col-lg-9 col-md-6">
            <textarea class="form-control" name="pekerjaan" id="pekerjaan" rows="5" required><?= @$main['pekerjaan'] ?></textarea>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Keterangan</label>
          <div class="col-lg-9 col-md-6">
            <textarea class="form-control" name="keterangan" id="keterangan" rows="3"><?= @$main['keterangan'] ?></textarea>
          </div>
        </div>
        <div class="border-dotted my-2"></div>
        <h3 class="mb-0"><i class="fas fa-copy me-2"></i>Berkas Terkait</h3>
        <h5 class="mb-1 mt-2 text-primary">Berkas 1</h5>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Berkas 1 Judul</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" name="berkas_1_judul" class="form-control" value="<?= @$main['berkas_1_judul'] ?>">
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Berkas 1 Url</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" name="berkas_1_url" class="form-control" value="<?= @$main['berkas_1_url'] ?>">
          </div>
        </div>
        <div class="border-dotted my-2"></div>
        <h5 class="mb-1 mt-2 text-primary">Berkas 2</h5>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Berkas 2 Judul</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" name="berkas_2_judul" class="form-control" value="<?= @$main['berkas_2_judul'] ?>">
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Berkas 2 Url</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" name="berkas_2_url" class="form-control" value="<?= @$main['berkas_2_url'] ?>">
          </div>
        </div>
        <div class="border-dotted my-2"></div>
        <h5 class="mb-1 mt-2 text-primary">Berkas 3</h5>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Berkas 3 Judul</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" name="berkas_3_judul" class="form-control" value="<?= @$main['berkas_3_judul'] ?>">
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Berkas 3 Url</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" name="berkas_3_url" class="form-control" value="<?= @$main['berkas_3_url'] ?>">
          </div>
        </div>

      </div>
      <div class="col-lg-6 col-md-12">
        <h3 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Pegawai</h3>
        <div class="border-dotted my-2"></div>
        <?php for ($i = 0; $i < 10; $i++) : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label <?= ($i == 0) ? 'required' : '' ?>">Pegawai <?= $i + 1 ?></label>
            <div class="col-lg-9 col-md-6">
              <select class="form-select chosen-select" name="pegawai_id[<?= $i ?>]" <?= ($i == 0) ? 'required' : '' ?>>
                <option value="">-- Pilih Pegawai --</option>
                <?php foreach ($all_pegawai as $r) : ?>
                  <option value="<?= $r['pegawai_id'] ?>" <?= (@$all_spkl_pegawai[$i]['pegawai_id'] == $r['pegawai_id']) ? 'selected' : '' ?>><?= $r['pegawai_nm'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        <?php endfor; ?>
        <div class="border-dotted my-2"></div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Pembuat</label>
          <div class="col-lg-9 col-md-6">
            <select class="form-select chosen-select" name="pembuat_id" required>
              <option value="">-- Pilih Pegawai --</option>
              <?php foreach ($all_pegawai as $r) : ?>
                <option value="<?= $r['pegawai_id'] ?>" <?= (@$main) ? ($r['pegawai_id'] == @$main['pembuat_id'] ? 'selected' : '') : ($r['pegawai_id'] == session('pegawai_id') ? 'selected' : '') ?>><?= $r['pegawai_nm'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Pembuat Tgl</label>
          <div class="col-lg-4 col-md-6">
            <input type="text" name="pembuat_tgl" class="form-control datetimepicker" value="<?= (@$main) ? @to_date($main['pembuat_tgl']) : date("d-m-Y H:i:s") ?>" required>
          </div>
        </div>
      </div>
    </div>
    <div class="border-dotted"></div>
    <div class="row mt-2">
      <div class="col">
        <div class="float-end">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Batal</button>
          <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
        </div>
      </div>
    </div>
  </div>
</form>