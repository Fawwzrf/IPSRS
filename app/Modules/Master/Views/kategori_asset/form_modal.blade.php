<form id="form" action="{{ $form_act }}" method="post" autocomplete="off">
    @csrf
    <div class="card-body">

        {{-- Grup Informasi Kategori --}}
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Informasi Kategori</legend>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">ID Kategori</label>
                <div class="col-lg-9">
                    <input type="text" name="kategori_asset_id" id="kategori_asset_id" class="form-control" value="{{ @$main['kategori_asset_id'] }}" @if(@$main) readonly @endif required>
                    @if(!@$main)
                        <small class="form-hint">Contoh: KAT001, KAT002</small>
                    @endif
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Nama Kategori</label>
                <div class="col-lg-9">
                    <input type="text" name="kategori_asset_nm" id="kategori_asset_nm" class="form-control" value="{{ @$main['kategori_asset_nm'] }}" required>
                </div>
            </div>

            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Deskripsi</label>
                <div class="col-lg-9">
                    <textarea name="deskripsi" class="form-control" rows="3">{{ @$main['deskripsi'] }}</textarea>
                </div>
            </div>
        </fieldset>
        
        {{-- Grup Status --}}
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

        {{-- Tombol Aksi --}}
        <div class="row mt-3">
            <div class="col-lg-9 offset-lg-3">
                <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>