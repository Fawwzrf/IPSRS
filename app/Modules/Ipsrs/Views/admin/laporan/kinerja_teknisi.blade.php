@include('ipsrs::admin.laporan._js')
@if (request('print') == '1')
    <div class="print-header">
        <h2 class="text-center">{{ $judul ?? 'Laporan Kinerja Teknisi' }}</h2>
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

<div class="page-wrapper laporan-kinerja-teknisi">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <?= $nav['nav_nm'] ?>
                    </div>
                    <h2 class="page-title">
                        Laporan Kinerja Teknisi
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
                        <input type="hidden" name="n" value="{{ request('n') }}">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Filter Berdasarkan Teknisi</label>
                                @php
                                    $dataFilter = @$nav_sess['search']['data'];
                                    if (!is_array($dataFilter)) {
                                        $dataFilter = [];
                                    }
                                @endphp
                                <select name="pegawai_id" id="pegawai_id" class="form-select chosen-select">
                                    <option value="">-- Semua Teknisi --</option>
                                    @foreach ($all_teknisi as $t)
                                        <option value="{{ $t['pegawai_id'] }}"
                                            @if (($dataFilter['pegawai_id'] ?? '')== $t['pegawai_id']) selected @endif>{{ $t['pegawai_nm'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pencarian</label>
                                <input type="text" name="search" class="form-control">
                            </div>
                            <div class="col-md-4">
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
                                <th>Nama Teknisi</th>
                                <th>Total Tugas Diterima</th>
                                <th>Tugas Selesai</th>
                                <th>Persentase Selesai</th>
                                <th>Rata-rata Durasi (Menit)</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-print-none">
                    <div class="d-flex align-items-center">
                        <p class="m-0 text-muted">
                            Menampilkan <span id="count-data">0</span> data
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

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .d-print-none,
        .btn,
        .page-header,
        .card-footer {
            display: none !important;
        }

        .print-header {
            margin-bottom: 20px;
        }

        .print-header h2 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px 8px;
        }

        thead th {
            background: #eee;
        }

        tfoot {
            font-weight: bold;
        }

        .table-responsive {
            overflow: visible !important;
        }
    }

    @media print and (orientation: landscape) {
        @page {
            size: A4 landscape;
        }
    }
</style>
