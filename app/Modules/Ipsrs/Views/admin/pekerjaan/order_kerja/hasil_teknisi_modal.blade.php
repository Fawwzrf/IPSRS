<div class="modal-header">
    <h5 class="modal-title">Hasil Tugas Teknisi</h5>
</div>
<div class="modal-body">
    <fieldset class="border p-2 rounded mb-3">
        <legend class="float-none w-auto px-2 fs-6 fw-bold">Penolakan Tugas</legend>
        <?php $ada_penolakan = false; ?>
        <ul>
            <?php foreach($penugasan as $pt): ?>
                <?php if($pt['status'] == 'dibatalkan' && !empty($pt['catatan_penolakan'])): ?>
                    <?php $ada_penolakan = true; ?>
                    <li>
                        <strong><?= $pt['pegawai_nm'] ?></strong>:
                        <?= $pt['catatan_penolakan'] ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if(!$ada_penolakan): ?>
                <li class="text-muted">Tidak ada penolakan tugas.</li>
            <?php endif; ?>
        </ul>
    </fieldset>

    <fieldset class="border p-2 rounded mb-3">
        <legend class="float-none w-auto px-2 fs-6 fw-bold">Log Kerja Teknisi</legend>
        <?php if(!empty($log_kerja)): ?>
            <?php foreach($log_kerja as $log): ?>
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><?= $log['pegawai_nm'] ?? '-' ?></strong>
                        <span class="badge bg-info ms-2"><?= ucfirst($log['hasil'] ?? '-') ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td width="40%">Diagnosa</td>
                                        <td>: <?= $log['diagnosa'] ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tindakan</td>
                                        <td>: <?= $log['tindakan'] ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Durasi</td>
                                        <td>: <?= $log['durasi_menit'] ?? 0 ?> menit</td>
                                    </tr>
                                    <tr>
                                        <td>Biaya Tambahan</td>
                                        <td>: Rp <?= number_format($log['total_biaya'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="mt-2">
                            <!-- Sparepart -->
                            <label class="fw-bold mb-1">Sparepart Digunakan:</label>
                            <?php if(!empty($log['sparepart'])): ?>
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nama Sparepart</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($log['sparepart'] as $sp): ?>
                                            <tr>
                                                <td><?= $sp['sparepart_nm'] ?? '-' ?></td>
                                                <td><?= $sp['jumlah'] ?? 0 ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <span class="text-muted">Tidak ada sparepart digunakan.</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <!-- Foto -->
                            <label class="fw-bold mb-1">Bukti Foto:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if(!empty($log['fotos'])): ?>
                                    <?php foreach($log['fotos'] as $foto): ?>
                                        <?php if($foto['foto_url']): ?>
                                            <a href="<?= $foto['foto_url'] ?>" target="_blank">
                                                <img src="<?= $foto['foto_url'] ?>" alt="Foto" class="img-thumbnail" style="max-width:500px;max-height:500px;">
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if(empty($log['fotos'])): ?>
                                        <span class="text-muted">Tidak ada foto.</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada foto.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-muted">Belum ada log kerja teknisi.</div>
        <?php endif; ?>
    </fieldset>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>