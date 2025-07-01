<div class="page-wrapper">
  <div class="page-body mt-2">
    <div class="container-xl">
      <div class="row">
        <div class="col gx-2">
          <div class="card">
            <div class="card-body p-1">
              <div class="row">
                <div class="col-lg-8">
                  <img src="<?= $identitas['photo'] ?>" style="height: 500px; width:100%;object-fit: cover;" class="img-fluid" alt="<?= $identitas['perusahaan_nm'] ?>">
                </div>
                <div class="col-lg-4 p-1">
                  <div class="h3 m-0"><i class="fas fa-building"></i> Profile Perusahaan</div>
                  <div class="border-dotted mb-1"></div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="subheader">Nama Perusahaan</div>
                      <div class="h4 m-0"><?= $identitas['perusahaan_nm'] ?></div>
                    </div>
                    <div class="col-lg-6">
                      <div class="subheader">Direktur Utama</div>
                      <div class="h4 m-0"><?= $identitas['kepala_nm'] ?></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="subheader">Alamat</div>
                      <div class="h4 m-0"><?= $identitas['alamat'] ?></div>
                    </div>
                  </div>
                  <div class="h3 m-0 mt-3"><i class="fas fa-chart-line"></i> Statistik Perusahaan</div>
                  <div class="border-dotted mb-1"></div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="subheader">Jumlah Pegawai</div>

                    </div>
                    <div class="col-lg-6">
                      <div class="subheader">Jumlah Client</div>

                    </div>
                  </div>
                  <div class="border-dotted mt-1 mb-1"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>