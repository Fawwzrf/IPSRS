<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <h2 class="page-title">Laporan Kinerja Teknisi</h2>
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
                            <div class="col-md-4">
                                <label class="form-label">Filter Berdasarkan Teknisi</label>
                                <select name="pegawai_id" class="form-select">
                                    <option value="">-- Semua Teknisi --</option>
                                    @foreach($all_teknisi as $t)
                                        <option value="{{ $t['pegawai_id'] }}" @if(@$nav_sess['search']['data']['pegawai_id'] == $t['pegawai_id']) selected @endif>{{ $t['pegawai_nm'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Teknisi</th>
                                <th>Total Tugas Diterima</th>
                                <th>Tugas Selesai</th>
                                <th>Rata-rata Durasi (Menit)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['pegawai_nm'] }}</td>
                                <td>{{ $row['total_tugas'] }} Tugas</td>
                                <td>{{ $row['tugas_selesai'] }} Tugas</td>
                                <td>{{ number_format($row['rata_rata_durasi'] ?? 0, 2) }} Menit</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data untuk ditampilkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>