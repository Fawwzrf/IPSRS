<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Portal Pelapor Kerusakan & Komplain
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    {{-- Tombol untuk membuka modal form komplain --}}
                    <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url('ipsrs/pelapor/form_komplain_modal') }}', size: 'modal-lg'})" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Laporan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Laporan Saya</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal Lapor</th>
                                <th>Aset yang Dilaporkan</th>
                                <th>Lokasi</th>
                                <th>Deskripsi Masalah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $item)
                            <tr>
                                <td>{{ to_date($item['tgl']) }}</td>
                                <td>{{ $item['asset_nm'] }}</td>
                                <td>{{ $item['lokasi_nm'] }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($item['deskripsi'], 150) }}</td>
                                <td>
                                    @php
                                        $status = strtolower($item['status']);
                                        $badgeClass = 'bg-secondary';
                                        if ($status == 'baru') $badgeClass = 'bg-info';
                                        elseif ($status == 'diproses') $badgeClass = 'bg-warning';
                                        elseif ($status == 'selesai') $badgeClass = 'bg-success';
                                        elseif ($status == 'ditolak') $badgeClass = 'bg-danger';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Anda belum pernah membuat laporan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>