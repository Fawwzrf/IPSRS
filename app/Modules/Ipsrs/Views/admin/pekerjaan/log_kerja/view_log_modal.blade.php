<div class="container-xl">
    <div class="row g-3">
        {{-- Detail Laporan --}}
        <div class="col-lg-7">
            <fieldset class="border p-2 rounded h-100">
                <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Laporan Kerja</legend>
                <dl class="row">
                    <dt class="col-4">ID Log Kerja:</dt>
                    <dd class="col-8">{{ $log['log_kerja_id'] }}</dd>
                    <dt class="col-4">Teknisi:</dt>
                    <dd class="col-8">{{ $log['teknisi_nm'] }}</dd>
                    <dt class="col-4">Waktu Mulai:</dt>
                    <dd class="col-8">{{ @to_date($log['tgl_mulai'], '-', 'datetime') }}</dd>
                    <dt class="col-4">Waktu Selesai:</dt>
                    <dd class="col-8">{{ @to_date($log['tgl_selesai'], '-', 'datetime') }}</dd>
                    <dt class="col-4">Durasi:</dt>
                    <dd class="col-8">{{ $log['durasi_menit'] }} Menit</dd>
                    <dt class="col-4">Hasil:</dt>
                    <dd class="col-8"><span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $log['hasil'])) }}</span></dd>
                    <hr class="my-2">
                    <dt class="col-12">Diagnosa:</dt>
                    <dd class="col-12"><p>{{ $log['diagnosa'] }}</p></dd>
                    <dt class="col-12">Tindakan yang Dilakukan:</dt>
                    <dd class="col-12"><p>{{ $log['tindakan'] }}</p></dd>
                </dl>
            </fieldset>
        </div>

        {{-- Galeri Foto Bukti --}}
        <div class="col-lg-5">
            <fieldset class="border p-2 rounded h-100">
                <legend class="float-none w-auto px-2 fs-6 fw-bold">Bukti Foto</legend>
                <div class="row g-2">
                    @forelse($photos as $photo)
                        <div class="col-6">
                            <a href="{{ $photo['foto_url'] }}" data-fslightbox="gallery-log">
                                <img src="{{ $photo['foto_url'] }}" class="img-fluid rounded" alt="Bukti Foto">
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted">
                            Tidak ada foto bukti yang diunggah.
                        </div>
                    @endforelse
                </div>
            </fieldset>
        </div>
    </div>
</div>

{{-- Inisialisasi Lightbox untuk galeri foto --}}
<script>
    // Pastikan library FsLightbox sudah dimuat di layout utama Anda
    if (typeof refreshFsLightbox === 'function') {
        refreshFsLightbox();
    }
</script>