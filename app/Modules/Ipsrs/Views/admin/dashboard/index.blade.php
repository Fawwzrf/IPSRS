
<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Dashboard Operasional IPSRS
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            {{-- Bagian Widget Ringkasan --}}
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-danger text-white avatar">
                                        <i class="fas fa-exclamation-triangle fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $count_komplain_baru }} Komplain Baru
                                    </div>
                                    <div class="text-muted">
                                        Menunggu untuk dibuatkan Order Kerja
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
                                        <i class="fas fa-calendar-alt fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $count_jadwal_belum_ok }} Jadwal PM
                                    </div>
                                    <div class="text-muted">
                                        Menunggu dibuatkan Order Kerja
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
                                        <i class="fas fa-tasks fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $count_order_kerja_aktif }} Order Kerja Aktif
                                    </div>
                                    <div class="text-muted">
                                        Dalam proses pengerjaan
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
                                    <span class="bg-primary text-white avatar">
                                        <i class="fas fa-toolbox fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $count_aset_perbaikan }} Aset Dalam Perbaikan
                                    </div>
                                    <div class="text-muted">
                                        Status aset sedang perbaikan
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
                                    <span class="bg-secondary text-white avatar">
                                        <i class="fas fa-box-open fa-fw"></i>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">
                                        {{ $count_sparepart_kritis }} Sparepart Kritis
                                    </div>
                                    <div class="text-muted">
                                        Stok di bawah batas minimum
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Grafik --}}
            <div class="row g-4 mt-2">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Tren Komplain Harian (7 Hari Terakhir)</h3>
                            <div id="chart-komplain-harian" class="chart-lg"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Bagian Tabel Pekerjaan Mendesak --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pekerjaan dengan Prioritas "Darurat"</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID Order Kerja</th>
                                        <th>Aset</th>
                                        <th>Deskripsi Komplain</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($urgent_jobs as $job)
                                    <tr>
                                        <td><span class="text-muted">{{ $job['order_kerja_id'] }}</span></td>
                                        <td>{{ $job['asset_nm'] }}</td>
                                        <td>{{ $job['deskripsi'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Tidak ada pekerjaan darurat saat ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk render chart (menggunakan ApexCharts sebagai contoh) --}}
{{-- Pastikan library ApexCharts sudah dimuat di layout utama Anda --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Pastikan elemen chart ada sebelum mencoba merendernya
    if(document.getElementById('chart-komplain-harian')) {
        var chartData = @json($chart_komplain_harian);
        
        // Memformat data untuk ApexCharts
        var seriesData = chartData.map(item => item.jumlah);
        var categoriesData = chartData.map(item => {
            // Format tanggal agar lebih mudah dibaca
            let date = new Date(item.tanggal);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });

        var options = {
            chart: {
                type: 'area',
                height: 350,
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            series: [{
                name: 'Jumlah Komplain',
                data: seriesData
            }],
            xaxis: {
                categories: categoriesData
            },
            tooltip: {
                x: {
                    format: 'dd MMMM yyyy'
                },
            },
        };

        var chart = new ApexCharts(document.querySelector("#chart-komplain-harian"), options);
        chart.render();
    }
});
</script>
