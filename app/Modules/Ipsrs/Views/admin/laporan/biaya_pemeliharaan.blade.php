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
                    <form action="{{ url($uri . '?n=' . request('n')) }}" method="POST" class="mb-0 filter-form" id="search" autocomplete="off" onsubmit="_search(event)">
                        @csrf
                        <input type="hidden" name="search_act" value="save">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="text" name="tgl_start" id="tgl_start" class="form-control datepicker-notauto" value="{{ @$nav_sess['search']['data']['tgl_start'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="text" name="tgl_end" id="tgl_end" class="form-control datepicker-notauto" value="{{ @$nav_sess['search']['data']['tgl_end'] }}">
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <button type="button" class="btn btn-primary btn-filter"><i class="fas fa-search"></i>&nbsp;Filter</button>
                                    <button type="button" class="btn btn-secondary btn-reset"><i class="fas fa-times"></i>&nbsp;Reset</button>
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
        background-color: rgba(0,0,0,.05) !important;
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
document.addEventListener("DOMContentLoaded", function () {
    $('.datepicker-notauto').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'DD-MM-YYYY' }
    });
});
</script>