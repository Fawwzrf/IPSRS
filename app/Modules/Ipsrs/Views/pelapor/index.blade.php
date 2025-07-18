<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Portal Pelapor Kerusakan & Komplain
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    {{-- Tombol untuk membuka modal form komplain --}}
                    <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url('ipsrs/pelapor/form_komplain_modal') }}', size: 'modal-lg'})" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Laporan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Laporan Saya</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table" id="table-komplain">
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
                            @forelse($history as $item)
                            <tr>
                                <td>{{ to_date($item['tgl']) }}</td>
                                <td>{{ $item['asset_nm'] }}</td>
                                <td>{{ $item['lokasi_nm'] }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($item['deskripsi'], 150) }}</td>
                                <td>
                                    @php
                                        $status = strtolower($item['status']);
                                        $badgeClass = \App\Modules\Ipsrs\Models\PelaporModel::getStatusBadgeClass($status);
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Anda belum pernah membuat laporan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    @if(isset($pagination) && $pagination['total'] > $pagination['per_page'])
                    <div class="mt-3">
                        <ul class="pagination justify-content-center">
                            {{-- First Page --}}
                            <li class="page-item {{ $pagination['current_page'] == 1 ? 'disabled' : '' }}">
                                <a class="page-link" href="javascript:void(0)" onclick="changePage(1)">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                            
                            {{-- Previous Page --}}
                            <li class="page-item {{ $pagination['current_page'] == 1 ? 'disabled' : '' }}">
                                <a class="page-link" href="javascript:void(0)" onclick="changePage({{ $pagination['current_page'] - 1 }})">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                            
                            {{-- Page Numbers --}}
                            @php
                                $start = max(1, $pagination['current_page'] - 2);
                                $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                            @endphp
                            
                            @for($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $pagination['current_page'] == $i ? 'active' : '' }}">
                                    <a class="page-link" href="javascript:void(0)" onclick="changePage({{ $i }})">{{ $i }}</a>
                                </li>
                            @endfor
                            
                            {{-- Next Page --}}
                            <li class="page-item {{ $pagination['current_page'] == $pagination['last_page'] ? 'disabled' : '' }}">
                                <a class="page-link" href="javascript:void(0)" onclick="changePage({{ $pagination['current_page'] + 1 }})">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                            
                            {{-- Last Page --}}
                            <li class="page-item {{ $pagination['current_page'] == $pagination['last_page'] ? 'disabled' : '' }}">
                                <a class="page-link" href="javascript:void(0)" onclick="changePage({{ $pagination['last_page'] }})">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <script>
                        function changePage(page) {
                            let url = 'ipsrs/pelapor/get_table_data?page=' + page;
                            const currentToken = getParameterByName('n', window.location.href);
                            if (currentToken) {
                                url += '&n=' + currentToken;
                            }
                            
                            const tableContainer = $('#table-komplain').closest('.table-container');
                            tableContainer.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Memuat data...</div>');
                            
                            $.ajax({
                                url: url,
                                method: 'GET',
                                success: function(html) {
                                    tableContainer.html(html);
                                },
                                error: function() {
                                    tableContainer.html('<div class="alert alert-danger">Gagal memuat data</div>');
                                }
                            });
                        }
                    </script>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>