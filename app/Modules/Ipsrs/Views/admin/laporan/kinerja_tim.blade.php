{{-- filepath: c:\laragon\www\ipsrs\app\Modules\Ipsrs\Views\admin\laporan\kinerja_tim.blade.php --}}
@if(request('print') == '1')
    <div class="print-header">
        <h2 class="text-center">{{ $judul ?? 'Laporan Kinerja Tim' }}</h2>
        <table class="mb-2" style="width:100%;font-size:14px;">
            <tr>
                <td style="width:120px;">Periode</td>
                <td>: {{ $periode_label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Teknisi</td>
                <td>: {{ $teknisi_label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pencarian</td>
                <td>: {{ $pencarian_label ?? '-' }}</td>
            </tr>
        </table>
    </div>
@endif
@include('ipsrs::admin.laporan._js')
<div class="page-wrapper laporan-kinerja-tim">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ $nav['nav_nm'] ?? '' }}
                    </div>
                    <h2 class="page-title">
                        Laporan Kinerja Tim
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <button type="button" class="btn btn-success d-sm-inline-block btn-export-excel">
                            <i class="fas fa-file-excel"></i> Ekspor ke Excel
                        </button>
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
                    <form action="{{ url($uri . '?n=' . request('n')) }}" method="POST" class="mb-0 filter-form"
                        id="search" autocomplete="off" onsubmit="_search(event)">
                        @csrf
                        <input type="hidden" name="search_act" value="save">
                        <input type="hidden" name="tgl_start" value="">
                        <input type="hidden" name="tgl_end" value="">
                        <input type="hidden" name="n" value="{{ request('n') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Periode</label>
                                <select name="periode" class="form-select" id="periode-filter">
                                    <option value="custom">Custom</option>
                                    <option value="harian">Harian</option>
                                    <option value="bulanan">Bulanan</option>
                                    <option value="tahunan">Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3 filter-tgl-mulai">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="text" name="tgl_start" class="form-control datepicker-notauto"
                                    value="{{ @$nav_sess['search']['data']['tgl_start'] ?? date('01-m-Y') }}">
                            </div>
                            <div class="col-md-3 filter-tgl-akhir">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="text" name="tgl_end" class="form-control datepicker-notauto"
                                    value="{{ @$nav_sess['search']['data']['tgl_end'] ?? date('t-m-Y') }}">
                            </div>
                            <div class="col-md-3 filter-tgl-single" style="display:none;">
                                <label class="form-label">Tanggal</label>
                                <input type="text" name="tgl_single" class="form-control datepicker-notauto"
                                    value="">
                            </div>
                            <div class="col-md-3 filter-bulan" style="display:none;">
                                <label class="form-label">Bulan & Tahun</label>
                                <div class="d-flex gap-2">
                                    <select name="bulan" class="form-select" style="width: 60%;">
                                        <option value="">Bulan</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                        @endfor
                                    </select>
                                    <select name="tahun_bulan" class="form-select" style="width: 40%;">
                                        <option value="">Tahun</option>
                                        @for ($y = date('Y'); $y >= date('Y') - 10; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 filter-tahun" style="display:none;">
                                <label class="form-label">Tahun</label>
                                <select name="tahun" class="form-select">
                                    <option value="">Pilih Tahun</option>
                                    @for ($y = date('Y'); $y >= date('Y') - 10; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Teknisi</label>
                                <select name="pegawai_id" class="form-select chosen-select">
                                    <option value="">Semua Teknisi</option>
                                    @foreach ($all_teknisi as $teknisi)
                                        <option value="{{ $teknisi['pegawai_id'] }}"
                                            {{ isset($nav_sess['search']['data']['pegawai_id']) && $nav_sess['search']['data']['pegawai_id'] == $teknisi['pegawai_id'] ? 'selected' : '' }}>
                                            {{ $teknisi['pegawai_nm'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pencarian</label>
                                <input type="text" name="search" class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <div class="input-group mt-4">
                                    <button type="button" class="btn btn-primary btn-filter"><i
                                            class="fas fa-search"></i>&nbsp;Filter</button>
                                    <button type="button" class="btn btn-secondary btn-reset"><i
                                            class="fas fa-times"></i>&nbsp;Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table id="datatable-main" class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Order ID</th>
                                <th>Jenis</th>
                                <th>Teknisi</th>
                                <th>Aset</th>
                                <th>Respon Admin (mnt)</th>
                                <th>Penerimaan Teknisi (mnt)</th>
                                <th>Pengerjaan (mnt)</th>
                                <th>Total Penyelesaian (mnt)</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="8" class="text-end">Rata-rata Penyelesaian</th>
                                <th class="text-end h3" id="footer-rerata-penyelesaian">-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer d-print-none">
                    <div class="d-flex align-items-center">
                        <p class="m-0 text-muted">
                            Menampilkan <span id="count-data">0</span> data
                            &nbsp;|&nbsp;
                            <span id="rata-rata-penyelesaian"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 1.5cm;
    }
    body { font-family: Arial, sans-serif; font-size: 12px; }
    .d-print-none, .btn, .page-header, .card-footer { display: none !important; }
    .print-header { margin-bottom: 20px; }
    .print-header h2 { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
    table { border-collapse: collapse; width: 100%; page-break-inside: avoid; }
    th, td { border: 1px solid #333; padding: 6px 8px; }
    thead th { background: #eee; }
    tfoot { font-weight: bold; }
    .table-responsive { overflow: visible !important; }
}
@media print and (orientation: landscape) {
    @page { size: A4 landscape; }
}
</style>
<script>
    $(document).ready(function() {
        function updateRerataFooter(json) {
            if (json && json.rata_rata_penyelesaian !== undefined) {
                $('#footer-rerata-penyelesaian').text(json.rata_rata_penyelesaian + ' menit');
            } else {
                $('#footer-rerata-penyelesaian').text('-');
            }
        }

        if (typeof tabel !== 'undefined' && tabel) {
            tabel.on('xhr', function(e, settings, json) {
                updateRerataFooter(json);
            });
            tabel.on('draw', function(e, settings) {
                // Ambil data terakhir dari ajax cache DataTables
                var json = tabel.ajax.json();
                updateRerataFooter(json);
            });
        }
    });
</script>
