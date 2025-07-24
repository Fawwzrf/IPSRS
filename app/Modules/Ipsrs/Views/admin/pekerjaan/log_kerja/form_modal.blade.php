<form id="form-log-kerja" action="{{ $form_act }}" method="post" autocomplete="off" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="action" value="save_log_kerja">
    <input type="hidden" name="n" value="{{ request('n') }}">

    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Pilih Order Kerja & Teknisi</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Order Kerja</label>
                <div class="col-lg-9">
                    <select class="form-select" name="order_kerja_id" required>
                        <option value="">- Pilih Order Kerja -</option>
                        @foreach ($all_order_kerja as $ok)
                            <option value="{{ $ok['order_kerja_id'] }}"
                                @if (@$main['order_kerja_id'] == $ok['order_kerja_id']) selected @endif>
                                {{ $ok['order_kerja_id'] }} - {{ $ok['asset_nm'] ?? '-' }} ({{ $ok['jenis'] ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Teknisi</label>
                <div class="col-lg-9">
                    <select class="form-select" name="teknisi_pegawai_id" required>
                        <option value="">- Pilih Teknisi -</option>
                        @foreach ($all_teknisi as $teknisi)
                            <option value="{{ $teknisi['pegawai_id'] }}"
                                @if (@$main['teknisi_pegawai_id'] == $teknisi['pegawai_id']) selected @endif>
                                {{ $teknisi['pegawai_nm'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Laporan</legend>
            <div class="mb-3">
                <label class="form-label required">Diagnosa Masalah</label>
                <textarea name="diagnosa" class="form-control" rows="3" required>{{ @$main['diagnosa'] }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label required">Tindakan yang Dilakukan</label>
                <textarea name="tindakan" class="form-control" rows="4" required>{{ @$main['tindakan'] }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label required">Hasil Pekerjaan</label>
                <select class="form-select" name="hasil" required>
                    <option value="">- Pilih Hasil -</option>
                    <option value="berhasil">Berhasil</option>
                    <option value="menunggu_sparepart">Menunggu Sparepart</option>
                    <option value="perlu_tindak_lanjut">Perlu Tindak Lanjut</option>
                    <option value="tidak_berhasil">Tidak Berhasil</option>
                </select>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Sparepart yang Digunakan</legend>
            <div id="sparepart-container">
                <div class="row mb-2 sparepart-row">
                    <div class="col-md-6">
                        <select name="sparepart_id[]" class="form-select">
                            <option value="">-- Pilih Sparepart --</option>
                            @foreach ($all_sparepart as $sp)
                                <option value="{{ $sp['sparepart_id'] }}">{{ $sp['sparepart_nm'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="jumlah[]" class="form-control" min="1" value="1"
                            required placeholder="Jumlah">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-sparepart">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" id="btn-add-sparepart" class="btn btn-success btn-sm mt-2">
                <i class="fas fa-plus"></i> Tambah Sparepart
            </button>
            <div class="form-text text-muted">Tambah sparepart yang digunakan dalam pekerjaan ini.</div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Biaya, Durasi & Bukti</legend>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Durasi (Menit)</label>
                    <input type="number" name="durasi_menit" class="form-control"
                        value="{{ @$main['durasi_menit'] ?? 0 }}">
                </div>
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Biaya Lain-lain (Rp)</label>
                    <input type="text" name="total_biaya" class="form-control autonumeric"
                        value="{{ @$main['total_biaya'] ?? 0 }}">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Unggah Foto Bukti</label>
                <input class="form-control" type="file" name="fotos[]" id="foto-input" multiple>
                <div id="foto-preview" class="d-flex flex-wrap mt-2"></div>
            </div>
            {{-- Tampilkan foto-foto yang sudah ada --}}
            @if (!empty($log_fotos))
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Foto Bukti yang Sudah Diunggah:</strong></p>
                        <div class="d-flex flex-wrap">
                            @foreach ($log_fotos as $foto)
                                <div class="me-2 mb-2">
                                    <img src="{{ $foto['foto_url'] }}" alt="Foto Bukti" class="img-thumbnail"
                                        style="height: 100px;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </fieldset>

        <div class="row mt-3">
            <div class="col-lg-12">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" onclick="_save(event)">Simpan</button>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('#btn-add-sparepart').on('click', function() {
            var row = $('#sparepart-container .sparepart-row:first').clone();
            row.find('select').val('');
            row.find('input').val(1);
            $('#sparepart-container').append(row);
        });

        // Hapus baris sparepart
        $('#sparepart-container').on('click', '.btn-remove-sparepart', function() {
            if ($('#sparepart-container .sparepart-row').length > 1) {
                $(this).closest('.sparepart-row').remove();
            }
        });

        // Preview foto sebelum upload
        $('#foto-input').on('change', function() {
            $('#foto-preview').html('');
            if (this.files) {
                $.each(this.files, function(i, file) {
                    if (/^image\//.test(file.type)) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            $('#foto-preview').append(
                                '<img src="' + e.target.result + '" class="img-thumbnail me-2 mb-2" style="height:100px;">'
                            );
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    });
</script>
