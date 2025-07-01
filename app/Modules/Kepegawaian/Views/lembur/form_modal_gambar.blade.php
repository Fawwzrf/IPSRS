<div class="card-body">
  <div class="row mt-2">
    <div class="col-12">
      <?= $main['uraian'] ?>
    </div>
    <div class="border-dotted my-2"></div>
    <div class="col-12">
      <img src="<?= site_url('app/get_file/lembur/' . @$main[$file_index]) ?>" alt="<?= $file_index ?>" class="img-thumbnail" style="width:100% !important">
    </div>
  </div>
</div>