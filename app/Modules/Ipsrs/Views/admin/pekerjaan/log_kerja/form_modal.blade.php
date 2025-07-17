@include ('ipsrs::admin.pekerjaan.log_kerja._js')

<form id="form-log-kerja" action="{{ $form_act }}" method="post" autocomplete="off" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="action" value="save_log_kerja">
    <input type="hidden" name="asset_id" value="{{ $asset_id ?? '' }}">
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
            
            {{-- Tampilkan foto-foto yang sudah ada --}}
            @if(!empty($log_fotos))
            <div class="row mt-3">
                <div class="col-12">
                    <p><strong>Foto Bukti yang Sudah Diunggah:</strong></p>
                    <div class="d-flex flex-wrap">
                        @foreach($log_fotos as $foto)
                        <div class="me-2 mb-2">
                            <img src="{{ $foto['foto_url'] }}" alt="Foto Bukti" class="img-thumbnail" style="height: 100px;">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </fieldset>

        <div class="row mt-3">
            <div class="col-lg-12">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Simpan Laporan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>
