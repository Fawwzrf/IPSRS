@include('ipsrs::admin.laporan._js')
<div class="page-wrapper laporan-biaya-pemeliharaan">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <?= $nav['nav_nm'] ?>
                    </div>
                    <h2 class="page-title">
                        Laporan Biaya Pemeliharaan & Perbaikan
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ url($uri . '?n=' . request('n') . '&export=excel') }}"
                            class="btn btn-success d-sm-inline-block">
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
                    <form action="{{ url($uri . '?n=' . request('n')) }}" method="POST" class="mb-0 filter-form"
                        id="search" autocomplete="off" onsubmit="_search(event)">
                        @csrf
                        <input type="hidden" name="search_act" value="save">
                        <input type="hidden" name="tgl_start" value="">
                        <input type="hidden" name="tgl_end" value="">
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
                                <input type="text" name="tgl_single" class="form-control datepicker-notauto" value="">
                            </div>
                            <div class="col-md-3 filter-bulan" style="display:none;">
                                <label class="form-label">Bulan & Tahun</label>
                                <div class="d-flex gap-2">
                                    <select name="bulan" class="form-select" style="width: 60%;">
                                        <option value="">Bulan</option>
                                        @for($i=1;$i<=12;$i++)
                                            <option value="{{ str_pad($i,2,'0',STR_PAD_LEFT) }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                        @endfor
                                    </select>
                                    <select name="tahun_bulan" class="form-select" style="width: 40%;">
                                        <option value="">Tahun</option>
                                        @for($y = date('Y'); $y >= date('Y')-10; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 filter-tahun" style="display:none;">
                                <label class="form-label">Tahun</label>
                                <select name="tahun" class="form-select">
                                    <option value="">Pilih Tahun</option>
                                    @for($y = date('Y'); $y >= date('Y')-10; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pencarian</label>
                                <input type="text" name="search" class="form-control" value="">
                            </div>
                            <div class="col-md-3"></div>
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
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end">Total Keseluruhan</th>
                                <th class="text-end h3">{{ numId($total_biaya) }}</th>
                            </tr>
                        </tfoot>
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

<style media="print">
    .d-print-none {
        display: none !important;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, .05) !important;
        -webkit-print-color-adjust: exact;
    }

    .page-header {
        border-bottom: 1px solid #aaa;
        margin-bottom: 20px;
    }

    tfoot {
        font-weight: bold;
    }
</style>

{{-- Script untuk inisialisasi datepicker --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        $('.datepicker-notauto').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        if (typeof tabel !== 'undefined' && tabel) {
            tabel.on('xhr', function(e, settings, json) {
                if (json && json.total_biaya !== undefined) {
                    $('#datatable-main tfoot th.text-end.h3').text(
                        new Intl.NumberFormat('id-ID').format(json.total_biaya)
                    );
                }
            });
        }
    });
</script>
