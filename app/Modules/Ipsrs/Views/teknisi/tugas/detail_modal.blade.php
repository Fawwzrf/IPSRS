<div class="modal-body">
    <div class="row">
        <div class="col-md-7">
            <h5 class="mb-3">Detail Pekerjaan</h5>
            @if ($tugas['jenis'] == 'perbaikan')
                {{-- Tampilan untuk jenis komplain/perbaikan --}}
                <dl class="row">
                    <dt class="col-4">Aset</dt>
                    <dd class="col-8">{{ $tugas['asset_nm'] ?? '-' }}</dd>
                    <dt class="col-4">Lokasi</dt>
                    <dd class="col-8">{{ $tugas['lokasi_nm'] ?? '-' }}</dd>
                    <dt class="col-4">Pelapor</dt>
                    <dd class="col-8">{{ $tugas['nama_pelapor'] ?? 'N/A' }}</dd>
                    <dt class="col-4">Deskripsi</dt>
                    <dd class="col-8">{{ $tugas['deskripsi'] ?? '-' }}</dd>
                </dl>
            @else
                {{-- Tampilan untuk jenis Jadwal PM/pemeliharaan --}}
                <dl class="row">
                    <dt class="col-4">Aset</dt>
                    <dd class="col-8">{{ $tugas['asset_nm'] ?? '-' }}</dd>
                    <dt class="col-4">Lokasi</dt>
                    <dd class="col-8">{{ $tugas['lokasi_nm'] ?? '-' }}</dd>
                    <dt class="col-4">Frekuensi</dt>
                    <dd class="col-8">{{ $tugas['frekuensi'] ?? '-' }}</dd>
                    <dt class="col-4">Jenis</dt>
                    <dd class="col-8">{{ $tugas['jenis_pemeliharaan'] ?? '-' }}</dd>
                    <dt class="col-4">Tgl. Pemeliharaan</dt>
                    <dd class="col-8">{{ $tugas['tgl_pemeliharaan'] ? to_date($tugas['tgl_pemeliharaan']) : '-' }}</dd>
                    <dt class="col-4">Deskripsi</dt>
                    <dd class="col-8">{{ $tugas['deskripsi_pemeliharaan'] ?? '-' }}</dd>
                </dl>
            @endif
        </div>
        <div class="col-md-5">
            <h5 class="mb-3">Denah Lokasi</h5>
            @if (!empty($tugas['anotasi_url']))
                <img src="{{ $tugas['anotasi_url'] }}" class="img-fluid rounded border" alt="Denah Lokasi">
            @else
                <div class="text-muted text-center p-4 border rounded">Denah tidak tersedia.</div>
            @endif
        </div>
    </div>
    @if ($tugas['status'] == 'dibatalkan' && !empty($tugas['catatan_penolakan']))
        <div class="alert alert-danger mt-3">
            <strong>Alasan Penolakan:</strong><br>
            {{ $tugas['catatan_penolakan'] }}
        </div>
    @endif
</div>
<div class="modal-footer">
    @if ($tugas['status'] == 'ditugaskan')
        {{-- Tombol Terima Tugas (diubah menjadi button) --}}
        <button class="btn btn-success btn-task-action" data-url="{{ url('ipsrs/teknisitugas/terima') }}"
            data-id="{{ $tugas['penugasan_id'] }}">
            <i class="fas fa-check me-2"></i> Terima Tugas
        </button>
        <button class="btn btn-danger"
            onclick="_modal(event, {uri: '{{ url('ipsrs/teknisitugas/form_tolak_modal/' . $tugas['penugasan_id']) }}', size: 'modal-md', title: 'Tolak Tugas'})">
            <i class="fas fa-times me-2"></i> Tolak Tugas
        </button>
    @elseif($tugas['status'] == 'sedang_dikerjakan')
        {{-- Tombol Batalkan Penerimaan (diubah menjadi button) --}}
        <button class="btn btn-outline-warning btn-task-action" data-url="{{ url('ipsrs/teknisitugas/batal_terima') }}"
            data-id="{{ $tugas['penugasan_id'] }}">
            Batalkan Penerimaan
        </button>
        <a href="{{ url('master/asset/detail/' . $tugas['asset_id']) }}" class="btn btn-primary">
            <i class="fas fa-arrow-right me-2"></i> Lanjutkan & Buat Laporan
        </a>
    @elseif($tugas['status'] == 'dibatalkan')
        {{-- Tombol Terima Kembali (diubah menjadi button) --}}
        <button class="btn btn-success btn-task-action" data-url="{{ url('ipsrs/teknisitugas/terima') }}"
            data-id="{{ $tugas['penugasan_id'] }}">
            <i class="fas fa-check me-2"></i> Terima Kembali Tugas Ini
        </button>
    @endif
    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Tutup</button>
</div>