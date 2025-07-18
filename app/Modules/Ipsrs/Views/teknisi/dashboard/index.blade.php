@include('ipsrs::teknisi.dashboard._js')

<div class="container-fluid p-0">
    <div class="page-header d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 class="page-title ml-3 mb-0">Dashboard Teknisi</h2>
            <button onclick="refreshDashboard()" class="btn btn-outline-primary btn-icon">
                <i class="fas fa-sync"></i>
            </button>
        </div>
    </div>
    
    @if (isset($error_message))
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-title">Terjadi Kesalahan</h4>
            <div>{{ $error_message }}</div>
        </div>
    @endif
    
    @if (isset($tugas_baru_count) && $tugas_baru_count > 0)
        <div class="alert alert-info d-flex flex-column flex-md-row align-items-md-center justify-content-md-between" role="alert">
            <div class="mb-2 mb-md-0">
                <h4 class="alert-title mb-1">{{ $tugas_baru_count }} tugas baru!</h4>
                <div>Segera periksa daftar tugas Anda</div>
            </div>
            <div>
                <a href="{{ url('ipsrs/teknisitugas') }}?n={{ request('n') }}" class="btn btn-primary">
                    <i class="fas fa-clipboard-list me-1"></i> Lihat Tugas
                </a>
            </div>
        </div>
    @endif
    
    <!-- Ringkasan Statistik -->
    <div class="row g-2">
        <div class="col-6 col-md-3">
            <div class="card mobile-card">
                <div class="card-body p-2 px-3 d-flex align-items-center">
                    <div class="avatarlogo bg-primary me-2 flex-shrink-0">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $tugas_baru_count ?? 0 }}</div>
                        <div class="text-muted text-truncate">Tugas Baru</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card mobile-card">
                <div class="card-body p-2 px-3 d-flex align-items-center">
                    <div class="avatarlogo bg-success me-2 flex-shrink-0">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ isset($tugas_aktif_list) && is_array($tugas_aktif_list) ? count($tugas_aktif_list) : 0 }}</div>
                        <div class="text-muted text-truncate">Pekerjaan Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card mobile-card">
                <div class="card-body p-2 px-3 d-flex align-items-center">
                    <div class="avatarlogo bg-info me-2 flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $tugas_selesai_count ?? 0 }}</div>
                        <div class="text-muted text-truncate">Selesai Bulan Ini</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card mobile-card">
                <div class="card-body p-2 px-3 d-flex align-items-center">
                    <div class="avatarlogo bg-warning text-white me-2 flex-shrink-0">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $tugas_mendesak_count ?? 0 }}</div>
                        <div class="text-muted text-truncate">Prioritas Tinggi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tugas Aktif Card -->
    <div class="card mt-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0">Tugas Sedang Dikerjakan</h3>
                <a href="{{ url('ipsrs/teknisitugas') }}?n={{ request('n') }}" class="mx-4 mt-1 btn btn-sm btn-outline-primary">
                    Semua <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="list-group list-group-flush">
            @forelse($tugas_aktif_list ?? [] as $tugas)
                <div class="list-group-item p-3" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/detail/' . $tugas['penugasan_id']) }}?n={{ request('n') }}', size: 'modal-lg', title: 'Detail Tugas'})">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="text-truncate">{{ $tugas['asset_nm'] ?? 'Tidak ada nama aset' }}</strong>
                        @if(isset($tugas['prioritas']))
                            <span class="badge bg-{{ $tugas['prioritas'] == 'tinggi' ? 'danger' : ($tugas['prioritas'] == 'sedang' ? 'warning' : 'info') }}">
                                {{ ucfirst($tugas['prioritas']) }}
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ $tugas['lokasi_nm'] ?? '-' }}
                    </div>
                    <p class="mb-0 text-muted small">
                        {{ \Illuminate\Support\Str::limit($tugas['deskripsi'] ?? 'Tidak ada deskripsi', 80) }}
                    </p>
                </div>
            @empty
                <div class="empty p-3">
                    <div class="empty-img">
                        <i class="fas fa-tools fa-3x text-muted"></i>
                    </div>
                    <p class="empty-title">Tidak ada pekerjaan aktif</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Jadwal Pemeliharaan Card -->
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title m-0">Jadwal Pemeliharaan Mendatang</h3>
        </div>
        <div class="list-group list-group-flush">
            @if(isset($jadwal_mendatang) && is_array($jadwal_mendatang) && count($jadwal_mendatang) > 0)
                @foreach($jadwal_mendatang as $jadwal)
                    <div class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-truncate">{{ $jadwal['asset_nm'] ?? 'Tidak ada nama aset' }}</strong>
                            <span class="badge bg-danger">{{ isset($jadwal['tgl_jadwal']) ? to_date($jadwal['tgl_jadwal']) : '-' }}</span>
                        </div>
                        <div class="d-flex align-items-center text-muted small mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $jadwal['lokasi_nm'] ?? '-' }}
                        </div>
                        <p class="mb-0 text-muted small">
                            {{ \Illuminate\Support\Str::limit($jadwal['deskripsi'] ?? 'Pemeliharaan Rutin', 80) }}
                        </p>
                    </div>
                @endforeach
            @else
                <div class="empty p-3">
                    <div class="empty-img">
                        <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                    </div>
                    <p class="empty-title">Tidak ada jadwal mendatang</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Grafik Kinerja -->
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title m-0">Kinerja Bulan Ini</h3>
        </div>
        <div class="card-body">
            <div style="height: 220px;">
                <canvas id="chart-kinerja"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Sparepart Card -->
    <div class="card mt-3 mb-3">
        <div class="card-header">
            <h3 class="card-title m-0">Sparepart Sering Digunakan</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>Nama Sparepart</th>
                        <th class="text-center">Penggunaan</th>
                        <th class="text-center">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($top_spareparts) && is_array($top_spareparts) && count($top_spareparts) > 0)
                        @foreach($top_spareparts as $part)
                            <tr>
                                <td>{{ $part['sparepart_nm'] ?? '-' }}</td>
                                <td class="text-center">{{ $part['jumlah_pakai'] ?? 0 }}</td>
                                <td class="text-center">
                                    @if(isset($part['stok']) && isset($part['stok_min']) && $part['stok'] <= $part['stok_min'])
                                        <span class="badge bg-danger">{{ $part['stok'] }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $part['stok'] ?? 0 }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Belum ada data penggunaan sparepart</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Mobile-friendly styling dengan spacing yang lebih baik */
    .mobile-card {
        height: 100%;
        margin-bottom: 0.75rem;
    }
    
    .mobile-card .card-body {
        padding: 1rem;
    }
    
    .avatarlogo {
        width: 2.75rem;
        height: 2.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.15rem;
        margin-right: 0.875rem;
    }
    
    .list-group-item {
        cursor: pointer;
        transition: background-color 0.2s;
        padding: 1rem;
    }
    
    .list-group-item:active {
        background-color: #f8f9fa;
    }
    
    .empty {
        text-align: center;
        padding: 2.5rem 0;
        color: #6c757d;
    }
    
    .empty-img {
        margin-bottom: 1.25rem;
    }
    
    .empty-title {
        font-size: 1.1rem;
        font-weight: 300;
        margin-bottom: 0.5rem;
    }
    
    /* Perbaikan Card Layout */
    .card {
        margin-bottom: 1.25rem;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    
    .card-header {
        padding: 0.875rem 1rem;
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-title {
        margin-bottom: 0;
        font-size: 1.1rem;
    }
    
    /* Alert yang lebih baik */
    .alert {
        padding: 1rem;
        margin-bottom: 1.25rem;
    }
    
    /* Spacing untuk row statistik */
    .row.g-2 {
        margin-bottom: 0.5rem;
    }
    
    /* Perbaikan layout pada mobile */
    @media (max-width: 767.98px) {
        .container-fluid {
            padding: 0 0.75rem;
        }
        
        .page-header {
            padding: 0.75rem 0.75rem;
            margin-bottom: 1rem;
        }
        
        .card-header {
            padding: 0.75rem 1rem;
        }
        
        .card-body {
            padding: 0.875rem;
        }
        
        /* Untuk statistik */
        .mobile-card .card-body {
            padding: 0.75rem;
        }
        
        .avatarlogo {
            width: 2.5rem;
            height: 2.5rem;
            margin-right: 0.75rem;
        }
        
        /* Untuk tabel */
        .table th, .table td {
            padding: 0.625rem 0.75rem;
        }
        
        /* Perbaiki spacing alert */
        .alert {
            padding: 0.875rem;
            margin-bottom: 1rem;
        }
        
        /* Tingkatkan kontrast */
        .list-group-item + .list-group-item {
            border-top: 1px solid rgba(0,0,0,0.125);
        }
    }
    
    /* Tambahkan spacing antar komponen dalam list-group-item */
    .list-group-item > div:not(:last-child) {
        margin-bottom: 0.5rem;
    }
    
    /* Perbaikan untuk badge */
    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
    }
</style>

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
