
<!-- Start Body Loading -->
<div class="offcanvas offcanvas-bottom loading-box" id="offcanvasBottom" data-bs-backdrop="static" aria-labelledby="offcanvasBottomLabel" style="z-index: 9999999999;">
  <div class="offcanvas-body">
    <div class="text-center mt-2">
      <div class="spinner-border spinner-lg"></div>
      <div class="mt-3" id="loading-text" style="line-height: 1.3!important;">Proses Data...</div>
    </div>
  </div>
</div>
<!-- /End Body Loading -->
<!-- Start Body Modal -->
<?php for ($i = 1; $i <= 20; $i++) : ?>
  <div class="modal modal-blur" id="my-modal-<?= $i ?>" role="dialog">
    <div class="modal-dialog" id="modal-size-<?= $i ?>" role="document">
      <div class="modal-content">
        <div class="modal-header" id="modal-header-<?= $i ?>">
          <h5 class="modal-title" id="modal-title-<?= $i ?>"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modal-body-<?= $i ?>">
        </div>
      </div>
    </div>
  </div>
<?php endfor; ?>
<div class="modal modal-blur" id="ttd-modal" role="dialog">
  <div class="modal-dialog" id="ttd-modal-size" role="document" style="width:600;">
    <div class="modal-content">
      <div class="modal-header" id="ttd-modal-header">
        <h5 class="modal-title" id="ttd-modal-title">Tanda Tangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="ttd-modal-body">
        <canvas id="canvas_0"></canvas>
        <input type="hidden" name="canvas_image_0" id="canvas_image_0" value=""><br>
      </div>
      <div class="modal-footer py-1">
        <div class="col-9 offset-3">
          <div class="float-end">
            <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Batal</button>
            <button type="button" class="btn btn-primary" onclick="canvasSave(0)"><i class="fas fa-save me-2"></i> Simpan</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /End Body Modal -->
<!-- OffCanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasStart">
  <div class="offcanvas-header py-2">
    <h2 class="offcanvas-title" id="offcanvas-start-title"></h2>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body" id="offcanvas-start-body">
  </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd">
  <div class="offcanvas-header py-2">
    <h2 class="offcanvas-title" id="offcanvas-end-title"></h2>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body" id="offcanvas-end-body">
  </div>
</div>
<!-- /End OffCanvas -->
</div>

<script>
  function changeTitle() {
    var title = $(document).prop('title');
    if (typeof $(".page-title").html() === "undefined") {
      setTimeout(function() {
        changeTitle
      }, 1000);
      $(document).prop('title', title);
    } else {
      setTimeout(function() {
        changeTitle
      }, 1000);
      $(document).prop('title', title + ' - ' + $(".page-title").html());
    }
  }

  changeTitle();
</script>

</body>

</html>