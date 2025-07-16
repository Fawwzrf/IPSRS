@include ('ipsrs::admin.pekerjaan.log_kerja._js')

{{-- PERBAIKAN: Menggunakan form_act untuk action --}}
<form id="form-log-kerja" action="{{ $form_act }}" method="post" autocomplete="off" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="action" value="save_log_kerja">
    <!-- Tambahkan di form_modal.blade.php -->
    <input type="hidden" name="asset_id" value="{{ $order_kerja['asset_id'] ?? (request('asset_id') ?? '') }}">    <script>
        // ...kode inisialisasi yang sudah ada...
    
        $(document).off('submit', '#form-log-kerja').on('submit', '#form-log-kerja', function(e) {
            e.preventDefault();
    
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            let url = form.attr('action');
            
            // Mengambil parameter 'n' dari hidden field di form
            const n_param = form.find('input[name="n"]').val();
            
            // Ambil ID aset dari URL atau hidden field jika tersedia
            const assetId = form.find('input[name="asset_id"]').val() || 
                            new URLSearchParams(window.location.search).get('asset_id');
                            
            console.log("Mengirim form dengan parameter 'n':", n_param, "dan asset ID:", assetId);
            
            btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Menyimpan...');
            
            // Buat FormData dari form
            const formData = new FormData(this);
            
            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res) {
                    const responseData = res.data || res;
                    
                    if (responseData.code == '01' || responseData.code == '02' || res.status === true) {
                        _toast('success', responseData.message || res.message || 'Data berhasil disimpan.');
                        _modalHide(1);
                        
                        // PERBAIKAN: Redirect ke halaman detail aset dengan parameter n
                        if (assetId && n_param) {
                            // Buat URL detail aset dengan parameter n
                            const detailUrl = new URL(`${window.location.protocol}//${window.location.host}/master/asset/detail/${assetId}`);
                            detailUrl.searchParams.set('n', n_param);
                            
                            console.log("Redirect ke halaman detail aset:", detailUrl.toString());
                            window.location.href = detailUrl.toString();
                        } else if (res.uri) {
                            // Fallback: Gunakan URI dari server tapi tambahkan parameter n
                            if (n_param) {
                                try {
                                    const redirectUrl = new URL(res.uri);
                                    redirectUrl.searchParams.set('n', n_param);
                                    window.location.href = redirectUrl.toString();
                                } catch (e) {
                                    console.error("Error saat mengolah URI redirect:", e);
                                    window.location.href = res.uri + (res.uri.includes('?') ? '&' : '?') + 'n=' + n_param;
                                }
                            } else {
                                window.location.href = res.uri;
                            }
                        } else {
                            // Jika tidak ada opsi lain, reload halaman dengan mempertahankan parameter 'n'
                            if (n_param) {
                                const currentUrl = new URL(window.location.href);
                                currentUrl.searchParams.set('n', n_param);
                                window.location.href = currentUrl.toString();
                            } else {
                                window.location.reload();
                            }
                        }
                    } else {
                        _toast('error', responseData.message || 'Gagal memproses data.');
                        btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Laporan');
                    }
                },
                error: function(xhr) {
                    const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
                    _toast('error', errorMsg);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Laporan');
                }
            });
        });
    </script>
    <input type="hidden" name="n" value="{{ request('n') }}">

    <div class="card-body">
        <div class="alert alert-info">
            Melaporkan untuk Order Kerja: <strong>{{ $order_kerja['order_kerja_id'] }}</strong>
        </div>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Laporan</legend>
            <div class="mb-3">
                <label class="form-label required">Diagnosa Masalah</label>
                <textarea name="diagnosa" class="form-control" rows="3" required>{{ @$log_kerja['diagnosa'] }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label required">Tindakan yang Dilakukan</label>
                <textarea name="tindakan" class="form-control" rows="4" required>{{ @$log_kerja['tindakan'] }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label required">Hasil Pekerjaan</label>
                <select class="form-select" name="hasil" required>
                    <option value="">- Pilih Hasil -</option>
                    <option value="berhasil" @if (@$log_kerja['hasil'] == 'berhasil') selected @endif>Berhasil</option>
                    <option value="perlu_tindak_lanjut" @if (@$log_kerja['hasil'] == 'perlu_tindak_lanjut') selected @endif>Perlu Tindak
                        Lanjut</option>
                    <option value="tidak_berhasil" @if (@$log_kerja['hasil'] == 'tidak_berhasil') selected @endif>Tidak Berhasil
                    </option>
                </select>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Penggunaan Sparepart</legend>
            <div id="sparepart-repeater">
                <div data-repeater-list="sparepart">
                    <div data-repeater-item class="row mb-2 align-items-center">
                        <div class="col-lg-7">
                            <select name="id" class="form-select repeater-select">
                                <option value="">- Pilih Sparepart -</option>
                                @foreach ($all_sparepart as $sp)
                                    <option value="{{ $sp['sparepart_id'] }}">{{ $sp['sparepart_nm'] }} (Stok:
                                        {{ $sp['stok'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <input type="number" name="jumlah" class="form-control" placeholder="Jumlah"
                                min="1" value="1">
                        </div>
                        <div class="col-lg-2">
                            <button data-repeater-delete type="button" class="btn btn-danger btn-sm">Hapus</button>
                        </div>
                    </div>
                </div>
                <button data-repeater-create type="button" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="fas fa-plus"></i> Tambah Sparepart
                </button>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Biaya, Durasi & Bukti</legend>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Durasi (Menit)</label>
                    <input type="number" name="durasi_menit" class="form-control"
                        value="{{ @$log_kerja['durasi_menit'] ?? 0 }}">
                </div>
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Biaya Lain-lain (Rp)</label>
                    <input type="text" name="total_biaya" class="form-control autonumeric"
                        value="{{ @$log_kerja['total_biaya'] ?? 0 }}">
                </div>
            </div>
            <div class="mb-3">
                <label for="formFileMultiple" class="form-label">Unggah Foto Bukti</label>
                <input class="form-control" type="file" name="fotos[]" multiple>
            </div>
        </fieldset>

        <div class="row mt-3">
            <div class="col-lg-12">
                {{-- PERBAIKAN: Tombol kembali ke pola standar --}}
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Simpan Laporan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>
