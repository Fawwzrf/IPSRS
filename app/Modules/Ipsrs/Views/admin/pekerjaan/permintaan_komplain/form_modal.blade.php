<form id="form" action="{{ $form_act }}" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Permintaan Komplain</legend>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tanggal</label>
                <div class="col-lg-9">
                    <input type="text" name="tgl" class="form-control datepicker-notauto" value="{{ @to_date(@$main['tgl'], '-', 'date') ?: date('d-m-Y') }}" required>
                </div>
            </div>

            {{-- LANGKAH 1: PILIH LOKASI --}}
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Lokasi Komplain</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="lokasi_id" id="lokasi-select" required>
                        <option value="">- Pilih Lokasi Terlebih Dahulu -</option>
                        @php
                            // Blok ini untuk menentukan lokasi yang terpilih saat mode edit
                            $selected_lokasi_id = '';
                            if(@$main['asset_id']) {
                                // Ambil data asset untuk mendapatkan lokasi_id-nya
                                $asset = \App\Modules\App\Models\DbModel::getData('asset', ['asset_id' => $main['asset_id']]);
                                $selected_lokasi_id = @$asset['lokasi_id'];
                            }
                        @endphp
                        @foreach($all_lokasi as $lokasi)
                            {{-- Simpan URL denah di data-attribute untuk diakses oleh JavaScript --}}
                            <option value="{{ $lokasi['lokasi_id'] }}" data-denah-url="{{ $lokasi['denah_url'] ?? '' }}" @if($selected_lokasi_id == $lokasi['lokasi_id']) selected @endif>
                                {{ $lokasi['lokasi_nm'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- LANGKAH 2: PILIH ASET (Kontennya akan diisi oleh JavaScript) --}}
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Aset yang Dikomplain</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="asset_id" id="asset-select" required disabled>
                        <option value="">- Pilih Lokasi Terlebih Dahulu -</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Dibuat oleh</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="pegawai_id" required>
                        <option value="">- Pilih Pegawai -</option>
                        @foreach($all_pegawai as $pegawai)
                            <option value="{{ $pegawai['pegawai_id'] }}" @if(@$main['pegawai_id'] == $pegawai['pegawai_id']) selected @endif>
                                {{ $pegawai['pegawai_nm'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Deskripsi Komplain</label>
                <div class="col-lg-9">
                    <textarea name="deskripsi" class="form-control" rows="4" required>{{ @$main['deskripsi'] }}</textarea>
                </div>
            </div>

            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Status</label>
                <div class="col-lg-9">
                   <select class="form-select chosen-select" name="status">
                        <option value="baru" @if(@$main == '' || @$main['status'] == 'baru') selected @endif>Baru</option>
                        <option value="diproses" @if(@$main['status'] == 'diproses') selected @endif>Diproses</option>
                        <option value="selesai" @if(@$main['status'] == 'selesai') selected @endif>Selesai</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Denah Lokasi (Otomatis)</legend>
            <div class="mb-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-hapus-anotasi" style="display: none;">
                    <i class="fas fa-times me-2"></i> Hapus Tanda
                </button>
                <small class="form-hint ms-2">Klik pada denah untuk memberi tanda lokasi komplain.</small>
            </div>
            <div class="d-flex justify-content-center align-items-center bg-light" style="min-height: 450px; border: 1px dashed #ccc;">
                <canvas id="denah-canvas" style="display: none; max-width: 100%;"></canvas>
                <span id="canvas-placeholder">Pilih lokasi untuk menampilkan denah</span>
            </div>
            <input type="hidden" name="anotasi_url" id="anotasi_url" value="{{ @$main['anotasi_url'] }}">
        </fieldset>
        
        <div class="row mt-3">
            <div class="col-lg-9 offset-lg-3">
                <button type="submit" class="btn btn-primary" onclick="window.drawCanvasForSave(); _save(event);"><i class="fas fa-save me-2"></i> Simpan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>

{{-- Simpan semua data aset ke dalam variabel JavaScript global untuk diakses oleh _js.blade.php --}}
<script>
    window.allAssets = @json($all_asset);
    window.selectedAsset = '{{ @$main['asset_id'] }}'; 
</script>