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
                        @if (isset($order_kerja_id) && $order_kerja_id)
                            <button type="button" class="btn btn-success"
                                onclick="_modal(event, {
                                    uri: '{{ url('ipsrs/teknisitugas/form_log_kerja/' . $order_kerja_id) }}?n={{ $n_param ?? '' }}',
                                    size: 'modal-lg'
                                })">
                                <i class="fas fa-plus me-2"></i> Tambah Log Kerja
                            </button>
                        @endif

                        <a href="{{ url('ipsrs/teknisitugas') }}{{ isset($n_param) ? '?n=' . $n_param : '' }}"
                            class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Tugas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body mt-2">
        <div class="container-xl">
            @if (isset($asset) && $asset)
                <div class="row row-cards">
                    <!-- Detail Aset Card -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Informasi Aset</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td style="width: 30%"><strong>Kode Aset</strong></td>
                                                    <td>: {{ $asset['asset_id'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nama Aset</strong></td>
                                                    <td>: {{ $asset['asset_nm'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kategori</strong></td>
                                                    <td>: {{ $asset['kategori_asset_nm'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Merk/Type</strong></td>
                                                    <td>: {{ $asset['merk'] ?? '-' }} / {{ $asset['type'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Perolehan</strong></td>
                                                    <td>: {{ $asset['perolehan_tgl'] ?? '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td style="width: 30%"><strong>Serial Number</strong></td>
                                                    <td>: {{ $asset['no_seri'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Lokasi</strong></td>
                                                    <td>: {{ $asset['lokasi_nm'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status</strong></td>
                                                    <td>:
                                                        @if (isset($asset['status']))
                                                            @if ($asset['status'] == 'baik')
                                                                <span class="badge bg-success">Baik</span>
                                                            @elseif($asset['status'] == 'rusak')
                                                                <span class="badge bg-danger">Rusak</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-warning">{{ $asset['status'] }}</span>
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Terakhir Update</strong></td>
                                                    <td>:
                                                        {{ isset($asset['updated_at']) ? to_date($asset['updated_at'], '-', 'datetime') : '-' }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Pekerjaan -->
                    <div class="col-12 mt-3">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Riwayat Pemeliharaan & Perbaikan</h3>
                            </div>
                            <div class="card-body">
                                <!-- Bagian tabel riwayat -->
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Waktu Dimulai</th>
                                                <th>Waktu Selesai</th>
                                                <th>Jenis</th>
                                                <th>Teknisi</th>
                                                <th>Deskripsi</th>
                                                <th>Tindakan</th>
                                                <th>Sparepart Digunakan</th>
                                                <th>Total Biaya</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (count($log_kerja_list) > 0)
                                                @foreach ($log_kerja_list as $item)
                                                    <tr class="row-detail-riwayat" style="cursor:pointer"
                                                        data-order-kerja-id="{{ $item['order_kerja_id'] ?? '' }}">
                                                        <td>
                                                            @if ($item['tgl_mulai'])
                                                                {{ date('H:i', strtotime($item['tgl_mulai'])) }} -
                                                                {{ date('d-m-Y', strtotime($item['tgl_mulai'])) }}
                                                            @elseif($item['tgl_dibuat'])
                                                                {{ date('H:i', strtotime($item['tgl_dibuat'])) }} -
                                                                {{ date('d-m-Y', strtotime($item['tgl_dibuat'])) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item['tgl_selesai'])
                                                                {{ date('H:i', strtotime($item['tgl_selesai'])) }} -
                                                                {{ date('d-m-Y', strtotime($item['tgl_selesai'])) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($item['jenis']))
                                                                {{ $item['jenis'] == 'trx_jadwal_pm' ? 'Jadwal PM' : ($item['jenis'] == 'trx_order_kerja' ? 'Perbaikan' : ucfirst($item['jenis'])) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{ $item['teknisi_nama'] ?? '-' }}</td>
                                                        <td>{{ $item['deskripsi'] ?? ($item['diagnosa'] ?? '-') }}</td>
                                                        <td>{{ $item['tindakan'] ?? '-' }}</td>
                                                        <td>
                                                            @if (!empty($item['sparepart']))
                                                                <ul class="mb-0 ps-3">
                                                                    @foreach ($item['sparepart'] as $sp)
                                                                        <li>{{ $sp['sparepart_nm'] ?? '-' }}
                                                                            ({{ $sp['jumlah'] ?? 0 }})
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $biaya_sparepart = $item['total_biaya_sparepart'] ?? 0;
                                                                $biaya_lain = $item['total_biaya_lain'] ?? 0;
                                                                $total_biaya = $biaya_sparepart + $biaya_lain;
                                                            @endphp
                                                            {{ number_format($total_biaya, 0, ',', '.') }}
                                                        </td>
                                                        <td>
                                                            @if (isset($item['status']))
                                                                @if ($item['status'] == 'selesai')
                                                                    <span class="badge bg-success">Selesai</span>
                                                                @elseif($item['status'] == 'baru')
                                                                    <span class="badge bg-primary">Baru</span>
                                                                @elseif($item['status'] == 'ditugaskan')
                                                                    <span class="badge bg-info">Ditugaskan</span>
                                                                @elseif($item['status'] == 'diproses')
                                                                    <span class="badge bg-warning">Diproses</span>
                                                                @else
                                                                    <span
                                                                        class="badge bg-secondary">{{ $item['status'] }}</span>
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="9" class="text-center">Tidak ada data riwayat</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Tambahan Jika Ada -->
                    @if (isset($asset['keterangan']) && !empty($asset['keterangan']))
                        <div class="col-12 mt-3">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Keterangan Tambahan</h3>
                                </div>
                                <div class="card-body">
                                    <div class="text-muted">
                                        {{ $asset['keterangan'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="empty">
                    <div class="empty-icon">
                        <i class="fas fa-exclamation-circle fa-3x text-warning"></i>
                    </div>
                    <p class="empty-title">Data aset tidak ditemukan</p>
                    <p class="empty-subtitle text-muted">
                        Aset dengan ID tersebut tidak tersedia di database.
                    </p>
                    <div class="empty-action">
                        <a href="{{ url('ipsrs/teknisitugas') }}{{ isset($n_param) ? '?n=' . $n_param : '' }}"
                            class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Tugas
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cek jika ada flash message 'success' dari session Laravel
        @if (session('flash_success') || session('success'))
            _toast('success', '{{ session('flash_success') ?? session('success') }}');
        @endif

        @if (session('flash_error') || session('error'))
            _toast('error', '{{ session('flash_error') ?? session('error') }}');
        @endif

        // Handler klik pada baris riwayat
        // Ambil nilai parameter n dari PHP dan simpan ke JS variable
        var nParam = @json(request('n'));

        $('.row-detail-riwayat').on('click', function(e) {
            var orderKerjaId = $(this).data('order-kerja-id');
            if (!orderKerjaId) return;

            // Gunakan fungsi _modal seperti di log kerja admin
            _modal(e, {
                uri: _base_url + 'ipsrs/adminorderkerja/hasil_teknisi_modal/' + orderKerjaId + (
                    nParam ? '?n=' + nParam : ''),
                title: 'Hasil Tugas Teknisi',
                size: 'modal-lg'
            });
        });
    });
</script>
