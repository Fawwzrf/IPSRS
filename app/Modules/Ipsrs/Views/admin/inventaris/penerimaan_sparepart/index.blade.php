@include('ipsrs::admin.inventaris.penerimaan_sparepart._js')
<div class="page-wrapper">
    <div class="page-header d-print-none mt-2">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">Inventaris</div>
                    <h2 class="page-title">Penerimaan Sparepart</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="javascript:void(0)" onclick="_modal(event, {uri: '{{ url($uri . '/form_modal') }}', size: 'modal-lg'})" class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-plus"></i> Catat Penerimaan Baru
                        </a>
                    </div>
                </div>
            </div>
            {{-- Di sini bisa ditambahkan bagian filter jika diperlukan nanti --}}
        </div>
    </div>
    <div class="page-body mt-1">
        <div class="container-xl">
            <div class="card p-2">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped table-sm" id="datatable-main">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="7%">Aksi</th>
                                <th>ID Penerimaan</th>
                                <th>Tanggal</th>
                                <th>Nama Sparepart</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Vendor</th>
                                <th>No. Faktur</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>