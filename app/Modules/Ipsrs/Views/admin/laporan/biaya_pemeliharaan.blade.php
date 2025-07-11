<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <h2 class="page-title">Laporan Biaya Pemeliharaan & Perbaikan</h2>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <form action="{{ url($uri) }}" method="POST">
                        @csrf
                        <input type="hidden" name="search_act" value="1">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="text" name="tgl_start" class="form-control datepicker-notauto" value="{{ @$nav_sess['search']['data']['tgl_start'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="text" name="tgl_end" class="form-control datepicker-notauto" value="{{ @$nav_sess['search']['data']['tgl_end'] }}">
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
                                <th>Tanggal OK</th>
                                <th>ID Order Kerja</th>
                                <th>Aset</th>
                                <th>Jenis Pekerjaan</th>
                                <th class="text-end">Biaya Sparepart (Rp)</th>
                                <th class="text-end">Biaya Lain (Rp)</th>
                                <th class="text-end">Total Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $row)
                            <tr>
                                <td>{{ to_date($row['tgl_dibuat']) }}</td>
                                <td>{{ $row['order_kerja_id'] }}</td>
                                <td>{{ $row['asset_nm'] }}</td>
                                <td>{{ ucfirst($row['jenis']) }}</td>
                                <td class="text-end">{{ numId($row['total_biaya_sparepart'] ?? 0) }}</td>
                                <td class="text-end">{{ numId($row['biaya_lain'] ?? 0) }}</td>
                                <td class="text-end fw-bold">{{ numId($row['total_biaya_ok'] ?? 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada data untuk periode yang dipilih.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Total Keseluruhan</th>
                                <th class="text-end h3">{{ numId($total_biaya) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk inisialisasi datepicker --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    $('.datepicker-notauto').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'DD-MM-YYYY' }
    });
});
</script>