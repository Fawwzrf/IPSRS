{{-- Memanggil file JavaScript terpisah --}}
@include('master::asset._js')

<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ $nav['nav_nm'] ?? 'Master' }}
                    </div>
                    <h2 class="page-title">
                        {{ $title ?? 'Data Aset' }}
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ $uri . '/form_modal' }}', size: 'modal-lg'})" class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-plus"></i> Tambah Aset Baru
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row mt-2">
                <div class="col">
                    <div class="card mb-1">
                        <div class="accordion" id="accordion-filter">
                            <div class="accordion-item">
                                {{-- Judul Filter --}}
                                <h2 class="accordion-header" id="heading-filter">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filter-body" aria-expanded="false">
                                        <i class="fas fa-filter me-2"></i> Opsi Pencarian & Filter
                                    </button>
                                </h2>
                                <div id="filter-body" class="accordion-collapse collapse" data-bs-parent="#accordion-filter">
                                    <div class="accordion-body bg-white p-2">
                                        {{-- Form Filter --}}
                                        <form class="mb-0" id="search" action="{{ $search_act }}" method="post" autocomplete="off">
                                            @csrf
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <label class="form-label">Lokasi</label>
                                                    {{-- PERBAIKAN: name="search[lokasi_id]" menjadi name="lokasi_id" --}}
                                                    <select class="form-select chosen-select" name="lokasi_id">
                                                        <option value="">-- Semua Lokasi --</option>
                                                        @foreach($all_lokasi as $r)
                                                            <option value="{{ $r['lokasi_id'] }}" @if(@$nav_sess['search']['data']['lokasi_id'] == $r['lokasi_id']) selected @endif>
                                                                {{ $r['lokasi_id'] }} - {{ $r['lokasi_nm'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Kategori</label>
                                                    {{-- PERBAIKAN: name="search[kategori_asset_id]" menjadi name="kategori_asset_id" --}}
                                                    <select class="form-select chosen-select" name="kategori_asset_id">
                                                        <option value="">-- Semua Kategori --</option>
                                                        @foreach($all_kategori_asset as $r)
                                                            <option value="{{ $r['kategori_asset_id'] }}" @if(@$nav_sess['search']['data']['kategori_asset_id'] == $r['kategori_asset_id']) selected @endif>
                                                                {{ $r['kategori_asset_id'] }} - {{ $r['kategori_asset_nm'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Status Aset</label>
                                                    {{-- PERBAIKAN: name="search[status]" menjadi name="status" --}}
                                                    <select class="form-select chosen-select" name="status">
                                                        <option value="">-- Semua Status --</option>
                                                        <option value="aktif" @if('aktif' == @$nav_sess['search']['data']['status']) selected @endif>Aktif</option>
                                                        <option value="perbaikan" @if('perbaikan' == @$nav_sess['search']['data']['status']) selected @endif>Perbaikan</option>
                                                        <option value="nonaktif" @if('nonaktif' == @$nav_sess['search']['data']['status']) selected @endif>Nonaktif</option>
                                                        <option value="dihapus" @if('dihapus' == @$nav_sess['search']['data']['status']) selected @endif>Dihapus</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">Pencarian</label>
                                                    {{-- PERBAIKAN: name="search[term]" menjadi name="term" --}}
                                                    <input class="form-control" type="text" name="term" value="{{ @$nav_sess['search']['data']['term'] }}">
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group mt-4">
                                                        <button class="btn" type="submit" onclick="_search(event)"><i class="fas fa-search"></i>&nbsp;Cari</button>
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
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="row">
                <div class="col">
                    <div class="card p-2">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped table-sm display nowrap" id="datatable-main">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="7%">Aksi</th>
                                        <th width="10%">ID Aset</th>
                                        <th width="15%">Nama Aset</th>
                                        <th width="7%">Jenis</th>
                                        <th width="10%">No. Seri</th>
                                        <th>Merk</th>
                                        <th>Kategori</th>
                                        <th>Lokasi</th>
                                        <th width="10%">PM Berikutnya</th>
                                        <th width="5%">Status</th>
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