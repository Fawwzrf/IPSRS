<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Detail & Riwayat Aset</div>
                    <h2 class="page-title">{{ $asset['asset_nm'] ?? 'Aset tidak ditemukan' }}</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <button onclick="window.location.href='{{ url('ipsrs/teknisitugas') }}?n={{ request('n') }}'" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Tugas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="row g-4">
                {{-- Kolom Kiri: Identitas Aset --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-id-card me-2"></i>Identitas Alat</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-5">ID Aset:</dt>
                                <dd class="col-7">{{ $asset['asset_id'] }}</dd>
                                <dt class="col-5">No. Seri:</dt>
                                <dd class="col-7">{{ $asset['no_seri'] ?? '-' }}</dd>
                                <dt class="col-5">Lokasi:</dt>
                                <dd class="col-7">{{ $asset['lokasi_nm'] ?? '-' }}</dd>
                                <dt class="col-5">Kategori:</dt>
                                <dd class="col-7">{{ $asset['kategori_asset_nm'] ?? '-' }}</dd>
                                <dt class="col-5">Merk:</dt>
                                <dd class="col-7">{{ $asset['merk'] ?? '-' }}</dd>
                                <dt class="col-5">Status:</dt>
                                <dd class="col-7"><span class="badge bg-success">{{ $asset['status'] }}</span></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Riwayat Pekerjaan --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history me-2"></i>Riwayat Pekerjaan</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal OK</th>
                                        <th>Jenis</th>
                                        <th>Deskripsi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history as $item)
                                        <tr>
                                            <td>{{ to_date($item['tgl_dibuat']) }}</td>
                                            <td>{{ ucfirst($item['jenis']) }}</td>
                                            <td>{{ $item['deskripsi'] }}</td>
                                            <td><span class="badge bg-secondary">{{ ucfirst($item['status']) }}</span>
                                            </td>
                                            <td>
                                                @if ($item['status'] !== 'selesai' && $item['status'] !== 'ditolak')
                                                    {{-- Tombol ini akan memanggil modal log kerja --}}
                                                    <button class="btn btn-sm btn-success"
                                                        onclick="_modal(event, {uri: '{{ url('ipsrs/adminlogkerja/form_modal/' . $item['order_kerja_id']) }}', size: 'modal-lg'})">
                                                        <i class="fas fa-clipboard-check"></i> Selesaikan & Buat Log
                                                    </button>
                                                @else
                                                    <i class="fas fa-check-circle text-success"></i> Selesai
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada riwayat pekerjaan untuk
                                                aset ini.</td>
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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cek jika ada flash message 'success' dari session Laravel
        @if (session('flash_success'))
            // Gunakan fungsi _toast yang sudah ada di sistem Anda
            _toast('success', "{{ session('flash_success') }}");
        @endif

        // Cek jika ada flash message 'error'
        @if (session('flash_error'))
            _toast('error', "{{ session('flash_error') }}");
        @endif
    });
</script>
