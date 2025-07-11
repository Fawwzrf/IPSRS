<div class="container-xl">
    <div class="row g-4">
        {{-- Kolom Kiri: Identitas Aset --}}
        <div class="col-lg-5">
            <div class="card card-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card me-2"></i>Identitas Alat</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-5">ID Aset:</dt>
                        <dd class="col-7">{{ $asset['asset_id'] }}</dd>
                        <dt class="col-5">No. Seri:</dt>
                        <dd class="col-7">{{ $asset['no_seri'] ?? '-' }}</dd>
                        <dt class="col-5">Merk:</dt>
                        <dd class="col-7">{{ $asset['merk'] ?? '-' }}</dd>
                        <dt class="col-5">Model:</dt>
                        <dd class="col-7">{{ $asset['model'] ?? '-' }}</dd>
                        <dt class="col-5">Tgl Perolehan:</dt>
                        <dd class="col-7">{{ @to_date($asset['perolehan_tgl']) ?? '-' }}</dd>
                        <dt class="col-5">Umur Aset:</dt>
                        <dd class="col-7">{{ $asset['umur_tahun'] ? $asset['umur_tahun'] . ' Tahun' : '-' }}</dd>
                        <dt class="col-5">Lokasi:</dt>
                        <dd class="col-7">{{ $asset['lokasi_nm'] ?? '-' }}</dd>
                        <dt class="col-5">Kategori:</dt>
                        <dd class="col-7">{{ $asset['kategori_asset_nm'] ?? '-' }}</dd>
                        <dt class="col-5">Status:</dt>
                        <dd class="col-7"><span class="badge bg-success">{{ $asset['status'] }}</span></dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Riwayat Pekerjaan --}}
        <div class="col-lg-7">
            <div class="card card-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history me-2"></i>Riwayat Pekerjaan</h3>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-vcenter card-table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th> {{-- Kolom Aksi ditambahkan --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $item)
                            <tr>
                                <td>{{ to_date($item['tgl_dibuat']) }}</td>
                                <td>{{ $item['deskripsi'] }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($item['status']) }}</span></td>
                                <td>
                                    @if($item['status'] == 'selesai')
                                        {{-- Tombol ini akan membuka modal laporan kerja --}}
                                        <button class="btn btn-sm btn-outline-info" onclick="_modal(event, {uri: '{{ url('ipsrs/adminlogkerja/form_view_log_modal/' . $item['order_kerja_id']) }}', size: 'modal-xl'})">
                                            Lihat Laporan
                                        </button>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada riwayat pekerjaan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>