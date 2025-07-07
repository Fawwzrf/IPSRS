@include('ipsrs::order_kerja._js')
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
                            <i class="fas fa-plus"></i> Tambah Order Kerja Baru
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
                                                    <label class="form-label">Jenis Order</label>
                                                    <select class="form-select chosen-select" id="jenis_filter_ok" name="jenis_filter">
                                                        <option value="">-- Semua --</option>
                                                        <option value="pemeliharaan" <?= 'pemeliharaan' == @$nav_sess['search']['data']['jenis_filter'] ? 'selected' : '' ?>>Pemeliharaan</option>
                                                        <option value="perbaikan" <?= 'perbaikan' == @$nav_sess['search']['data']['jenis_filter'] ? 'selected' : '' ?>>Perbaikan</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Aset</label>
                                                    <select class="form-select chosen-select" id="asset_id_filter" name="asset_id">
                                                        <option value="">-- Pilih --</option>
                                                        <?php foreach($all_asset as $r) : ?>
                                                            <option value="<?= $r['asset_id'] ?>" <?= (@$nav_sess['search']['data']['asset_id'] == $r['asset_id']) ? 'selected' : '' ?>>
                                                                <?= $r['asset_id'] ?> - <?= $r['asset_nm'] ?> (Lokasi : <?= $r['lokasi_nm'] ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Status OK</label>
                                                    <select class="form-select chosen-select" id="status_filter" name="status">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="baru" <?= 'baru' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Baru</option>
                                                        <option value="ditugaskan" <?= 'ditugaskan' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Ditugaskan</option>
                                                        <option value="diproses" <?= 'diproses' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Diproses</option>
                                                        <option value="menunggu_sparepart" <?= 'menunggu_sparepart' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Menunggu Sparepart</option>
                                                        <option value="selesai" <?= 'selesai' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Selesai</option>
                                                        <option value="ditolak" <?= 'ditolak' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Ditolak</option>
                                                        <option value="dibatalkan" <?= 'dibatalkan' == @$nav_sess['search']['data']['status'] ? 'selected' : '' ?>>Dibatalkan</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Prioritas</label>
                                                    <select class="form-select chosen-select" id="prioritas_filter" name="prioritas">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="darurat" <?= 'darurat' == @$nav_sess['search']['data']['prioritas'] ? 'selected' : '' ?>>Darurat</option>
                                                        <option value="mendesak" <?= 'mendesak' == @$nav_sess['search']['data']['prioritas'] ? 'selected' : '' ?>>Mendesak</option>
                                                        <option value="normal" <?= 'normal' == @$nav_sess['search']['data']['prioritas'] ? 'selected' : '' ?>>Normal</option>
                                                        <option value="rendah" <?= 'rendah' == @$nav_sess['search']['data']['prioritas'] ? 'selected' : '' ?>>Rendah</option>
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
                                                <th width="10%">ID Order</th>
                                                <th width="10%">Jenis</th>
                                                <th width="15%">Aset</th>
                                                <th width="10%">Lokasi Aset</th>
                                                <th width="10%">Status OK</th>
                                                <th width="10%">Prioritas</th>
                                                <th width="10%">Dibuat</th>
                                                <th width="10%">Target Selesai</th>
                                                <th width="5%">Estimasi Biaya</th>
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