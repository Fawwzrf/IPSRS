<?php include(view_path('ipsrs::admin.pekerjaan.jadwal_pm._js')); ?>
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
            <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-lg', position: 'normal'})" class="btn btn-primary d-sm-inline-block">
              <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
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
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="search_act" value="save">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <label class="form-label">Filter Aset</label>
                                            <select class="form-select chosen-select" id="asset_id" name="asset_id">
                                                <option value="">-- Semua Aset --</option>
                                                <?php foreach($all_asset as $asset): ?>
                                                    <option value="<?= $asset['asset_id'] ?>" <?= (@$nav_sess['search']['data']['asset_id'] == $asset['asset_id']) ? 'selected' : '' ?>>
                                                        <?= $asset['asset_nm'] ?> (<?= $asset['no_seri'] ?? $asset['asset_id'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Filter Status</label>
                                            <select class="form-select chosen-select" id="status" name="status">
                                                <option value="">-- Semua Status --</option>
                                                <option value="aktif" <?= (@$nav_sess['search']['data']['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                                <option value="diproses" <?= (@$nav_sess['search']['data']['status'] == 'diproses') ? 'selected' : '' ?>>Diproses</option>
                                                <option value="selesai" <?= (@$nav_sess['search']['data']['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                                                <option value="dibatalkan" <?= (@$nav_sess['search']['data']['status'] == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Pencarian</label>
                                            <input class="form-control" type="text" id="term" name="term" value="<?= @$nav_sess['search']['data']['term'] ?>">
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="input-group mt-4">
                                              <button class="btn" type="submit" onclick="_search(event)"><i class="fas fa-search"></i>&nbsp;&nbsp;Cari</button>
                                              <button class="btn" type="button" onclick="_searchReset()"><i class="fas fa-times"></i>&nbsp;Reset</button>
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
  <div class="page-body mt-1">
    <div class="container-xl">
      <div class="row">
        <div class="col">
          <div class="card p-2">
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-striped table-sm" id="datatable-main">
                <thead>
                  <tr>
                    <th width="5%">No</th>
                    <th width="7%">Aksi</th>
                    <th width="15%">Nama Aset</th>
                    <th width="10%">Frekuensi</th>
                    <th width="15%">Jenis Pekerjaan</th>
                    <th width="10%">Pemeliharaan Terakhir</th>
                    <th width="10%">Pemeliharaan Berikutnya</th>
                    <th width="10%">Status</th>
                    <th width="5%">Aktif?</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>