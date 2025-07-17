@include('ipsrs::admin.pekerjaan.order_kerja._js')
<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <?= $nav['nav_nm'] ?>
                    </div>
                    <h2 class="page-title">
                        Manajemen Order Kerja
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="javascript:void(0)"
                            onclick="_modal(event, {uri: '{{ url($uri . '/form_modal') }}', size: 'modal-lg', position: 'normal'})"
                            class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-plus"></i> Tambah Order Kerja
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <div class="card mb-1">
                        <div class="accordion" id="accordion-example">
                            <div class="accordion-item-disabled">
                                <div id="filter" class="accordion-collapse collapse show"
                                    data-bs-parent="#accordion-example">
                                    <div class="accordion-body bg-white p-2">
                                        <form class="mb-0" id="search" action="<?= $search_act ?>" method="post"
                                            autocomplete="off" onsubmit="_search(event)">
                                            @csrf
                                            <input type="hidden" name="search_act" value="save">
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <label class="form-label">Jenis Order</label>
                                                    <select class="form-select chosen-select" name="jenis"
                                                        id="jenis">
                                                        <option value="">-- Semua Jenis --</option>
                                                        <option value="pemeliharaan"
                                                            @if (@$nav_sess['search']['data']['jenis'] == 'pemeliharaan') selected @endif>
                                                            Pemeliharaan</option>
                                                        <option value="perbaikan"
                                                            @if (@$nav_sess['search']['data']['jenis'] == 'perbaikan') selected @endif>Perbaikan
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select chosen-select" name="status"
                                                        id="status">
                                                        <option value="">-- Semua Status (Aktif) --</option>
                                                        <option value="baru"
                                                            @if (@$nav_sess['search']['data']['status'] == 'baru') selected @endif>Baru
                                                        </option>
                                                        <option value="ditugaskan"
                                                            @if (@$nav_sess['search']['data']['status'] == 'ditugaskan') selected @endif>Ditugaskan
                                                        </option>
                                                        <option value="diproses"
                                                            @if (@$nav_sess['search']['data']['status'] == 'diproses') selected @endif>Diproses
                                                        </option>
                                                        <option value="selesai"
                                                            @if (@$nav_sess['search']['data']['status'] == 'selesai') selected @endif>Selesai
                                                        </option>
                                                        <option value="dibatalkan"
                                                            @if (@$nav_sess['search']['data']['status'] == 'dibatalkan') selected @endif>Dibatalkan
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-4">
                                                    <label class="form-label">Pencarian (Aset, Deskripsi, ID)</label>
                                                    <input class="form-control" type="text" name="term"
                                                        id="term"
                                                        value="{{ @$nav_sess['search']['data']['term'] }}">
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group mt-4">
                                                        <button class="btn" type="submit" onclick="_search(event)"><i
                                                                class="fas fa-search"></i>&nbsp;Cari</button>
                                                        <button class="btn" type="button"
                                                            onclick="_searchReset()"><i
                                                                class="fas fa-times"></i>&nbsp;Reset</button>
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
                                <th width="10%">ID Order</th>
                                <th width="10%">Jenis</th>
                                <th>Aset & Deskripsi</th>
                                <th width="15%">Tim Teknisi</th>
                                <th width="10%">Prioritas</th>
                                <th width="10%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
