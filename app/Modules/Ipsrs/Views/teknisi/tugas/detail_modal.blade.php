<div class="modal-body">
    <div class="row">
        <div class="col-md-7">
            <h5 class="mb-3">Detail Pekerjaan</h5>
            <?php if ($tugas['jenis'] == 'perbaikan'): ?>
                {{-- Tampilan untuk jenis komplain/perbaikan --}}
                <dl class="row">
                    <dt class="col-4">Aset</dt>
                    <dd class="col-8"><?= $tugas['asset_nm'] ?? '-' ?></dd>
                    <dt class="col-4">Lokasi</dt>
                    <dd class="col-8"><?= $tugas['lokasi_nm'] ?? '-' ?></dd>
                    <dt class="col-4">Pelapor</dt>
                    <dd class="col-8"><?= $tugas['nama_pelapor'] ?? 'N/A' ?></dd>
                    <dt class="col-4">Deskripsi</dt>
                    <dd class="col-8"><?= $tugas['deskripsi'] ?? '-' ?></dd>
                </dl>
            <?php else: ?>
                {{-- Tampilan untuk jenis Jadwal PM/pemeliharaan --}}
                <dl class="row">
                    <dt class="col-4">Aset</dt>
                    <dd class="col-8"><?= $tugas['asset_nm'] ?? '-' ?></dd>
                    <dt class="col-4">Lokasi</dt>
                    <dd class="col-8"><?= $tugas['lokasi_nm'] ?? '-' ?></dd>
                    <dt class="col-4">Frekuensi</dt>
                    <dd class="col-8"><?= $tugas['frekuensi'] ?? '-' ?></dd>
                    <dt class="col-4">Jenis</dt>
                    <dd class="col-8"><?= $tugas['jenis_pemeliharaan'] ?? '-' ?></dd>
                    <dt class="col-4">Tgl. Pemeliharaan</dt>
                    <dd class="col-8"><?= $tugas['tgl_pemeliharaan'] ? to_date($tugas['tgl_pemeliharaan']) : '-' ?></dd>
                    <dt class="col-4">Deskripsi</dt>
                    <dd class="col-8"><?= $tugas['deskripsi_pemeliharaan'] ?? '-' ?></dd>
                </dl>
            <?php endif; ?>
            <?php if (!empty($tugas['order_kerja_id'])): ?>
                <?php
                    $orderKerja = \App\Modules\App\Models\DbModel::getData('trx_order_kerja', ['order_kerja_id' => $tugas['order_kerja_id']]);
                ?>
                <?php if (!empty($orderKerja['catatan'])): ?>
                    <div class="alert alert-info mt-3">
                        <strong>Catatan Admin:</strong> <?= $orderKerja['catatan'] ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="col-md-5">
            <h5 class="mb-3">Denah Lokasi</h5>
            <?php if (!empty($tugas['anotasi_url'])): ?>
                <img src="<?= $tugas['anotasi_url'] ?>" class="img-fluid rounded border" alt="Denah Lokasi">
            <?php else: ?>
                <div class="text-muted text-center p-4 border rounded">Denah tidak tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($tugas['status'] == 'dibatalkan' && !empty($tugas['catatan_penolakan'])): ?>
        <div class="alert alert-warning mt-3">
            <strong>Alasan Penolakan:</strong> <?= $tugas['catatan_penolakan'] ?>
        </div>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <?php if ($tugas['status'] == 'ditugaskan'): ?>
        <button type="button" class="btn btn-success btn-terima-tugas"
            data-url="<?= url('ipsrs/teknisitugas/terima') ?>" data-penugasan-id="<?= $tugas['penugasan_id'] ?>">
            <i class="fas fa-check"></i> Terima Tugas
        </button>

        <button type="button" class="btn btn-danger btn-tolak-tugas" data-penugasan-id="<?= $tugas['penugasan_id'] ?>"
            data-n="<?= request('n') ?>">
            <i class="fas fa-times"></i> Tolak Tugas
        </button>
    <?php elseif($tugas['status'] == 'dibatalkan'): ?>
        <button type="button" class="btn btn-info btn-ambil-kembali"
            data-url="<?= url('ipsrs/teknisitugas/ambil_kembali') ?>" data-penugasan-id="<?= $tugas['penugasan_id'] ?>">
            <i class="fas fa-undo"></i> Ambil Kembali Tugas
        </button>
    <?php elseif($tugas['status'] == 'sedang_dikerjakan'): ?>
        <button type="button" class="btn btn-danger"
            onclick="batalTerima('<?= $tugas['penugasan_id'] ?>')">
            <i class="fas fa-undo"></i> Batalkan Penerimaan
        </button>

        <a href="javascript:void(0)" class="btn btn-primary"
            onclick="openScanModal('<?= $tugas['order_kerja_id'] ?>', '<?= request('n') ?>')">
            <i class="fas fa-barcode"></i> Scan Barcode
        </a>
    <?php endif; ?>

    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times"></i> Tutup
    </button>
</div>
