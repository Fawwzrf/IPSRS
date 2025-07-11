@include('ipsrs::admin.pekerjaan.permintaan_komplain._js')
<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col"><h2 class="page-title">Manajemen Permintaan Komplain</h2></div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url($uri . '/form_modal') }}', size: 'modal-lg'})" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Permintaan
                    </a>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <div class="card mb-1">
                        <div class="accordion" id="accordion-filter">
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filter-body"><i class="fas fa-filter me-2"></i>Opsi Filter</button></h2>
                                <div id="filter-body" class="accordion-collapse collapse" data-bs-parent="#accordion-filter">
                                    <div class="accordion-body bg-white p-2">
                                        <form class="mb-0" id="search" action="{{ url($uri . '?n=' . request('n')) }}" method="post" autocomplete="off" onsubmit="_search(event)">
                                            @csrf
                                            <input type="hidden" name="search_act" value="save">
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <label class="form-label">Aset</label>
                                                    <select class="form-select chosen-select" name="asset_id">
                                                        <option value="">-- Semua Aset --</option>
                                                        @foreach($all_asset as $r)
                                                            <option value="{{ $r['asset_id'] }}" @if(@$nav_sess['search']['data']['asset_id'] == $r['asset_id']) selected @endif>{{ $r['asset_nm'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Pembuat Komplain</label>
                                                    <select class="form-select chosen-select" name="pegawai_id">
                                                        <option value="">-- Semua Pegawai --</option>
                                                         @foreach($all_pegawai as $r)
                                                            <option value="{{ $r['pegawai_id'] }}" @if(@$nav_sess['search']['data']['pegawai_id'] == $r['pegawai_id']) selected @endif>{{ $r['pegawai_nm'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select chosen-select" name="status">
                                                        <option value="">-- Semua --</option>
                                                        <option value="baru" @if(@$nav_sess['search']['data']['status'] == 'baru') selected @endif>Baru</option>
                                                        <option value="diproses" @if(@$nav_sess['search']['data']['status'] == 'diproses') selected @endif>Diproses</option>
                                                        <option value="selesai" @if(@$nav_sess['search']['data']['status'] == 'selesai') selected @endif>Selesai</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Pencarian</label>
                                                    <input class="form-control" type="text" name="term" value="{{ @$nav_sess['search']['data']['term'] }}">
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group mt-4">
                                                        <button class="btn" type="submit"><i class="fas fa-search"></i>&nbsp;Cari</button>
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
                                <th>No</th>
                                <th>Aksi</th>
                                <th>ID Permintaan</th>
                                <th>Tgl Komplain</th>
                                <th>Nama Aset</th>
                                <th>Pembuat Komplain</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Denah</th>
                                <th>Aktif?</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>