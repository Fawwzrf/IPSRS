@include('ipsrs::jadwal_pm._js')
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
                            <i class="fas fa-plus"></i> Tambah Jadwal PM Baru
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
                                                <div class="col-lg-3">
                                                    <label class="form-label">Aset</label>
                                                    <select class="form-select chosen-select" id="asset_id_filter" name="asset_id">
                                                        <option value="">-- Pilih --</option>
                                                        <?php foreach($all_asset as $r) : ?>
                                                            <option value="<?= $r['asset_id'] ?>" <?= (@$nav_sess['search']['data']['asset_id'] == $r['asset_id']) ? 'selected' : '' ?>>
                                                                <?= $r['asset_id'] ?> - <?= $r['asset_nm'] ?> (No. Seri: <?= $r['no_seri'] ?>) {{-- Sesuaikan dengan kolom yang tersedia --}}
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Frekuensi</label>
                                                    <select class="form-select chosen-select" id="frekuensi_filter" name="frekuensi">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="harian" <?= 'harian' == @$nav_sess['search']['data']['frekuensi'] ? 'selected' : '' ?>>Harian</option>
                                                        <option value="mingguan" <?= 'mingguan' == @$nav_sess['search']['data']['frekuensi'] ? 'selected' : '' ?>>Mingguan</option>
                                                        <option value="bulanan" <?= 'bulanan' == @$nav_sess['search']['data']['frekuensi'] ? 'selected' : '' ?>>Bulanan</option>
                                                        <option value="kuartalan" <?= 'kuartalan' == @$nav_sess['search']['data']['frekuensi'] ? 'selected' : '' ?>>Kuartalan</option>
                                                        <option value="tahunan" <?= 'tahunan' == @$nav_sess['search']['data']['frekuensi'] ? 'selected' : '' ?>>Tahunan</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Jenis PM</label>
                                                    <select class="form-select chosen-select" id="jenis_filter" name="jenis">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="kalibrasi" <?= 'kalibrasi' == @$nav_sess['search']['data']['jenis'] ? 'selected' : '' ?>>Kalibrasi</option>
                                                        <option value="inspeksi" <?= 'inspeksi' == @$nav_sess['search']['data']['jenis'] ? 'selected' : '' ?>>Inspeksi</option>
                                                        <option value="pembersihan" <?= 'pembersihan' == @$nav_sess['search']['data']['jenis'] ? 'selected' : '' ?>>Pembersihan</option>
                                                        <option value="penggantian_part" <?= 'penggantian_part' == @$nav_sess['search']['data']['jenis'] ? 'selected' : '' ?>>Penggantian Part</option>
                                                        <option value="penyesuaian" <?= 'penyesuaian' == @$nav_sess['search']['data']['jenis'] ? 'selected' : '' ?>>Penyesuaian</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Status Jadwal</label>
                                                    <select class="form-select chosen-select" id="status_filter" name="status">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="aktif" <?= 'aktif' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Aktif</option>
                                                        <option value="ditunda" <?= 'ditunda' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Ditunda</option>
                                                        <option value="selesai" <?= 'selesai' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Selesai</option>
                                                        <option value="dibatalkan" <?= 'dibatalkan' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Dibatalkan</option>
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
                                                <th width="10%">ID Jadwal</th>
                                                <th width="15%">Aset</th>
                                                <th width="10%">Frekuensi</th>
                                                <th width="10%">Jenis PM</th>
                                                <th width="10%">Tgl. Terakhir</th>
                                                <th width="10%">Tgl. Berikutnya</th>
                                                <th width="10%">Estimasi (menit)</th>
                                                <th width="10%">Status Jadwal</th>
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