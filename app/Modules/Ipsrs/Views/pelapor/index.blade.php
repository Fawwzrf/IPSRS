<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row g-2 mx-1 align-items-center">
                <div class="col">
                    <h2 class="page-title mb-1">
                        Portal Pelapor Kerusakan & Komplain
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="javascript:void(0)"
                        onclick="_modal(event, {uri: '<?= url('ipsrs/pelapor/form_komplain_modal') ?>', size: 'modal-lg'})"
                        class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Laporan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card mx-1 mb-3">
                <div class="card-header pb-2">
                    <h3 class="card-title mb-0">Riwayat Laporan Saya</h3>
                </div>
                <div class="table-responsive table-container px-2 pb-2 pt-2">
                    <table class="table table-vcenter card-table mb-0" id="table-komplain">
                        <thead>
                            <tr>
                                <th width="10%">Tanggal</th>
                                <th width="20%">Aset</th>
                                <th width="20%">Lokasi</th>
                                <th>Deskripsi</th>
                                <th width="10%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($history)): ?>
                            <?php foreach($history as $item): ?>
                            <tr>
                                <td class="align-middle"><?= to_date($item['tgl']) ?></td>
                                <td class="align-middle"><?= $item['asset_nm'] ?></td>
                                <td class="align-middle"><?= $item['lokasi_nm'] ?></td>
                                <td class="align-middle"><?= \Illuminate\Support\Str::limit($item['deskripsi'], 150) ?>
                                </td>
                                <td class="align-middle">
                                    <?php
                                    $status = strtolower($item['status']);
                                    $badgeClass = \App\Modules\Ipsrs\Models\PelaporModel::getStatusBadgeClass($status);
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Anda belum pernah membuat
                                    laporan.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if(isset($pagination) && $pagination['total'] > $pagination['per_page']): ?>
                    <div class="mt-3">
                        <ul class="pagination justify-content-center flex-wrap gap-1">
                            <li class="page-item <?= $pagination['current_page'] == 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="javascript:void(0)" onclick="changePage(1)">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item <?= $pagination['current_page'] == 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="javascript:void(0)"
                                    onclick="changePage(<?= $pagination['current_page'] - 1 ?>)">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                            <?php
                            $start = max(1, $pagination['current_page'] - 2);
                            $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                            ?>

                            <?php for($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>">
                                <a class="page-link" href="javascript:void(0)"
                                    onclick="changePage(<?= $i ?>)"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li
                                class="page-item <?= $pagination['current_page'] == $pagination['last_page'] ? 'disabled' : '' ?>">
                                <a class="page-link" href="javascript:void(0)"
                                    onclick="changePage(<?= $pagination['current_page'] + 1 ?>)">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                            <li
                                class="page-item <?= $pagination['current_page'] == $pagination['last_page'] ? 'disabled' : '' ?>">
                                <a class="page-link" href="javascript:void(0)"
                                    onclick="changePage(<?= $pagination['last_page'] ?>)">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        margin-bottom: 1.25rem;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .page-title {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .btn-primary {
        margin-left: 0.5rem;
    }

    .card {
        margin-bottom: 1.25rem;
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.25rem 0.75rem 1.25rem;
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        margin-bottom: 0.25rem;
    }

    .card-title {
        margin-bottom: 0;
        font-size: 1.1rem;
    }

    .table-responsive {
        padding: 0.75rem 0.75rem 0.75rem 0.75rem;
    }

    .table th,
    .table td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
    }

    .table tr+tr {
        border-top: 1px solid #eee;
    }

    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.95em;
    }

    .pagination {
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        gap: 0.25rem;
    }

    .pagination .page-link {
        padding: 0.5rem 0.75rem;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .table-container {
        margin-bottom: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .container-xl {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .page-header {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .page-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .card-header {
            padding: 0.75rem 0.75rem 0.5rem 0.75rem;
        }

        .table-responsive {
            padding: 0.25rem 0.25rem 0.5rem 0.25rem;
        }

        .table th,
        .table td {
            padding: 0.65rem 0.5rem;
        }

        .pagination {
            margin-top: 1rem;
        }
    }
</style>
