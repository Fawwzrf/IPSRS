@include('ipsrs::admin.pekerjaan.jadwal_pm._js')
<div class="page-wrapper">
  <div class="page-header d-print-none mt-2">
    <div class="container-xl">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">Manajemen Jadwal Pemeliharaan</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url('ipsrs/adminjadwalpm/form_modal') }}', size: 'modal-lg'})" class="btn btn-primary d-sm-inline-block">
              <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
          </div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col">
            <div class="card mb-1">
                <div class="accordion" id="accordion-filter">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-filter">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filter-body">
                                <i class="fas fa-filter me-2"></i> Opsi Filter
                            </button>
                        </h2>
                        <div id="filter-body" class="accordion-collapse collapse" data-bs-parent="#accordion-filter">
                            <div class="accordion-body bg-white p-2">
                                <form class="mb-0" id="search" action="{{ url('ipsrs/adminjadwalpm?n=' . request('n')) }}" method="post" autocomplete="off" onsubmit="_search(event)">
                                    @csrf
                                    <input type="hidden" name="search_act" value="save">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <label class="form-label">Filter Aset</label>
                                            <select class="form-select chosen-select" name="asset_id">
                                                <option value="">-- Semua Aset --</option>
                                                @foreach($all_asset as $asset)
                                                    <option value="{{ $asset['asset_id'] }}" @if(@$nav_sess['search']['data']['asset_id'] == $asset['asset_id']) selected @endif>
                                                        {{ $asset['asset_nm'] }} ({{ $asset['no_seri'] ?? $asset['asset_id'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Filter Status</label>
                                            <select class="form-select chosen-select" name="status">
                                                <option value="">-- Semua Status --</option>
                                                <option value="aktif" @if(@$nav_sess['search']['data']['status'] == 'aktif') selected @endif>Aktif</option>
                                                <option value="selesai" @if(@$nav_sess['search']['data']['status'] == 'selesai') selected @endif>Selesai</option>
                                                <option value="ditunda" @if(@$nav_sess['search']['data']['status'] == 'ditunda') selected @endif>Ditunda</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Pencarian</label>
                                            <input class="form-control" type="text" name="term" value="{{ @$nav_sess['search']['data']['term'] }}">
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="input-group mt-4">
                                              <button class="btn" type="submit" onclick="_search(event)"><i class="fas fa-search"></i>&nbsp;&nbsp;Cari</button>
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
  <div class="page-body mt-1">
    <div class="container-xl">
      <div class="row">
        <div class="col">
          <div class="card p-2">
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-striped table-sm" id="datatable-main">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Aksi</th>
                    <th>Nama Aset</th>
                    <th>Frekuensi</th>
                    <th>Jenis Pekerjaan</th>
                    <th>Tgl Terakhir</th>
                    <th>Tgl Berikutnya</th>
                    <th>Status</th>
                    <th>Aktif?</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>