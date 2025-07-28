@include('ipsrs::admin.pekerjaan.permintaan_komplain._js')
<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <?= $nav['nav_nm'] ?>
                    </div>
                    <h2 class="page-title">
                        Manajemen Permintaan Komplain
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-lg'})" class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-plus"></i> Tambah Permintaan
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <div class="card mb-1">
                        <div class="accordion" id="accordion-filter">
                            <div class="accordion-item-disabled">
                                <div id="filter-body" class="accordion-collapse collapse show" data-bs-parent="#accordion-filter">
                                    <div class="accordion-body bg-white p-2">
                                        <form class="mb-0" id="search" action="<?= $search_act ?>" method="post" autocomplete="off" onsubmit="_search(event)">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="search_act" value="save">
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <label class="form-label">Aset</label>
                                                    <select class="form-select chosen-select" name="asset_id" id="asset_id">
                                                        <option value="">-- Semua Aset --</option>
                                                        <?php foreach($all_asset as $r): ?>
                                                            <option value="<?= $r['asset_id'] ?>" <?= @$nav_sess['search']['data']['asset_id'] == $r['asset_id'] ? 'selected' : '' ?>><?= $r['asset_nm'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Pembuat Komplain</label>
                                                    <select class="form-select chosen-select" name="pegawai_id" id="pegawai_id">
                                                        <option value="">-- Semua Pegawai --</option>
                                                        <?php foreach($all_pegawai as $r): ?>
                                                            <option value="<?= $r['pegawai_id'] ?>" <?= @$nav_sess['search']['data']['pegawai_id'] == $r['pegawai_id'] ? 'selected' : '' ?>><?= $r['pegawai_nm'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select chosen-select" name="status" id="status">
                                                        <option value="">-- Semua --</option>
                                                        <option value="baru" <?= @$nav_sess['search']['data']['status'] == 'baru' ? 'selected' : '' ?>>Baru</option>
                                                        <option value="diproses" <?= @$nav_sess['search']['data']['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                        <option value="selesai" <?= @$nav_sess['search']['data']['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Pencarian</label>
                                                    <input class="form-control" type="text" name="term" id="term" value="<?= @$nav_sess['search']['data']['term'] ?>">
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group mt-4">
                                                        <button class="btn" type="submit" onclick="_search(event)"><i class="fas fa-search"></i>&nbsp;Cari</button>
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
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card p-2">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped table-sm" id="datatable-main">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="7%">Aksi</th>
                                <th width="10%">ID Permintaan</th>
                                <th width="10%">Tgl Komplain</th>
                                <th width="15%">Nama Aset</th>
                                <th width="15%">Pembuat Komplain</th>
                                <th>Deskripsi</th>
                                <th width="8%">Status</th>
                                <th width="5%">Denah</th>
                                <th width="5%">Aktif?</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>