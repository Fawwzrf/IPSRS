@include('kepegawaian::pegawai._js')
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
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-lg', position: 'normal'})" class="btn btn-primary d-sm-inline-block"><i class="fas fa-plus"></i> Tambah Baru</a>
          </div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
          <div class="card mb-1">
            <div class="accordion" id="accordion-example">
              <div class="accordion-item-disabled">
                <div id="filter" class="accordion-collapse collapse show" data-bs-parent="#accordion-example">
                  <div class="accordion-body bg-white p-2">
                    <form class="mb-0" id="search" action="<?= $search_act ?>" method="post" autocomplete="off" onsubmit="_search(event)">
                      <div class="row">
                        <div class="col-lg-2">
                          <label class="form-label">Divisi</label>
                          <select class="form-select chosen-select" id="divisi_id" name="divisi_id" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($all_divisi as $r) : ?>
                              <option value="<?= $r['divisi_id'] ?>" <?= (@$nav_sess['search']['data']['divisi_id'] == $r['divisi_id']) ? 'selected' : '' ?>><?= $r['divisi_id'] ?> - <?= $r['divisi_nm'] ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-lg-2">
                          <label class="form-label">Jabatan</label>
                          <select class="form-select chosen-select" id="jabatan_id" name="jabatan_id" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($all_jabatan as $r) : ?>
                              <option value="<?= $r['jabatan_id'] ?>" <?= (@$nav_sess['search']['data']['jabatan_id'] == $r['jabatan_id']) ? 'selected' : '' ?>><?= $r['jabatan_id'] ?> - <?= $r['jabatan_nm'] ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-lg-2">
                          <label class="form-label">Status</label>
                          <select class="form-select chosen-select" id="active_st" name="active_st" required>
                            <option value="">-- Pilih --</option>
                            <option value="1" <?= '1' == @$nav_sess['search']['data']['active_st'] ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= '0' == @$nav_sess['search']['data']['active_st'] ? 'selected' : '' ?>>Tidak Aktif</option>
                          </select>
                        </div>
                        <div class="col-lg-3">
                          <label class="form-label">Pencarian</label>
                          <input class="form-control" type="text" id="term" name="term" value="<?= @$nav_sess['search']['data']['term'] ?>">
                        </div>
                        <div class="col-lg-2">
                          <div class="input-group mt-4">
                            <button class="btn" type="submit" onclick="_search(event)"><i class="fas fa-search"></i>&nbsp;&nbsp;Cari</button>
                            <button class="btn" type="button" onclick="_searchReset()"><i class="fas fa-times"></i>&nbsp;&nbsp;Batal</button>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="page-wrapper">
    <div class="page-body mt-1">
      <div class="container-xl">
        <div class="row">
          <div class="col">
            <div class="card p-2">
              <div class="w-100">
                <div class="table-responsive">
                  <table class="table table-vcenter card-table table-sm display nowrap" id="datatable-main">
                    <thead>
                      <tr>
                        <th width="20">No</th>
                        <th width="50">Aksi</th>
                        <th width="90">Pegawai Id</th>
                        <th width="">Nama Lengkap</th>
                        <th width="30">JK</th>
                        <th width="120">Divisi</th>
                        <th width="150">Jabatan</th>
                        <th width="80">TMT</th>
                        <th width="80">TAT</th>
                        <th width="80">Masa Kerja</th>
                        <th width="40">SPKL</th>
                        <th width="40">Status</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>