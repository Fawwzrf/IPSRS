{{-- filepath: c:\laragon\www\ipsrs\app\Modules\Ipsrs\Views\admin\laporan\kinerja_tim.blade.php --}}
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
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="text" name="tgl_start" class="form-control datepicker-notauto" value="{{ $nav_sess['search']['data']['tgl_start'] ?? '' }}" placeholder="Tanggal Mulai">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="text" name="tgl_end" class="form-control datepicker-notauto" value="{{ $nav_sess['search']['data']['tgl_end'] ?? '' }}" placeholder="Tanggal Selesai">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Teknisi</label>
                                <select name="pegawai_id" class="form-select chosen-select">
                                    <option value="">Semua Teknisi</option>
                                    @foreach($all_teknisi as $teknisi)
                                        <option value="{{ $teknisi['pegawai_id'] }}" {{ (isset($nav_sess['search']['data']['pegawai_id']) && $nav_sess['search']['data']['pegawai_id'] == $teknisi['pegawai_id']) ? 'selected' : '' }}>
                                            {{ $teknisi['pegawai_nm'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group mt-4">
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
</style>