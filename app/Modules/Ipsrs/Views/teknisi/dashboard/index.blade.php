@include('ipsrs::teknisi.dashboard._js')

<div class="container-fluid p-0">
    <div class="page-header d-flex flex-column mb-2">
        <div class="d-flex align-items-center justify-content-between m-3">
            <h2 class="page-title ml-3 mb-0 fw-bold">Dashboard Teknisi</h2>
            <button onclick="refreshDashboard()" class="btn btn-outline-primary btn-icon rounded-circle">
                <i class="fas fa-sync"></i>
            </button>
        </div>
    </div>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-title">Terjadi Kesalahan</h4>
            <div><?= $error_message ?></div>
        </div>
    <?php endif ?>

    <?php if (isset($tugas_baru_count) && $tugas_baru_count > 0): ?>
        <div class="alert alert-info d-flex flex-column flex-md-row align-items-md-center justify-content-md-between" role="alert">
            <div>
                <h4 class="alert-title mb-1"><?= $tugas_baru_count ?> tugas baru!</h4>
                <div>Segera periksa daftar tugas Anda</div>
            </div>
            <a href="<?= url('ipsrs/teknisitugas') ?>?n=<?= request('n') ?>" class="btn btn-primary mt-2 mt-md-0">
                <i class="fas fa-clipboard-list me-1"></i> Lihat Tugas
            </a>
        </div>
    <?php endif ?>

    <!-- Statistik Ringkasan -->
    <div class="row g-2 mb-2">
        <?php foreach([
            ['count' => $tugas_baru_count ?? 0, 'label' => 'Tugas Baru', 'icon' => 'clipboard-list', 'bg' => 'primary text-white'],
            ['count' => isset($tugas_aktif_list) && is_array($tugas_aktif_list) ? count($tugas_aktif_list) : 0, 'label' => 'Pekerjaan Aktif', 'icon' => 'tools', 'bg' => 'success text-white'],
            ['count' => $tugas_selesai_count ?? 0, 'label' => 'Selesai Bulan Ini', 'icon' => 'check-circle', 'bg' => 'info text-white'],
            ['count' => $tugas_mendesak_count ?? 0, 'label' => 'Prioritas Tinggi', 'icon' => 'clock', 'bg' => 'warning text-white'],
        ] as $stat): ?>
        <div class="col-6 col-md-3">
            <div class="card mobile-card shadow-sm">
                <div class="card-body p-2 px-3 d-flex align-items-center">
                    <div class="avatarlogo bg-<?= $stat['bg'] ?> me-2 flex-shrink-0">
                        <i class="fas fa-<?= $stat['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5"><?= $stat['count'] ?></div>
                        <div class="text-muted text-truncate small"><?= $stat['label'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach ?>
    </div>

    <!-- Tugas Aktif Card -->
    <div class="card mt-2 shadow-sm">
        <div class="card-header py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0 fs-6 fw-bold">Tugas Sedang Dikerjakan</h3>
                <a href="<?= url('ipsrs/teknisitugas') ?>?n=<?= request('n') ?>" class="btn btn-sm btn-outline-primary">
                    Semua <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="list-group list-group-flush">
            <?php if (!empty($tugas_aktif_list)): ?>
                <?php foreach($tugas_aktif_list as $tugas): ?>
                <div class="list-group-item p-2" onclick="_modal(event, {uri: '<?= url('ipsrs/teknisitugas/detail/' . $tugas['penugasan_id']) ?>?n=<?= request('n') ?>', size: 'modal-lg', title: 'Detail Tugas'})">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-truncate"><?= $tugas['asset_nm'] ?? '-' ?></strong>
                        <?php if(isset($tugas['prioritas'])): ?>
                            <span class="badge bg-<?= $tugas['prioritas'] == 'tinggi' ? 'danger' : ($tugas['prioritas'] == 'sedang' ? 'warning' : 'info') ?>">
                                <?= ucfirst($tugas['prioritas']) ?>
                            </span>
                        <?php endif ?>
                    </div>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?= $tugas['lokasi_nm'] ?? '-' ?>
                    </div>
                    <div class="text-muted small">
                        <?= \Illuminate\Support\Str::limit($tugas['deskripsi'] ?? '-', 60) ?>
                    </div>
                </div>
                <?php endforeach ?>
            <?php else: ?>
                <div class="empty p-3">
                    <i class="fas fa-tools fa-2x text-muted mb-2"></i>
                    <div class="empty-title">Tidak ada pekerjaan aktif</div>
                </div>
            <?php endif ?>
        </div>
    </div>

    <!-- Jadwal Pemeliharaan Card -->
    <div class="card mt-2 shadow-sm">
        <div class="card-header py-2">
            <h3 class="card-title m-0 fs-6 fw-bold">Jadwal Pemeliharaan Mendatang</h3>
        </div>
        <div class="list-group list-group-flush">
            <?php if(isset($jadwal_mendatang) && is_array($jadwal_mendatang) && count($jadwal_mendatang) > 0): ?>
                <?php foreach($jadwal_mendatang as $jadwal): ?>
                    <div class="list-group-item p-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-truncate"><?= $jadwal['asset_nm'] ?? '-' ?></strong>
                            <span class="badge bg-danger"><?= isset($jadwal['tgl_jadwal']) ? to_date($jadwal['tgl_jadwal']) : '-' ?></span>
                        </div>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?= $jadwal['lokasi_nm'] ?? '-' ?>
                        </div>
                        <div class="text-muted small">
                            <?= \Illuminate\Support\Str::limit($jadwal['deskripsi'] ?? 'Pemeliharaan Rutin', 60) ?>
                        </div>
                    </div>
                <?php endforeach ?>
            <?php else: ?>
                <div class="empty p-3">
                    <i class="fas fa-calendar-alt fa-2x text-muted mb-2"></i>
                    <div class="empty-title">Tidak ada jadwal mendatang</div>
                </div>
            <?php endif ?>
        </div>
    </div>

    <!-- Grafik Kinerja -->
    <div class="card mt-2 shadow-sm">
        <div class="card-header py-2">
            <h3 class="card-title m-0 fs-6 fw-bold">Kinerja Bulan Ini</h3>
        </div>
        <div class="card-body py-2">
            <div style="height: 180px;">
                <canvas id="chart-kinerja"></canvas>
            </div>
        </div>
    </div>

    <!-- Sparepart Card -->
    <div class="card mt-2 mb-2 shadow-sm">
        <div class="card-header py-2">
            <h3 class="card-title m-0 fs-6 fw-bold">Sparepart Sering Digunakan</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter table-sm">
                <thead>
                    <tr>
                        <th>Nama Sparepart</th>
                        <th class="text-center">Penggunaan</th>
                        <th class="text-center">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($top_spareparts) && is_array($top_spareparts) && count($top_spareparts) > 0): ?>
                        <?php foreach($top_spareparts as $part): ?>
                            <tr>
                                <td><?= $part['sparepart_nm'] ?? '-' ?></td>
                                <td class="text-center"><?= $part['jumlah_pakai'] ?? 0 ?></td>
                                <td class="text-center">
                                    <?php if(isset($part['stok']) && isset($part['stok_min']) && $part['stok'] <= $part['stok_min']): ?>
                                        <span class="badge bg-danger"><?= $part['stok'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= $part['stok'] ?? 0 ?></span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Belum ada data penggunaan sparepart</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .mobile-card { height: 100%; margin-bottom: 0.5rem; }
    .avatarlogo { width: 2.2rem; height: 2.2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.1rem; margin-right: 0.7rem; }
    .card { margin-bottom: 0.7rem; border-radius: 0.375rem; overflow: hidden; }
    .card-header { padding: 0.6rem 1rem; background-color: rgba(0,0,0,0.03); border-bottom: 1px solid rgba(0,0,0,0.125); }
    .card-title { margin-bottom: 0; font-size: 0.95rem; }
    .list-group-item { cursor: pointer; transition: background-color 0.2s; padding: 0.7rem 1rem; }
    .list-group-item:active { background-color: #f8f9fa; }
    .empty { text-align: center; padding: 2rem 0; color: #6c757d; }
    .empty-img { margin-bottom: 1rem; }
    .empty-title { font-size: 1rem; font-weight: 300; margin-bottom: 0.5rem; }
    .badge { padding: 0.3em 0.6em; font-weight: 500; font-size: 0.85em; }
    .table th, .table td { padding: 0.5rem 0.7rem; }
    @media (max-width: 767.98px) {
        .container-fluid { padding: 0 0.5rem; }
        .page-header { padding: 0.5rem 0.5rem; margin-bottom: 0.7rem; }
        .card-header { padding: 0.5rem 1rem; }
        .card-body { padding: 0.7rem; }
        .mobile-card .card-body { padding: 0.5rem; }
        .avatarlogo { width: 2rem; height: 2rem; margin-right: 0.6rem; }
        .list-group-item { padding: 0.6rem 0.8rem; }
        .table th, .table td { padding: 0.4rem 0.6rem; }
        .alert { padding: 0.7rem; margin-bottom: 0.7rem; }
    }
</style>

