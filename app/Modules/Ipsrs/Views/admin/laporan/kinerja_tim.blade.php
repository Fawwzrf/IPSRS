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
                        Laporan Kinerja & Waktu Proses Tim
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
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
                                <label class="form-label">Filter Teknisi</label>
                                <select name="pegawai_id" id="pegawai_id" class="form-select chosen-select">
                                    <option value="">-- Semua Teknisi --</option>
                                    @foreach($all_teknisi as $t)
                                        <option value="{{ $t['pegawai_id'] }}" @if(@$nav_sess['search']['data']['pegawai_id'] == $t['pegawai_id']) selected @endif>{{ $t['pegawai_nm'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i>&nbsp;Filter</button>
                                    <button type="button" class="btn btn-secondary" onclick="_searchReset()"><i class="fas fa-times"></i>&nbsp;Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped table-sm" id="datatable-main">
                        <thead>
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Order ID</th>
                                <th rowspan="2">Teknisi</th>
                                <th colspan="4" class="text-center">Durasi Proses (Menit)</th>
                            </tr>
                            <tr>
                                <th>Respon Admin</th>
                                <th>Penerimaan Teknisi</th>
                                <th>Pengerjaan</th>
                                <th class="bg-blue-lt">Total Penyelesaian</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-print-none">
                    <p class="m-0 text-muted">Laporan ini menampilkan durasi dalam menit untuk setiap tahapan proses kerja.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .d-print-none { display: none !important; }
    .page-header, .card-body, .card-footer { display: none !important; }
    body { padding: 1cm; }
    table thead th { text-align: center; }
</style>