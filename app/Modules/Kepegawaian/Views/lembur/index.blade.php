@include('kepegawaian::lembur._js')
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
            <?php if ($nav['all_data_st'] == 1) : ?>
              <a href="<?= $uri ?>/export_excel/semua?n=<?= $nav_id ?>" target="_blank" class="btn btn-green d-sm-inline-block"><i class="fas fa-file-excel"></i> Excel Semua</a>
              <a href="<?= $uri ?>/export_excel/durasi?n=<?= $nav_id ?>" target="_blank" class="btn btn-green d-sm-inline-block"><i class="fas fa-file-excel"></i> Excel Durasi</a>
              <a href="<?= $uri ?>/export_excel/verifikasi?n=<?= $nav_id ?>" target="_blank" class="btn btn-green d-sm-inline-block"><i class="fas fa-file-excel"></i> Excel Verifikasi</a>
            <?php endif; ?>
            <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-xl', position: 'normal'})" class="btn btn-primary d-sm-inline-block"><i class="fas fa-plus"></i> Tambah Baru</a>
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
                          <label class="form-label">Bulan</label>
                          <select class="form-select chosen-select" id="bulan" name="bulan" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach (list_bulan() as $k => $r) : ?>
                              <option value="<?= $k ?>" <?= $k == $bulan ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-lg-2">
                          <label class="form-label">Tahun</label>
                          <select class="form-select chosen-select" id="tahun" name="tahun" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach (list_tahun(2024, date('Y'), 'desc') as $r) : ?>
                              <option value="<?= $r ?>" <?= $r == $tahun ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <?php if ($nav['all_data_st'] == 1) : ?>
                          <div class="col-lg-3">
                            <label class="form-label">Pegawai</label>
                            <?= _frm_select('pegawai_id', $all_pegawai, 'pegawai_id', 'pegawai_nm', @$nav_sess['search']['data']['pegawai_id'], '-- Pilih --', 'class="form-select chosen-select" id="pegawai_id" required') ?>
                          </div>
                        <?php endif; ?>
                        <div class="col-lg-2">
                          <div class="input-group mt-4">
                            <button class="btn" type="submit" onclick="_search(event)"><i class="fas fa-search"></i>&nbsp;&nbsp;Cari</button>
                            <button class="btn" type="button" onclick="_searchReset('<?= $nav_id ?>')"><i class="fas fa-times"></i>&nbsp;&nbsp;Batal</button>
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
            <div class="card">
              <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tabdata">
                  <li class="nav-item" role="presentation">
                    <a href="#tab-data" id="nav-data" class="nav-link active" data-bs-toggle="tab" aria-selected="true" role="tab"><i class="fas fa-list me-2"></i>Data</a>
                  </li>
                  <?php if ($nav['all_data_st'] == 1) : ?>
                    <li class="nav-item" role="presentation">
                      <a href="#tab-verifikasi" id="nav-verifikasi" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab" tabindex="-1"><i class="fas fa-check me-2"></i>Verifikasi</a>
                    </li>
                  <?php endif; ?>
                  <?php if ($nav['all_data_st'] == 1) : ?>
                    <li class="nav-item" role="presentation">
                      <a href="#tab-pegawai" id="nav-pegawai" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab" tabindex="-1"><i class="fas fa-users me-2"></i>Pegawai</a>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
              <div class="card-body p-2">
                <div class="tab-content">
                  <div class="tab-pane active show" id="tab-data" role="tabpanel">
                    @include('kepegawaian::lembur.informasi')
                    <div class="w-100">
                      <div class="table-responsive">
                        <table class="table table-vcenter card-table table-striped table-sm display nowrap" id="datatable-main">
                          <thead>
                            <tr>
                              <th width="20">No</th>
                              <th width="60">Aksi</th>
                              <th width="80">Id</th>
                              <th width="250">Pegawai</th>
                              <th width="50">Hari</th>
                              <th width="120">Mulai</th>
                              <th width="120">Selesai</th>
                              <th width="50">Durasi</th>
                              <th>Uraian</th>
                              <th width="80">Gambar</th>
                              <th width="100">Created At</th>
                            </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <?php if ($nav['all_data_st'] == 1) : ?>
                    <div class="tab-pane" id="tab-verifikasi" role="tabpanel">
                      <?php if (@$nav_sess['search']['data']['pegawai_id'] == "") : ?>
                        <div class="alert alert-danger">
                          <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Pilih pegawai terlebih dulu!</h4>
                        </div>
                      <?php else : ?>
                        <div class="w-100">
                          <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped table-sm display nowrap" id="datatable-verifikasi">
                              <thead>
                                <tr>
                                  <th width="20">No</th>
                                  <th width="70">Aksi</th>
                                  <th width="80">Id</th>
                                  <th width="250">Pegawai</th>
                                  <th width="50">Hari</th>
                                  <th width="120">Mulai</th>
                                  <th width="120">Selesai</th>
                                  <th width="50">Durasi</th>
                                  <th width="50">Verifikasi</th>
                                  <th>Uraian</th>
                                  <th width="80">Gambar</th>
                                  <th width="100">Created At</th>
                                </tr>
                              </thead>
                              <tbody></tbody>
                              <tfoot>
                                <tr>
                                  <th colspan="6"></th>
                                  <th class="text-center">Total:</th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                </tr>
                              </tfoot>
                            </table>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="tab-pane" id="tab-pegawai" role="tabpanel">
                      <div class="w-100">
                        <div class="table-responsive">
                          <table class="table table-vcenter card-table table-striped table-sm display nowrap" id="datatable-pegawai-rekap">
                            <thead>
                              <tr>
                                <th width="20">No</th>
                                <th width="100">Pegawai Id</th>
                                <th width="">Nama Pegawai</th>
                                <th width="200">Jabatan</th>
                                <th width="200">Durasi</th>
                                <th width="200">Verifikasi Durasi</th>
                              </tr>
                            </thead>
                            <tbody></tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>