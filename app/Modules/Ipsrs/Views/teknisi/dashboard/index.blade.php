<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <h2 class="page-title">Dashboard Teknisi</h2>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            @if ($tugas_baru_count > 0)
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-title">Anda memiliki {{ $tugas_baru_count }} tugas baru!</h4>
                    <div class="text-muted">Segera periksa daftar tugas Anda untuk menerima atau menolak pekerjaan.</div>
                    <div class="mt-3">
                        <a href="{{ url('ipsrs/teknisitugas') }}?n={{ request('n') }}" class="btn btn-primary">Lihat
                            Daftar Tugas</a>
                    </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tugas yang Sedang Dikerjakan</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($tugas_aktif_list as $tugas)
                        <a href="javascript:void(0)"
                            onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/detail/' . $tugas['penugasan_id']) }}?n={{ $n }}', size: 'modal-lg', title: 'Detail Tugas'})"
                            class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ $tugas['asset_nm'] }}</h5>
                                <small>{{ to_date($tugas['tgl_dibuat']) }}</small>
                            </div>
                            <p class="mb-1">{{ $tugas['deskripsi'] }}</p>
                            <small class="text-muted">{{ $tugas['lokasi_nm'] }}</small>
                        </a>
                    @empty
                        <div class="list-group-item">
                            <p class="text-muted text-center mb-0">Tidak ada pekerjaan yang sedang aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
