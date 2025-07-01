<!-- Navbar -->
<div class="sticky-top">
  <header class="navbar navbar-expand-md d-print-none" style="z-index: 800;">
    <div class="container-xl">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <h2 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
        <img src="<?= $identitas['logo'] ?>" alt="<?= $identitas['perusahaan_nm'] ?>" class="navbar-brand-image">
      </h2>
      <div class="navbar-nav flex-row order-md-last">

        <div class="nav-item dropdown">
          <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
            <span class="avatar avatar-sm" style="background-image: url('<?= asset('assets/users/no-photo.png') ?>')"></span>
            <div class="d-none d-xl-block ps-2">
              <div><?= session('pegawai_nm') ?></div>
              <div class="mt-1 small text-muted"><?= session('jabatan_nm') ?></div>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <a href="javascript:void(0)" onclick="_modal(event, {uri: '<?= url('app/profile/form_modal/' . session('pegawai_id')) ?>', size: 'modal-lg', position: 'normal'})" class="dropdown-item"><i class="fas fa-user me-2"></i>Profile</a>
            <div class="dropdown-divider"></div>
            <a href="<?= url('/app/auth/logout_action') ?>" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
          </div>
        </div>
      </div>
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link ps-0" href="<?= url('/app/dashboard?n=' . md5('00')) ?>">
                <span class="nav-link-icon text-dark me-0 d-md-none d-lg-inline-block"><i class="fas fa-tachometer-alt"></i></span>
                <span class="nav-link-title">Dashboard</span>
              </a>
            </li>
            <?php foreach ($nav_list as $n1) : ?>
              <?php if (count($n1['child']) == 0) : ?>
                <li class="nav-item">
                  <a class="nav-link" href="<?= url('/') . '/' . $n1['nav_url'] . '?n=' . md5($n1['nav_id']) ?>">
                    <span class="nav-link-icon text-dark me-0 d-md-none d-lg-inline-block"><i class="<?= $n1['icon'] ?>"></i></span>
                    <span class="nav-link-title"><?= $n1['nav_nm'] ?></span>
                  </a>
                </li>
              <?php else : ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                    <span class="nav-link-icon text-dark me-0 d-md-none d-lg-inline-block"><i class="<?= $n1['icon'] ?>"></i></span>
                    <span class="nav-link-title"><?= $n1['nav_nm'] ?></span>
                  </a>
                  <div class="dropdown-menu">
                    <div class="dropdown-menu-columns">
                      <?php
                      $n1child = array_chunk($n1['child'], 10);
                      ?>
                      <?php foreach ($n1child as $n1childrow) : ?>
                        <div class="dropdown-menu-column">
                          <?php foreach ($n1childrow as $n2) : ?>
                            <?php if (count($n2['child']) == 0) : ?>
                              <a class="dropdown-item" href="<?= url('/') . '/' . $n2['nav_url'] . '?n=' . md5($n2['nav_id']) ?>">
                                - <?= $n2['nav_nm'] ?>
                              </a>
                            <?php else : ?>
                              <div class="dropend">
                                <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                  - <?= $n2['nav_nm'] ?>
                                </a>
                                <div class="dropdown-menu">
                                  <div class="dropdown-menu-columns">
                                    <?php
                                    $n2child = array_chunk($n2['child'], 10);
                                    ?>
                                    <?php foreach ($n2child as $n2childrow) : ?>
                                      <div class="dropdown-menu-column">
                                        <?php foreach ($n2childrow as $n3) : ?>
                                          <?php if (count($n3['child']) == 0) : ?>
                                            <a href="<?= url('/') . '/' . $n3['nav_url'] . '?n=' . md5($n3['nav_id']) ?>" class="dropdown-item">
                                              - <?= $n3['nav_nm'] ?>
                                            </a>
                                          <?php else : ?>
                                            <div class="dropend">
                                              <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                                - <?= $n3['nav_nm'] ?>
                                              </a>
                                              <div class="dropdown-menu">
                                                <?php foreach ($n3['child'] as $n4) : ?>
                                                  <a href="<?= url('/') . '/' . $n4['nav_url'] . '?n=' . md5($n4['nav_id']) ?>" class="dropdown-item">
                                                    <?= $n4['nav_nm'] ?>
                                                  </a>
                                                <?php endforeach; ?>
                                              </div>
                                            </div>
                                          <?php endif; ?>
                                        <?php endforeach; ?>
                                      </div>
                                    <?php endforeach; ?>
                                  </div>
                                </div>
                              </div>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </header>
</div>