@php
    function getPrioritasBadge($prioritas)
    {
        switch (strtolower($prioritas)) {
            case 'mendesak':
                return '<span class="badge bg-warning me-1">Mendesak</span>';
            case 'darurat':
                return '<span class="badge bg-danger me-1">Darurat</span>';
            default:
                return '<span class="badge bg-secondary me-1">Normal</span>';
        }
    }
@endphp

@include('ipsrs::teknisi.tugas._js')

<div class="container-fluid p-0">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="page-header d-flex flex-column">
        <h2 class="page-title m-3">Daftar Tugas Saya</h2>
    </div>

    <!-- Tab Navigation Scrollable untuk Mobile -->
    <div class="card mb-3">
        <div class="card-header mx-3 p-0">
            <div class="nav-scroller">
                <ul class="nav nav-tabs nav-fill" data-bs-toggle="tabs">
                    <li class="nav-item">
                        <a href="#tabs-baru" class="nav-link active" data-bs-toggle="tab">
                            <i class="fas fa-clipboard-list me-1 d-md-none"></i>
                            <span>Tugas Baru</span>
                            @if(count($list_tugas_baru) > 0)
                                <span class="badge bg-danger ms-1">{{ count($list_tugas_baru) }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#tabs-dikerjakan" class="nav-link" data-bs-toggle="tab">
                            <i class="fas fa-tools me-1 d-md-none"></i>
                            <span>Dikerjakan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#tabs-selesai" class="nav-link" data-bs-toggle="tab">
                            <i class="fas fa-check me-1 d-md-none"></i>
                            <span>Selesai</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#tabs-ditolak" class="nav-link" data-bs-toggle="tab">
                            <i class="fas fa-times me-1 d-md-none"></i>
                            <span>Ditolak</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                {{-- Tab untuk Tugas Baru --}}
                <div class="tab-pane active show" id="tabs-baru">
                    <div class="list-group list-group-flush">
                        @forelse($list_tugas_baru as $tugas)
                            <div class="list-group-item p-3" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_detail_modal/' . $tugas['penugasan_id']) }}', size: 'modal-lg', title: 'Detail Tugas'})">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-truncate">{{ $tugas['asset_nm'] }}</strong>
                                    <div>{!! getPrioritasBadge($tugas['prioritas']) !!}</div>
                                </div>
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $tugas['lokasi_nm'] }}
                                </div>
                                {{-- Tampilkan Sumber Pekerjaan --}}
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @if (!empty($tugas['permintaan_id']))
                                        <span class="badge bg-azure-lt">Komplain</span>
                                    @elseif(!empty($tugas['jadwal_pm_id']))
                                        <span class="badge bg-purple-lt">Jadwal PM</span>
                                    @endif
                                </div>
                                <p class="mb-0 text-muted small">
                                    {{ \Illuminate\Support\Str::limit($tugas['deskripsi'], 80) }}
                                </p>
                            </div>
                        @empty
                            <div class="empty p-4">
                                <div class="empty-img">
                                    <i class="fas fa-clipboard fa-3x text-muted"></i>
                                </div>
                                <p class="empty-title">Tidak ada tugas baru</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab untuk Tugas Sedang Dikerjakan --}}
                <div class="tab-pane" id="tabs-dikerjakan">
                    <div class="list-group list-group-flush">
                        @forelse($list_tugas_dikerjakan as $tugas)
                            <div class="list-group-item p-3" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_detail_modal/' . $tugas['penugasan_id']) }}', size: 'modal-lg', title: 'Detail Tugas'})">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-truncate">{{ $tugas['asset_nm'] }}</strong>
                                    <div>{!! getPrioritasBadge($tugas['prioritas']) !!}</div>
                                </div>
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $tugas['lokasi_nm'] }}
                                </div>
                                {{-- Tampilkan Sumber Pekerjaan --}}
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @if(!empty($tugas['permintaan_id']))
                                        <span class="badge bg-azure-lt">Komplain</span>
                                    @elseif(!empty($tugas['jadwal_pm_id']))
                                        <span class="badge bg-purple-lt">Jadwal PM</span>
                                    @endif
                                </div>
                                <p class="mb-0 text-muted small">
                                    {{ \Illuminate\Support\Str::limit($tugas['deskripsi'], 80) }}
                                </p>
                            </div>
                        @empty
                            <div class="empty p-4">
                                <div class="empty-img">
                                    <i class="fas fa-tools fa-3x text-muted"></i>
                                </div>
                                <p class="empty-title">Tidak ada tugas yang sedang dikerjakan</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab untuk Tugas Selesai --}}
                <div class="tab-pane" id="tabs-selesai">
                    <div class="list-group list-group-flush">
                        @forelse($list_tugas_selesai as $tugas)
                            <div class="list-group-item p-3" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_detail_modal/' . $tugas['penugasan_id']) }}', size: 'modal-lg', title: 'Detail Tugas'})">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-truncate">{{ $tugas['asset_nm'] }}</strong>
                                    <span class="badge bg-success">Selesai</span>
                                </div>
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $tugas['lokasi_nm'] }}
                                </div>
                                <p class="mb-0 text-muted small">
                                    {{ \Illuminate\Support\Str::limit($tugas['deskripsi'], 80) }}
                                </p>
                            </div>
                        @empty
                            <div class="empty p-4">
                                <div class="empty-img">
                                    <i class="fas fa-check-circle fa-3x text-muted"></i>
                                </div>
                                <p class="empty-title">Belum ada tugas yang selesai</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab untuk Tugas Ditolak --}}
                <div class="tab-pane" id="tabs-ditolak">
                    <div class="list-group list-group-flush">
                        @forelse($list_tugas_ditolak as $tugas)
                            <div class="list-group-item p-3" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_detail_modal/' . $tugas['penugasan_id']) }}', size: 'modal-lg', title: 'Detail Tugas'})">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-truncate">{{ $tugas['asset_nm'] }}</strong>
                                    <span class="badge bg-danger">Ditolak</span>
                                </div>
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $tugas['lokasi_nm'] }}
                                </div>
                                <p class="mb-0 text-muted small">
                                    Alasan: {{ $tugas['catatan_penolakan'] ?? 'Tidak ada alasan.' }}
                                </p>
                            </div>
                        @empty
                            <div class="empty p-4">
                                <div class="empty-img">
                                    <i class="fas fa-ban fa-3x text-muted"></i>
                                </div>
                                <p class="empty-title">Tidak ada tugas yang ditolak</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Style untuk mobile-friendly dengan perbaikan spacing */
    .nav-scroller {
        position: relative;
        z-index: 2;
        overflow-y: hidden;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1px;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Perbaikan padding untuk tab content */
    .tab-content {
        padding-bottom: 1rem;
    }
    
    /* Perbaikan untuk list item */
    .list-group-item {
        cursor: pointer;
        transition: background-color 0.2s;
        padding: 1rem;
        border-left: none;
        border-right: none;
    }
    .list-group-item:active {
        background-color: #f8f9fa;
    }
    
    /* Spacing dalam list item */
    .list-group-item > div {
        margin-bottom: 0.5rem;
    }
    .list-group-item > div:last-child {
        margin-bottom: 0;
    }
    
    /* Perbaikan untuk empty state */
    .empty {
        text-align: center;
        padding: 2.5rem 0;
        color: #6c757d;
    }
    .empty-img {
        margin-bottom: 1.25rem;
    }
    .empty-title {
        font-size: 1.25rem;
        font-weight: 300;
    }
    
    /* Badge styling */
    .badge {
        padding: 0.4em 0.6em;
        font-size: 85%;
    }
    
    /* Card padding dan spacing */
    .card {
        margin-bottom: 1rem;
    }
    .card-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }
    .page-header {
        margin-bottom: 1rem;
    }
    
    @media (max-width: 767.98px) {
        .page-title {
            font-size: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .card-header {
            padding: 0.75rem 1rem;
        }
        
        .list-group-item {
            padding: 1rem 0.875rem;
        }
        
        /* Vertikal spacing pada mobile */
        .d-flex.align-items-center.text-muted.small {
            margin-top: 0.35rem;
            margin-bottom: 0.35rem;
        }
        
        /* Tingkatkan ukuran tap target pada mobile */
        .nav-link {
            padding: 0.75rem 0.5rem;
            min-height: 44px;
        }
    }
    
    /* Separator untuk item-item dalam list */
    .list-group-item + .list-group-item {
        border-top-width: 1px;
    }
</style>
