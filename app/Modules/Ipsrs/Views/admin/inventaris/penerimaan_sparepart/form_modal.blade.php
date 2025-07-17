<form id="form-penerimaan-sparepart" action="{{ $form_act }}" method="post" autocomplete="off">
    @csrf
    <div class="card-body">
        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Detail Penerimaan</legend>
            
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Sparepart</label>
                <div class="col-lg-9">
                    <select class="form-select chosen-select" name="sparepart_id" id="sparepart_id" required>
                        <option value="">- Pilih Sparepart yang Diterima -</option>
                        @foreach($all_sparepart as $sp)
                            <option value="{{ $sp['sparepart_id'] }}" @if(@$main['sparepart_id'] == $sp['sparepart_id']) selected @endif>
                                {{ $sp['sparepart_nm'] }} (ID: {{ $sp['sparepart_id'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Tanggal Penerimaan</label>
                <div class="col-lg-5">
                    <input type="text" name="tgl" id="tgl" class="form-control datepicker-notauto" value="{{ @$main ? to_date(@$main['tgl'], '-', 'date') : date('d-m-Y') }}" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label required">Jumlah</label>
                <div class="col-lg-4">
                    <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" value="{{ @$main['jumlah'] ?? 1 }}" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Harga Satuan (Rp)</label>
                <div class="col-lg-5">
                    <input type="text" name="harga_satuan" id="harga_satuan" class="form-control" value="{{ @$main['harga_satuan'] ?? 0 }}">
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-2 rounded mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-bold">Informasi Tambahan</legend>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">Vendor/Pemasok</label>
                <div class="col-lg-9">
                    <input type="text" name="vendor" id="vendor" class="form-control" value="{{ @$main['vendor'] }}">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-lg-3 col-form-label">No. Faktur/PO</label>
                <div class="col-lg-9">
                    <input type="text" name="no_faktur" id="no_faktur" class="form-control" value="{{ @$main['no_faktur'] }}">
                </div>
            </div>
            <div class="mb-1 row">
                <label class="col-lg-3 col-form-label">Catatan</label>
                <div class="col-lg-9">
                    <textarea name="catatan" id="catatan" class="form-control" rows="3">{{ @$main['catatan'] }}</textarea>
                </div>
            </div>
        </fieldset>

        <div class="row mt-3">
            <div class="col-lg-9 offset-lg-3">
                <button type="button" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan Transaksi</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>