@include('master::pendidikan._js')
<div class="page-wrapper">
  <div class="page-header d-print-none mt-2">
    <div class="container-xl">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            <?= $nav['nav_nm'] ?>
          </div>
          <h2 class="page-title">
            <?= $title ?>
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= $uri . '/form_modal' ?>', size: 'modal-md', position: 'normal'})" class="btn btn-primary d-sm-inline-block"><i class="fas fa-plus"></i> Tambah Baru</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="page-wrapper">
    <div class="page-body mt-2">
      <div class="container-xl">
        <div class="row">
          <div class="col">
            <div class="card p-2">
              <div class="w-100">
                <div class="table-responsive">
                  <table class="table table-vcenter card-table table-striped table-sm display nowrap" id="datatable-main">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th width="7%">Aksi</th>
                        <th width="7%">Id</th>
                        <th>Pendidikan</th>
                        <th width="5%">Aktif?</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>