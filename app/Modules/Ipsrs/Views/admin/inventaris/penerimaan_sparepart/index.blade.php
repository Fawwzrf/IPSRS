@include('ipsrs::admin.inventaris.penerimaan_sparepart._js')
<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <?= $nav['nav_nm'] ?>
                    </div>
                    <h2 class="page-title">
                        Penerimaan Sparepart
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-lg'})" class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-plus"></i> Catat Penerimaan Baru
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
                                                <div class="col-lg-8">
                                                    <label class="form-label">Pencarian (ID, Nama Sparepart, Vendor, No Faktur)</label>
                                                    <input class="form-control" type="text" name="term" id="term" value="<?= @$nav_sess['search']['data']['term'] ?>">
                                                </div>
                                                <div class="col-lg-4">
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
                                <th>ID Penerimaan</th>
                                <th>Tanggal</th>
                                <th>Nama Sparepart</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Vendor</th>
                                <th>No. Faktur</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>