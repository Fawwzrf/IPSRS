@include('ipsrs::lokasi._js')
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
                        <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-md', position: 'normal'})" class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-plus"></i> Tambah Lokasi Baru
                        </a>
                    </div>
                </div>
            </div>
            {{-- Bagian Filter Pencarian (Ditambahkan sesuai pola Pegawai) --}}
            <div class="row mt-2">
                <div class="col">
                    <div class="card mb-1">
                        <div class="accordion" id="accordion-example">
                            <div class="accordion-item-disabled">
                                <div id="filter" class="accordion-collapse collapse show" data-bs-parent="#accordion-example">
                                    <div class="accordion-body bg-white p-2">
                                        <form class="mb-0" id="search" action="<?= $search_act ?>" method="post" autocomplete="off" onsubmit="_search(event)">
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <label class="form-label">Tipe Lokasi</label>
                                                    <select class="form-select chosen-select" id="tipe_lokasi_filter" name="tipe_lokasi">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="Gedung" <?= 'Gedung' == @$nav_sess['search']['data']['tipe_lokasi'] ? 'selected' : '' ?>>Gedung</option>
                                                        <option value="Lantai" <?= 'Lantai' == @$nav_sess['search']['data']['tipe_lokasi'] ? 'selected' : '' ?>>Lantai</option>
                                                        <option value="Ruangan" <?= 'Ruangan' == @$nav_sess['search']['data']['tipe_lokasi'] ? 'selected' : '' ?>>Ruangan</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Lokasi Induk</label>
                                                    <select class="form-select chosen-select" id="parent_lokasi_id_filter" name="parent_lokasi_id">
                                                        <option value="">-- Pilih --</option>
                                                        <?php foreach($all_parent_lokasi as $r) : ?>
                                                            <option value="<?= $r['lokasi_id'] ?>" <?= (@$nav_sess['search']['data']['parent_lokasi_id'] == $r['lokasi_id']) ? 'selected' : '' ?>>
                                                                <?= $r['lokasi_id'] ?> - <?= $r['lokasi_nm'] ?> (<?= $r['tipe_lokasi'] ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Aktif Sistem?</label>
                                                    <select class="form-select chosen-select" id="active_st" name="active_st">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="1" <?= '1' == @$nav_sess['search']['data']['active_st'] ? 'selected' : '' ?>>Aktif</option>
                                                        <option value="0" <?= '0' == @$nav_sess['search']['data']['active_st'] ? 'selected' : '' ?>>Tidak Aktif</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
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
            {{-- End Bagian Filter Pencarian --}}
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
                                    <table class="table table-vcenter card-table table-striped table-sm display nowrap" id="datatable-main">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="7%">Aksi</th>
                                                <th width="10%">ID Lokasi</th>
                                                <th width="10%">ID Parent</th>
                                                <th width="15%">Nama Lokasi</th>
                                                <th>Tipe Lokasi</th>
                                                <th>Deskripsi</th>
                                                <th width="5%">Aktif?</th>
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