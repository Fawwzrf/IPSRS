@include('ipsrs::log_kerja._js')
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
                            <i class="fas fa-plus"></i> Tambah Log Kerja Baru
                        </a>
                    </div>
                </div>
            </div>
            {{-- Bagian Filter Pencarian --}}
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
                                                    <label class="form-label">Jenis Log</label>
                                                    <select class="form-select chosen-select" id="jenis_filter_log" name="jenis_filter">
                                                        <option value="">-- Semua --</option>
                                                        <option value="pemeliharaan" <?= 'pemeliharaan' == @$nav_sess['search']['data']['jenis_filter'] ? 'selected' : '' ?>>Pemeliharaan</option>
                                                        <option value="perbaikan" <?= 'perbaikan' == @$nav_sess['search']['data']['jenis_filter'] ? 'selected' : '' ?>>Perbaikan</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Order Kerja</label>
                                                    <select class="form-select chosen-select" id="order_kerja_id_filter" name="order_kerja_id">
                                                        <option value="">-- Pilih --</option>
                                                        <?php foreach($all_orders as $r) : ?>
                                                            <option value="<?= $r['order_kerja_id'] ?>" <?= (@$nav_sess['search']['data']['order_kerja_id'] == $r['order_kerja_id']) ? 'selected' : '' ?>>
                                                                <?= $r['order_kerja_id'] ?> (<?= $r['jenis']?>) - Aset: <?= @$r['asset_nm'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Teknisi</label>
                                                    <select class="form-select chosen-select" id="pegawai_id_filter" name="pegawai_id">
                                                        <option value="">-- Pilih --</option>
                                                        <?php foreach($all_pegawai as $pgw) : ?>
                                                            <option value="<?= $pgw['pegawai_id'] ?>" <?= (@$nav_sess['search']['data']['pegawai_id'] == $pgw['pegawai_id']) ? 'selected' : '' ?>>
                                                                <?= $pgw['pegawai_nm'] ?>
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
                                                <th width="10%">ID Log</th>
                                                <th width="10%">Order Kerja</th>
                                                <th width="10%">Jenis OK</th>
                                                <th width="15%">Aset</th>
                                                <th width="10%">Lokasi Aset</th>
                                                <th width="10%">Teknisi</th>
                                                <th width="10%">Tgl. Mulai</th>
                                                <th width="10%">Tgl. Selesai</th>
                                                <th width="5%">Biaya Total</th>
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