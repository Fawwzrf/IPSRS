{{-- filepath: c:\laragon\www\ipsrs\app\Modules\Ipsrs\Views\admin\pekerjaan\log_status\form_modal.blade.php --}}
<div class="card">
    <div class="card-header bg-light">
        <h5>Riwayat Status Order Kerja: <strong><?= $order_kerja['order_kerja_id'] ?></strong></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th width="15%">Tanggal</th>
                        <th width="15%">Status Sebelumnya</th>
                        <th width="15%">Status Baru</th>
                        <th width="15%">Oleh</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($riwayat) && count($riwayat) > 0): ?>
                        <?php foreach ($riwayat as $key => $item): ?>
                            <tr>
                                <td class="text-center"><?= $key + 1 ?></td>
                                <td><?= to_date($item['tgl_perubahan'], '-', 'full_date') ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= ucfirst($item['status_lama']) ?></span>
                                </td>
                                <td>
                                    <?php
                                        $badgeClass = 'bg-secondary';
                                        switch ($item['status_baru']) {
                                            case 'menunggu':
                                                $badgeClass = 'bg-warning';
                                                break;
                                            case 'diproses':
                                                $badgeClass = 'bg-info';
                                                break;
                                            case 'selesai':
                                                $badgeClass = 'bg-success';
                                                break;
                                            case 'dibatalkan':
                                                $badgeClass = 'bg-danger';
                                                break;
                                            case 'menunggu_sparepart':
                                                $badgeClass = 'bg-primary';
                                                break;
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($item['status_baru']) ?></span>
                                </td>
                                <td><?= isset($item['pegawai_nm']) ? $item['pegawai_nm'] : '-' ?></td>
                                <td><?= isset($item['catatan']) ? $item['catatan'] : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data riwayat status</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>