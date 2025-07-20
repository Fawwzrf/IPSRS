@include('ipsrs::admin.laporan._js')
<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <?= $nav['nav_nm'] ?>
                    </div>
                    <h2 class="page-title">
                        Laporan Kinerja Aset
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ url($uri . '?n=' . request('n') . '&export=excel') }}" class="btn btn-success d-sm-inline-block">
                            <i class="fas fa-file-excel"></i> Ekspor ke Excel
                        </a>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Cetak Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <form action="{{ url($uri . '?n=' . request('n')) }}" method="POST" class="mb-0" id="search" autocomplete="off" onsubmit="_search(event)">
                        @csrf
                        <input type="hidden" name="search_act" value="save">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Kategori Aset</label>
                                <select name="kategori_asset_id" id="kategori_asset_id" class="form-select chosen-select">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach($all_kategori_asset as $k)
                                        <option value="{{ $k['kategori_asset_id'] }}" @if(@$nav_sess['search']['data']['kategori_asset_id'] == $k['kategori_asset_id']) selected @endif>{{ $k['kategori_asset_nm'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lokasi</label>
                                <select name="lokasi_id" id="lokasi_id" class="form-select chosen-select">
                                    <option value="">-- Semua Lokasi --</option>
                                    @foreach($all_lokasi as $l)
                                        <option value="{{ $l['lokasi_id'] }}" @if(@$nav_sess['search']['data']['lokasi_id'] == $l['lokasi_id']) selected @endif>{{ $l['lokasi_nm'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group mt-4">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i>&nbsp;Filter</button>
                                    <button type="button" class="btn btn-secondary" onclick="_searchReset()"><i class="fas fa-times"></i>&nbsp;Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Aset</th>
                                <th>Lokasi</th>
                                <th>Jumlah OK</th>
                                <th>Jumlah Perbaikan</th>
                                <th>Jumlah PM</th>
                                <th>Terakhir Ditangani</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['asset_nm'] }}</td>
                                <td>{{ $row['lokasi_nm'] }}</td>
                                <td>{{ numId($row['jumlah_ok']) }}</td>
                                <td>{{ numId($row['jumlah_perbaikan']) }}</td>
                                <td>{{ numId($row['jumlah_pemeliharaan']) }}</td>
                                <td>{{ @to_date($row['terakhir_ditangani']) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada data untuk ditampilkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-print-none">
                    <div class="d-flex align-items-center">
                        <p class="m-0 text-muted">Menampilkan {{ count($laporan) }} data</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .d-print-none {
        display: none !important;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.05) !important;
        -webkit-print-color-adjust: exact;
    }
    .page-header {
        border-bottom: 1px solid #aaa;
        margin-bottom: 20px;
    }
</style>