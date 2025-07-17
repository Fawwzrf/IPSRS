{{-- Memanggil file JavaScript terpisah --}}
@include('master::asset._js')
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
            <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-lg', position: 'normal'})" class="btn btn-primary d-sm-inline-block"><i class="fas fa-plus"></i> Tambah Aset Baru</a>
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
                      @csrf
                      <div class="row">
                        <div class="col-lg-3">
                          <label class="form-label">Lokasi</label>
                          <select class="form-select chosen-select" id="lokasi_id" name="lokasi_id">
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
                          <select class="form-select chosen-select" id="kategori_asset_id" name="kategori_asset_id">
                            <option value="">-- Semua Kategori --</option>
                            @foreach($all_kategori_asset as $r)
                              <option value="{{ $r['kategori_asset_id'] }}" @if(@$nav_sess['search']['data']['kategori_asset_id'] == $r['kategori_asset_id']) selected @endif>
                                {{ $r['kategori_asset_id'] }} - {{ $r['kategori_asset_nm'] }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-lg-2">
                          <label class="form-label">Status</label>
                          <select class="form-select chosen-select" id="status" name="status">
                            <option value="">-- Semua Status --</option>
                            <option value="aktif" @if('aktif' == @$nav_sess['search']['data']['status']) selected @endif>Aktif</option>
                            <option value="perbaikan" @if('perbaikan' == @$nav_sess['search']['data']['status']) selected @endif>Perbaikan</option>
                            <option value="nonaktif" @if('nonaktif' == @$nav_sess['search']['data']['status']) selected @endif>Nonaktif</option>
                            <option value="dihapus" @if('dihapus' == @$nav_sess['search']['data']['status']) selected @endif>Dihapus</option>
                          </select>
                        </div>
                        <div class="col-lg-2">
                          <label class="form-label">Pencarian</label>
                          <input class="form-control" type="text" id="term" name="term" value="{{ @$nav_sess['search']['data']['term'] }}">
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
                  <table class="table table-vcenter card-table table-sm display nowrap" id="datatable-main">
                    <thead>
                      <tr>
                        <th width="20">No</th>
                        <th width="50">Aksi</th>
                        <th width="90">Asset ID</th>
                        <th width="">Nama Aset</th>
                        <th width="100">Jenis</th>
                        <th width="100">No Seri</th>
                        <th width="100">Merk</th>
                        <th width="120">Kategori</th>
                        <th width="150">Lokasi</th>
                        <th width="90">PM Berikutnya</th>
                        <th width="80">Status</th>
                        <th width="40">Aktif</th>
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