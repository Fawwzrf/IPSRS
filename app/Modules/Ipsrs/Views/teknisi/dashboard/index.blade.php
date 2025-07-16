<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <h2 class="page-title">Dashboard Teknisi</h2>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            @if ($tugas_baru_count > 0)
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-title">Anda memiliki {{ $tugas_baru_count }} tugas baru!</h4>
                    <div class="text-muted">Segera periksa daftar tugas Anda untuk menerima atau menolak pekerjaan.</div>
                    <div class="mt-3">
                        <a href="{{ url('ipsrs/teknisitugas') }}?n={{ request('n') }}" class="btn btn-primary">Lihat
                            Daftar Tugas</a>
                    </div>
                </div>
            @endif
            
            <!-- Ringkasan Statistik -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-primary text-white avatar">
                                        <i class="fas fa-clipboard-list fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $tugas_baru_count }} Tugas Baru
                                    </div>
                                    <div class="text-muted">
                                        Menunggu tindakan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-success text-white avatar">
                                        <i class="fas fa-tools fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ count($tugas_aktif_list ?? []) }} Pekerjaan Aktif
                                    </div>
                                    <div class="text-muted">
                                        Sedang dikerjakan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-info text-white avatar">
                                        <i class="fas fa-check-circle fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $tugas_selesai_count ?? 0 }} Selesai Bulan Ini
                                    </div>
                                    <div class="text-muted">
                                        Pekerjaan yang telah diselesaikan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-warning text-white avatar">
                                        <i class="fas fa-clock fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $tugas_mendesak_count ?? 0 }} Mendesak
                                    </div>
                                    <div class="text-muted">
                                        Prioritas tinggi & darurat
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tambahkan widget untuk tugas ditolak -->
                <div class="col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-danger text-white avatar">
                                        <i class="fas fa-ban fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $tugas_ditolak_count ?? 0 }} Ditolak/Dibatalkan
                                    </div>
                                    <div class="text-muted">
                                        Tugas yang tidak diproses
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Tugas Aktif -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tugas yang Sedang Dikerjakan</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            @forelse($tugas_aktif_list as $tugas)
                                <a href="javascript:void(0)"
                                    onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/detail/' . $tugas['penugasan_id']) }}?n={{ $n }}', size: 'modal-lg', title: 'Detail Tugas'})"
                                    class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $tugas['asset_nm'] }}</h5>
                                        <small>{{ to_date($tugas['tgl_dibuat']) }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="mb-1">{{ $tugas['deskripsi'] }}</p>
                                        <span class="badge bg-{{ $tugas['prioritas'] == 'tinggi' ? 'danger' : ($tugas['prioritas'] == 'sedang' ? 'warning' : 'info') }} ms-2">
                                            {{ ucfirst($tugas['prioritas']) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $tugas['lokasi_nm'] }}</small>
                                </a>
                            @empty
                                <div class="list-group-item">
                                    <p class="text-muted text-center mb-0">Tidak ada pekerjaan yang sedang aktif.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <!-- Jadwal Pemeliharaan -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Jadwal Pemeliharaan Mendatang</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            @if(isset($jadwal_mendatang) && is_array($jadwal_mendatang) && count($jadwal_mendatang) > 0)
                                @foreach($jadwal_mendatang as $jadwal)
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">{{ $jadwal['asset_nm'] }}</h5>
                                            <small class="text-danger">{{ to_date($jadwal['tgl_jadwal']) }}</small>
                                        </div>
                                        <p class="mb-1">{{ $jadwal['deskripsi'] ?? 'Pemeliharaan Rutin' }}</p>
                                        <small class="text-muted">{{ $jadwal['lokasi_nm'] }}</small>
                                    </div>
                                @endforeach
                            @else
                                <div class="list-group-item">
                                    <p class="text-muted text-center mb-0">Tidak ada jadwal pemeliharaan mendatang.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik & Statistik -->
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kinerja Bulan Ini</h3>
                        </div>
                        <div class="card-body">
                            <div>
                                <canvas id="chart-kinerja" style="height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Sparepart yang Sering Digunakan</h3>
                        </div>
                        <div class="card-body">
                            @if(isset($top_spareparts) && is_array($top_spareparts) && count($top_spareparts) > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table">
                                        <thead>
                                            <tr>
                                                <th>Nama Sparepart</th>
                                                <th>Penggunaan</th>
                                                <th>Stok Tersedia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($top_spareparts as $part)
                                                <tr>
                                                    <td>{{ $part['sparepart_nm'] }}</td>
                                                    <td>{{ $part['jumlah_pakai'] }}</td>
                                                    <td>
                                                        @if($part['stok'] <= $part['stok_min'])
                                                            <span class="badge bg-danger">{{ $part['stok'] }}</span>
                                                        @else
                                                            <span class="badge bg-success">{{ $part['stok'] }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center">Belum ada data penggunaan sparepart.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        try {
            console.log("Chart initialization started");
            
            // Data untuk grafik
            const selesaiData = [{{ implode(',', isset($chart_kinerja) && isset($chart_kinerja['selesai']) ? $chart_kinerja['selesai'] : [0,0,0,0]) }}];
            const baruData = [{{ implode(',', isset($chart_kinerja) && isset($chart_kinerja['baru']) ? $chart_kinerja['baru'] : [0,0,0,0]) }}];
            
            console.log("Chart data:", selesaiData, baruData);
            
            // Mendapatkan referensi ke canvas
            const ctx = document.getElementById('chart-kinerja');
            if (!ctx) {
                console.error("Canvas element not found!");
                return;
            }
            
            // Chart.js versi lama menggunakan sintaks yang berbeda
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                    datasets: [{
                        label: 'Tugas Selesai',
                        data: selesaiData,
                        backgroundColor: '#206bc4',
                        borderColor: '#206bc4',
                        borderWidth: 1
                    }, {
                        label: 'Tugas Baru',
                        data: baruData,
                        backgroundColor: '#79a6dc',
                        borderColor: '#79a6dc',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel + ' tugas';
                            }
                        }
                    }
                }
            });
            
            console.log("Chart rendered successfully");
        } catch (error) {
            console.error("Error rendering chart:", error);
            console.error(error.stack);
        }
    });
</script>
@endsection
