@include('ipsrs::teknisi.tugas._js')

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

<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <h2 class="page-title">Daftar Tugas Saya</h2>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                        <li class="nav-item"><a href="#tabs-baru" class="nav-link active" data-bs-toggle="tab">Tugas Baru</a></li>
                        <li class="nav-item"><a href="#tabs-dikerjakan" class="nav-link" data-bs-toggle="tab">Sedang Dikerjakan</a></li>
                        <li class="nav-item"><a href="#tabs-ditolak" class="nav-link" data-bs-toggle="tab">Ditolak</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        {{-- Tab untuk Tugas Baru --}}
                        <div class="tab-pane active show" id="tabs-baru">
                            <div class="list-group">
                                @forelse($list_tugas_baru as $tugas)
                                    <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_detail_modal/' . $tugas['penugasan_id']) }}', size: 'modal-lg', title: 'Detail Tugas'})" class="list-group-item list-group-item-action">
                                        <strong class="d-block">{{ $tugas['asset_nm'] }}</strong>
                                        <small class="text-muted">{{ $tugas['lokasi_nm'] }}</small>
                                        <p class="mb-0 text-muted small">{{ \Illuminate\Support\Str::limit($tugas['deskripsi'], 100) }}</p>
                                    </a>
                                @empty
                                    <p class="text-muted text-center">Tidak ada tugas baru.</p>
                                @endforelse
                            </div>
                        </div>
                        
                        {{-- Tab untuk Tugas Diterima --}}
                        <div class="tab-pane" id="tabs-dikerjakan">
                            <div class="list-group">
                                @forelse($list_tugas_dikerjakan as $tugas)
                                    <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_detail_modal/' . $tugas['penugasan_id']) }}', size: 'modal-lg', title: 'Detail Tugas'})" class="list-group-item list-group-item-action">
                                        <strong class="d-block">{{ $tugas['asset_nm'] }}</strong>
                                        <small class="text-muted">{{ $tugas['lokasi_nm'] }}</small>
                                        <p class="mb-0 text-muted small">{{ \Illuminate\Support\Str::limit($tugas['deskripsi'], 100) }}</p>
                                    </a>
                                @empty
                                    <p class="text-muted text-center">Tidak ada tugas yang sedang dikerjakan.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Tab untuk Tugas Ditolak --}}
                        <div class="tab-pane" id="tabs-ditolak">
                             <div class="list-group">
                                @forelse($list_tugas_ditolak as $tugas)
                                    <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_detail_modal/' . $tugas['penugasan_id']) }}', size: 'modal-lg', title: 'Detail Tugas'})" class="list-group-item list-group-item-action">
                                        <strong class="d-block">{{ $tugas['asset_nm'] }}</strong>
                                        <small class="text-muted">{{ $tugas['lokasi_nm'] }}</small>
                                        <p class="mb-0 text-muted small">Alasan ditolak: {{ $tugas['catatan_penolakan'] ?? 'Tidak ada alasan.' }}</p>
                                    </a>
                                @empty
                                    <p class="text-muted text-center">Tidak ada tugas yang ditolak.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

