<form id="form" action="<?= $form_act ?>" method="post" autocomplete="off" enctype="multipart/form-data">
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        @include('kepegawaian::lembur.informasi')
      </div>
    </div>
    <div class="row">
      <div class="col-lg-6 col-md-12">
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Lembur Id</label>
          <div class="col-lg-4">
            <input type="text" name="lembur_id" class="form-control" value="<?= @$main['lembur_id'] ?>" readonly>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Pegawai</label>
          <div class="col-lg-9 col-md-6">
            <input type="text" class="form-control" value="<?= (@$main) ? @$pegawai['pegawai_nm'] : session('pegawai_nm') ?>" readonly>
            <input type="hidden" name="pegawai_id" value="<?= (@$main) ? @$pegawai['pegawai_id'] : session('pegawai_id') ?>">
          </div>
        </div>
        <?php if ($pegawai['spkl_st'] == 1) : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label required">SPKL</label>
            <div class="col-lg-9 col-md-6">
              <select class="form-select chosen-select" name="spkl_id" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($all_spkl as $r) : ?>
                  <option value="<?= $r['spkl_id'] ?>" <?= (@$main['spkl_id'] == $r['spkl_id']) ? 'selected' : '' ?>><?= $r['spkl_id'] ?> - <?= $r['pekerjaan'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        <?php endif; ?>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Mulai</label>
          <div class="col-lg-4">
            <input type="text" name="mulai_tgl" id="mulai_tgl" class="form-control waktu datetimepicker-lembur" value="<?= @to_date($main['mulai_tgl'], '-', 'full_date') ?>" required>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Selesai</label>
          <div class="col-lg-4">
            <input type="text" name="selesai_tgl" id="selesai_tgl" class="form-control waktu datetimepicker-lembur" value="<?= @to_date($main['selesai_tgl'], '-', 'full_date') ?>" required>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Durasi</label>
          <div class="col-lg-4">
            <div class="input-group">
              <input type="text" name="durasi" id="durasi" class="form-control text-center" value="<?= @$main['durasi'] ?>" required readonly>
              <span class="input-group-text">Jam</span>
            </div>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Uraian</label>
          <div class="col-lg-9 col-md-6">
            <textarea class="form-control" name="uraian" id="uraian" rows="5" required><?= @$main['uraian'] ?></textarea>
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-12">
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label required">Link Commit Github</label>
          <div class="col-lg-9 col-md-6">
            <textarea class="form-control" name="link" id="link" rows="5" required><?= @$main['link'] ?></textarea>
          </div>
        </div>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label <?= (@$main['gambar_1'] == "") ? 'required' : '' ?>">Gambar 1</label>
          <div class="col-lg-8 col-md-6">
            <input type="file" name="gambar_1" class="form-control" value="" id="gambar_1" <?= (@$main['gambar_1'] == "") ? 'required' : '' ?>>
          </div>
        </div>
        <?php if (@$main['gambar_1'] != "") : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label"></label>
            <div class="col-lg-6 col-md-6">
              <a href="<?= $uri . 'app/get_file/lembur/' . @$main['gambar_1'] ?>" target="_blank">
                <img src="<?= $uri . 'app/get_file/lembur/' . @$main['gambar_1'] ?>" alt="Gambar 1" class="img-thumbnail" style="height:150px !important">
              </a>
            </div>
          </div>
        <?php endif; ?>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Gambar 2</label>
          <div class="col-lg-8 col-md-6">
            <input type="file" name="gambar_2" class="form-control" value="" id="gambar_2">
          </div>
        </div>
        <?php if (@$main['gambar_2'] != "") : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label"></label>
            <div class="col-lg-6 col-md-6">
              <a href="<?= $uri . 'app/get_file/lembur/' . @$main['gambar_2'] ?>" target="_blank">
                <img src="<?= $uri . 'app/get_file/lembur/' . @$main['gambar_2'] ?>" alt="Gambar 2" class="img-thumbnail" style="height:150px !important">
              </a>
            </div>
          </div>
        <?php endif; ?>
        <div class="mb-1 row">
          <label class="col-lg-3 col-md-6 col-form-label">Gambar 3</label>
          <div class="col-lg-8 col-md-6">
            <input type="file" name="gambar_3" class="form-control" value="">
          </div>
        </div>
        <?php if (@$main['gambar_3'] != "") : ?>
          <div class="mb-1 row">
            <label class="col-lg-3 col-md-6 col-form-label"></label>
            <div class="col-lg-6 col-md-6">
              <a href="<?= $uri . 'app/get_file/lembur/' . @$main['gambar_3'] ?>" target="_blank">
                <img src="<?= $uri . 'app/get_file/lembur/' . @$main['gambar_3'] ?>" alt="Gambar 3" class="img-thumbnail" style="height:150px !important">
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="border-dotted"></div>
    <div class="row mt-2">
      <div class="col-12">
        <div class="float-end">
          <button type="submit" class="btn btn-primary" onclick="_save(event)"><i class="fas fa-save me-2"></i> Simpan</button>
          <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Batal</button>
        </div>
      </div>
    </div>
  </div>
</form>
<script>
  $(document).ready(function() {
    $(".datetimepicker-lembur").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        timePicker24Hour: true,
        autoApply: false,
        autoUpdateInput: false,
        // minDate: "<?= date('d-m-Y 00:00:00', strtotime('-7 days')) ?>",
        // maxDate: "<?= date('d-m-Y 23:59:59') ?>",
        locale: {
          format: "DD-MM-YYYY HH:mm:ss",
          separator: " - ",
          applyLabel: "&nbsp; Pilih &nbsp;",
          cancelLabel: "&nbsp; Batal &nbsp;",
          fromLabel: "From",
          toLabel: "To",
          customRangeLabel: "Custom",
          weekLabel: "W",
          daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
          monthNames: [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
          ],
          firstDay: 1,
        },
        drops: "auto",
      },
      function(start, end, label) {

      }
    );

    $(".datetimepicker-lembur").on(
      "apply.daterangepicker",
      function(ev, picker) {
        $(this).val(picker.startDate.format("DD-MM-YYYY HH:mm:ss"));

        setTimeout(() => {
          var mulai = $("#mulai_tgl").val();
          var selesai = $("#selesai_tgl").val();
          var now = new Date(toDate(mulai, '', 'full_date'));
          var then = new Date(toDate(selesai, '', 'full_date'));
          var minsDiff = Math.floor((then.getTime() - now.getTime()) / 1000 / 60);
          var hoursDiff = Math.floor(minsDiff / 60);
          minsDiff = minsDiff % 60;
          minsDiff = minsDiff / 60;
          minsDiff = Math.round(minsDiff * 100) / 100
          if (selesai != '') {
            if (minsDiff != 0) {
              var result = hoursDiff + minsDiff;
              result = parseFloat(result).toFixed(2);
              $("#durasi").val(result);
            } else {
              $("#durasi").val(hoursDiff);
            }
          }
        }, 500);
      }
    );

    $(".datetimepicker-lembur").on(
      "cancel.daterangepicker",
      function(ev, picker) {
        $(this).val("");
      }
    );

  });
</script>