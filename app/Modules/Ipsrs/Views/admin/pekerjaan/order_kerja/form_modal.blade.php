<form id="form" action="{{ $form_act }}" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Sumber & Penugasan</legend>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Sumber dari Jadwal PM</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="jadwal_pm_id" id="jadwal_pm_id">
                        <option value="">- Pilih Jadwal PM (jika ada) -</option>
                        @if(isset($all_jadwal_pm) && !empty($all_jadwal_pm))
                            @foreach($all_jadwal_pm as $jadwal)
                                <option value="{{ $jadwal['jadwal_pm_id'] }}" @if(@$main['jadwal_pm_id'] == $jadwal['jadwal_pm_id']) selected @endif>
                                    {{ $jadwal['jadwal_pm_id'] }} - {{ $jadwal['asset_nm'] }} ({{ $jadwal['jenis'] }} - {{ $jadwal['frekuensi'] }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="text-center my-2"><strong>ATAU</strong></div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Sumber dari Komplain</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="permintaan_id" id="permintaan_id">
                        <option value="">- Pilih dari Komplain (jika ada) -</option>
                        @foreach($all_komplain as $komplain)
                            <option value="{{ $komplain['permintaan_id'] }}" @if(@$main['permintaan_id'] == $komplain['permintaan_id']) selected @endif>
                                {{ $komplain['permintaan_id'] }} - {{ $komplain['asset_nm'] }} ({{ \Illuminate\Support\Str::limit($komplain['deskripsi'], 40) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tugaskan Teknisi</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="pegawai_ids[]" multiple required>
                        @foreach($all_teknisi as $teknisi)
                            {{-- Logika 'in_array' akan memilih kembali teknisi yang sudah ditugaskan --}}
                            <option value="{{ $teknisi['pegawai_id'] }}" @if(in_array($teknisi['pegawai_id'], $assigned_teknisi)) selected @endif>
                                {{ $teknisi['pegawai_nm'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </fieldset>
        
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Pekerjaan</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tanggal Order</label>
                <div class="col-lg-9">
                    <input type="text" name="tgl_dibuat" class="form-control datepicker-notauto" value="{{ @to_date(@$main['tgl_dibuat'], '-', 'date') ?: date('d-m-Y') }}" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Target Selesai</label>
                <div class="col-lg-9">
                    <input type="text" name="tgl_target_selesai" class="form-control datepicker-notauto" value="{{ @to_date(@$main['tgl_target_selesai'], '-', 'date') }}">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Prioritas</label>
                <div class="col-lg-9">
                   <select class="form-select chosen-select" name="prioritas" required>
                        <option value="normal" @if(@$main['prioritas'] == 'normal') selected @endif>Normal</option>
                        <option value="mendesak" @if(@$main['prioritas'] == 'mendesak') selected @endif>Mendesak</option>
                        <option value="darurat" @if(@$main['prioritas'] == 'darurat') selected @endif>Darurat</option>
                   </select>
                </div>
            </div>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Estimasi Biaya</label>
                <div class="col-lg-9">
                    <input type="number" step="0.01" name="estimasi_biaya" class="form-control" value="{{ @$main['estimasi_biaya'] ?? 0 }}">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Catatan</label>
                <div class="col-lg-9">
                    <textarea name="catatan" class="form-control" rows="3">{{ @$main['catatan'] }}</textarea>
                </div>
            </div>

            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Status</label>
                <div class="col-lg-9">
                   <select class="form-select chosen-select" name="status" required>
                        <option value="baru" @if(@$main == '' || @$main['status'] == 'baru') selected @endif>Baru</option>
                        <option value="diproses" @if(@$main['status'] == 'diproses') selected @endif>Diproses</option>
                        <option value="selesai" @if(@$main['status'] == 'selesai') selected @endif>Selesai</option>
                        <option value="ditolak" @if(@$main['status'] == 'ditolak') selected @endif>Ditolak</option>
                   </select>
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