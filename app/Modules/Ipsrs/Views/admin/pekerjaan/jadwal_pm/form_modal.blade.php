<form id="form" action="{{ $form_act }}" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Jadwal</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Aset</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="asset_id" required>
                        <option value="">- Pilih Aset -</option>
                        @foreach($all_asset as $asset)
                            <option value="{{ $asset['asset_id'] }}" @if(@$main['asset_id'] == $asset['asset_id']) selected @endif>
                                {{ $asset['asset_nm'] }} ({{ $asset['no_seri'] ?? $asset['asset_id'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Frekuensi</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="frekuensi" required>
                        <option value="Harian" @if(@$main['frekuensi'] == 'Harian') selected @endif>Harian</option>
                        <option value="Mingguan" @if(@$main['frekuensi'] == 'Mingguan') selected @endif>Mingguan</option>
                        <option value="Bulanan" @if(@$main['frekuensi'] == 'Bulanan') selected @endif>Bulanan</option>
                        <option value="Kuartalan" @if(@$main['frekuensi'] == 'Kuartalan') selected @endif>Kuartalan (3 Bulan)</option>
                        <option value="Tahunan" @if(@$main['frekuensi'] == 'Tahunan') selected @endif>Tahunan</option>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Jenis Pekerjaan</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="jenis" required>
                        <option value="Pembersihan" @if(@$main['jenis'] == 'Pembersihan') selected @endif>Pembersihan</option>
                        <option value="Inspeksi" @if(@$main['jenis'] == 'Inspeksi') selected @endif>Inspeksi</option>
                        <option value="Kalibrasi" @if(@$main['jenis'] == 'Kalibrasi') selected @endif>Kalibrasi</option>
                        <option value="PenggantianPart" @if(@$main['jenis'] == 'PenggantianPart') selected @endif>Penggantian Part</option>
                        <option value="Penyesuaian" @if(@$main['jenis'] == 'Penyesuaian') selected @endif>Penyesuaian</option>
                    </select>
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Deskripsi</label>
                <div class="col-lg-9">
                    <textarea name="deskripsi" class="form-control" rows="3">{{ @$main['deskripsi'] }}</textarea>
                </div>
            </div>
        </fieldset>
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Penanggalan & Status</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Tgl Terakhir PM</label>
                <div class="col-lg-5">
                    <input type="text" name="tgl_terakhir" class="form-control datepicker-notauto" value="{{ @to_date(@$main['tgl_terakhir'], '-', 'date') }}">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tgl Berikutnya PM</label>
                <div class="col-lg-5">
                    <input type="text" name="tgl_berikutnya" class="form-control datepicker-notauto" value="{{ @to_date(@$main['tgl_berikutnya'], '-', 'date') }}" required>
                </div>
            </div>
             <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Status Jadwal</label>
                <div class="col-lg-9 pt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="aktif" @if(@$main == '' || @$main['status'] == 'aktif') checked @endif>
                        <span class="form-check-label">Aktif</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="ditunda" @if(@$main['status'] == 'ditunda') checked @endif>
                        <span class="form-check-label">Ditunda</span>
                    </div>
                </div>
            </div>
        </fieldset>
        <div class="row mt-3">
            <div class="col-lg-9 offset-lg-3">
                <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>