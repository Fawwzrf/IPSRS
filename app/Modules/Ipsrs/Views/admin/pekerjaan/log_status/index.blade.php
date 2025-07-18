{{-- filepath: c:\laragon\www\ipsrs\app\Modules\Ipsrs\Views\admin\pekerjaan\log_status\index.blade.php --}}
@include('ipsrs::admin.pekerjaan.log_status._js')

<div class="page-header d-print-none">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Riwayat Status Order Kerja
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                @if (isset($order_kerja))
                    <div class="alert alert-info">
                        Menampilkan riwayat status untuk Order Kerja: <strong>{{ $order_kerja['order_kerja_id'] }}</strong>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="datatable-main">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="15%">Status Sebelumnya</th>
                                    <th width="15%">Status Baru</th>
                                    <th width="15%">Oleh</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($riwayat) && count($riwayat) > 0)
                                    @foreach ($riwayat as $key => $item)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td>{{ to_date($item['tgl_perubahan'], '-', 'full_date') }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($item['status_sebelumnya']) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = 'bg-secondary';
                                                    switch ($item['status_baru']) {
                                                        case 'menunggu':
                                                            $badgeClass = 'bg-warning';
                                                            break;
                                                        case 'diproses':
                                                            $badgeClass = 'bg-info';
                                                            break;
                                                        case 'selesai':
                                                            $badgeClass = 'bg-success';
                                                            break;
                                                        case 'dibatalkan':
                                                            $badgeClass = 'bg-danger';
                                                            break;
                                                        case 'menunggu_sparepart':
                                                            $badgeClass = 'bg-primary';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($item['status_baru']) }}</span>
                                            </td>
                                            <td>{{ $item['pegawai_nm'] ?? '-' }}</td>
                                            <td>{{ $item['catatan'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data riwayat status</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Silahkan pilih Order Kerja untuk melihat riwayat statusnya.
                    </div>
                    
                    <form action="{{ $search_act }}" method="post" id="form-search">
                        @csrf
                        <input type="hidden" name="search_act" value="save">
                        <div class="row mb-3">
                            <div class="col-md-10">
                                <input type="text" name="search[term]" class="form-control" placeholder="Cari berdasarkan ID Order Kerja, Status, atau Catatan..." value="{{ @$nav_sess['search']['data']['term'] }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" onclick="_search(event)">
                                    <i class="fas fa-search me-2"></i> Cari
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="datatable-main">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="15%">Order Kerja ID</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="15%">Status Lama</th>
                                    <th width="15%">Status Baru</th>
                                    <th>Oleh</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan dimuat via AJAX -->
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>