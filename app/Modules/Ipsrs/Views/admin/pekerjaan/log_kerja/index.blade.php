@include('ipsrs::admin.pekerjaan.log_kerja._js')

<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Log Kerja Teknisi
                    </div>
                    <h2 class="page-title">
                        Log Kerja Teknisi (Admin)
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="javascript:void(0)"
                            onclick="_modal(event, {uri: '{{ url($uri . '/form_modal') }}', size: 'modal-lg', position: 'normal'})"
                            class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-plus"></i> Tambah Log Kerja
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <div class="card mb-1">
                        <div class="accordion" id="accordion-logkerja">
                            <div class="accordion-item-disabled">
                                <div id="filter" class="accordion-collapse collapse show" data-bs-parent="#accordion-logkerja">
                                    <div class="accordion-body bg-white p-2">
                                        <form class="mb-0" id="search" action="<?= $search_act ?>" method="post" autocomplete="off" onsubmit="_search(event)">
                                            @csrf
                                            <input type="hidden" name="search_act" value="save">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <label class="form-label">Filter Teknisi</label>
                                                    <select class="form-select chosen-select" id="teknisi_id" name="teknisi_id">
                                                        <option value="">-- Semua Teknisi --</option>
                                                        @foreach($all_teknisi as $teknisi)
                                                            <option value="{{ $teknisi['pegawai_id'] }}" @if(@$nav_sess['search']['data']['teknisi_id'] == $teknisi['pegawai_id']) selected @endif>
                                                                {{ $teknisi['pegawai_nm'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Filter Hasil</label>
                                                    <select class="form-select chosen-select" id="hasil" name="hasil">
                                                        <option value="">-- Semua Hasil --</option>
                                                        <option value="berhasil" @if(@$nav_sess['search']['data']['hasil'] == 'berhasil') selected @endif>Berhasil</option>
                                                        <option value="menunggu_sparepart" @if(@$nav_sess['search']['data']['hasil'] == 'menunggu_sparepart') selected @endif>Menunggu Sparepart</option>
                                                        <option value="perlu_tindak_lanjut" @if(@$nav_sess['search']['data']['hasil'] == 'perlu_tindak_lanjut') selected @endif>Perlu Tindak Lanjut</option>
                                                        <option value="tidak_berhasil" @if(@$nav_sess['search']['data']['hasil'] == 'tidak_berhasil') selected @endif>Tidak Berhasil</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label class="form-label">Pencarian</label>
                                                    <input class="form-control" type="text" id="term" name="term" value="<?= @$nav_sess['search']['data']['term'] ?>">
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group mt-4">
                                                        <button class="btn" type="submit" onclick="_search(event)"><i class="fas fa-search"></i>&nbsp;&nbsp;Cari</button>
                                                        <button class="btn" type="button" onclick="_searchReset()"><i class="fas fa-times"></i>&nbsp;Reset</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="row">
                <div class="col">
                    <div class="card p-2">
                        <div class="table-responsive">
                            <table id="datatable-main" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Order Kerja ID</th>
                                        <th>Teknisi</th>
                                        <th>Tgl Selesai</th>
                                        <th>Hasil</th>
                                        <th>Foto Bukti</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Data akan di-load oleh DataTables AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>