<form id="form" action="{{ $form_act }}" method="post" autocomplete="off" enctype="multipart/form-data">
    @csrf
    <div class="card-body">

        {{-- Grup 1: Informasi Utama Lokasi --}}
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Informasi Utama Lokasi</legend>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tipe Lokasi</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" id="tipe_lokasi" name="tipe_lokasi" required>
                        <option value="">- Pilih Tipe -</option>
                        <option value="Gedung" @if(@$main['tipe_lokasi'] == 'Gedung') selected @endif>Gedung</option>
                        <option value="Lantai" @if(@$main['tipe_lokasi'] == 'Lantai') selected @endif>Lantai</option>
                        <option value="Ruangan" @if(@$main['tipe_lokasi'] == 'Ruangan') selected @endif>Ruangan</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3 row" id="div_parent_lokasi"style="display:none;">
              
                <label class="col-lg-3 col-form-label required">Lokasi Induk</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="parent_lokasi_id" id="parent_lokasi_id" required>
                        <option value="">- Pilih Lokasi Induk -</option>
                        @foreach($all_parent_lokasi as $par)
                            <option value="{{ $par['lokasi_id'] }}" @if(@$main['parent_lokasi_id'] == $par['lokasi_id']) selected @endif>
                                {{ $par['lokasi_id'] }} - {{ $par['lokasi_nm'] }} ({{ $par['tipe_lokasi'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">ID Lokasi</label>
                <div class="col-lg-9">
                    {{-- Hapus atribut 'readonly' agar bisa diedit --}}
                    <input type="text" name="lokasi_id" id="lokasi_id" class="form-control" value="{{ @$main['lokasi_id'] }}" required>
                </div>
            </div>
        
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Nama Lokasi</label>
                <div class="col-lg-9">
                    <input type="text" name="lokasi_nm" class="form-control" value="{{ @$main['lokasi_nm'] }}" required>
                </div>
            </div>
        </fieldset>

        {{-- Grup 2: Detail & Visual Lokasi --}}
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail & Visual Lokasi</legend>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Deskripsi</label>
                <div class="col-lg-9">
                    <textarea name="deskripsi" class="form-control" rows="3">{{ @$main['deskripsi'] }}</textarea>
                </div>
            </div>
            <div class="mb-3 row">
                <label for="denah_url" class="col-lg-3 col-form-label">Denah Lokasi</label>
                <div class="col-lg-9">
                    <input type="file" name="denah_url" id="denah_url" class="form-control" accept="image/*">
                    <input type="hidden" name="denah_url_old" value="{{ @$main['denah_url'] }}">
                </div>
            </div>
            <div id="denah-gallery">
                <img id="denah-preview" 
                     src="{{ @$main['denah_url'] ? $main['denah_url'] : asset('assets/no-image.png') }}"
                     alt="Preview Denah"
                     class="img-thumbnail"
                     style="max-width: 200px; max-height: 150px; object-fit: cover; cursor: pointer;">
            </div>
        </fieldset>

        {{-- Grup 3: Status --}}
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Status</legend>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label required">Aktif?</label>
                <div class="col-lg-9 pt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="active_st" value="1" @if(@$main == '' || @$main['active_st'] == 1) checked @endif>
                        <span class="form-check-label">Aktif</span>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="active_st" value="0" @if(@$main != '' && @$main['active_st'] == 0) checked @endif>
                        <span class="form-check-label">Tidak Aktif</span>
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
<script>
    window.allParentLokasi = @json($all_parent_lokasi);
</script>