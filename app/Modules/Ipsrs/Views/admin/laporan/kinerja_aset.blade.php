<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <h2 class="page-title">Laporan Kinerja Aset</h2>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <form action="{{ url($uri) }}" method="POST">
                        @csrf
                        <input type="hidden" name="search_act" value="1">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="kategori_asset_id" class="form-select">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach($all_kategori_asset as $k)
                                        <option value="{{ $k['kategori_asset_id'] }}" @if(@$nav_sess['search']['data']['kategori_asset_id'] == $k['kategori_asset_id']) selected @endif>{{ $k['kategori_asset_nm'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="lokasi_id" class="form-select">
                                    <option value="">-- Semua Lokasi --</option>
                                    @foreach($all_lokasi as $l)
                                        <option value="{{ $l['lokasi_id'] }}" @if(@$nav_sess['search']['data']['lokasi_id'] == $l['lokasi_id']) selected @endif>{{ $l['lokasi_nm'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Aset</th>
                                <th>Lokasi</th>
                                <th>Jumlah OK</th>
                                <th>Jumlah Perbaikan</th>
                                <th>Jumlah PM</th>
                                <th>Terakhir Ditangani</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($laporan as $row)
                            <tr>
                                <td>{{ $row['asset_nm'] }}</td>
                                <td>{{ $row['lokasi_nm'] }}</td>
                                <td>{{ $row['jumlah_ok'] }}</td>
                                <td>{{ $row['jumlah_perbaikan'] }}</td>
                                <td>{{ $row['jumlah_pemeliharaan'] }}</td>
                                <td>{{ @to_date($row['terakhir_ditangani']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>