<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off" enctype="multipart/form-data"> {{-- Tambahkan enctype --}}
    @csrf {{-- Pastikan CSRF token ada --}}
    <div class="card-body">
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Tipe Lokasi</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="tipe_lokasi" id="tipe_lokasi" required>
                    <option value="">- Pilih Tipe -</option>
                    <option value="Gedung" <?= (@$main['tipe_lokasi'] == 'Gedung') ? 'selected' : '' ?>>Gedung</option>
                    <option value="Lantai" <?= (@$main['tipe_lokasi'] == 'Lantai') ? 'selected' : '' ?>>Lantai</option>
                    <option value="Ruangan" <?= (@$main['tipe_lokasi'] == 'Ruangan') ? 'selected' : '' ?>>Ruangan</option>
                </select>
            </div>
        </div>
        <div class="mb-1 row" id="div_parent_lokasi">
            <label class="col-lg-3 col-md-6 col-form-label">Lokasi Induk</label>
            <div class="col-lg-8 col-md-6">
                <select class="form-select chosen-select" name="parent_lokasi_id" id="parent_lokasi">
                    <option value="">- None -</option>
                    <?php
                    foreach ($all_parent_lokasi as $par) :
                        $selected = (@$main['parent_lokasi_id'] == $par['lokasi_id']) ? 'selected' : '';
                        $disabled = ($par['lokasi_id'] == @$main['lokasi_id']) ? 'disabled' : ''; // Tidak bisa jadi parent dirinya sendiri
                    ?>
                        <option value="<?= $par['lokasi_id'] ?>" <?= $selected ?> <?= $disabled ?>>
                            <?= $par['lokasi_id'] ?> - <?= $par['lokasi_nm'] ?> (<?= $par['tipe_lokasi'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">ID Lokasi</label>
            <div class="col-lg-4">
                {{-- ID Lokasi sekarang selalu diisi manual/dari sistem --}}
                <input type="text" name="lokasi_id" id="lokasi_id" class="form-control" value="<?= @$main['lokasi_id'] ?>" <?= (@$main['lokasi_id'])?> required>
                <small class="form-hint">ID lokasi harus diisi secara unik. Contoh: 01, 01.01, 01.01.01</small>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Nama Lokasi</label>
            <div class="col-lg-8 col-md-6">
                <input type="text" name="lokasi_nm" class="form-control" value="<?= @$main['lokasi_nm'] ?>" required>
            </div>
        </div>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Deskripsi</label>
            <div class="col-lg-8 col-md-6">
                <textarea name="deskripsi" class="form-control"><?= @$main['deskripsi'] ?></textarea>
            </div>
        </div>
        {{-- Input FILE denah_url --}}
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label">Denah Lokasi</label>
            <div class="col-lg-4 col-md-6">
                <input type="file" name="denah_url" class="form-control" accept="image/*">
            </div>
        </div>
        <?php if (!empty(@$main['denah_url'])) : ?>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label"></label>
            <div class="col-lg-6 col-md-6">
                <img src="<?= @$main['denah_url'] ?>" alt="Denah Lokasi" class="img-thumbnail" width="150">
            </div>
        </div>
        <?php endif; ?>
        <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">Aktif?</label>
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