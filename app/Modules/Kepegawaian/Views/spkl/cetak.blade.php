<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SPKL <?= $main['spkl_id'] ?></title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14px;
      margin: 0px !important;
      padding: 0px !important;
    }

    .text-center {
      text-align: center;
    }

    .text-bold {
      font-weight: bold;
    }

    table {
      border-collapse: collapse;
    }

    td {
      vertical-align: top;
      font-size: 14px;
    }

    th {
      vertical-align: top;
      font-size: 14px;
    }

    #table-pegawai td {
      padding: 2px 4px;
    }

    #table-detail td {
      padding: 2px 4px;
    }

    .title {
      font-size: 16px;
    }
  </style>
</head>

<body>
  <img src="<?= 'data:image/png;base64,' . base64_encode(file_get_contents(@$identitas['kop_surat'])) ?>" width="100%">
  <div style="margin: 0px 55px;">
    <table width="100%">
      <tbody>
        <tr>
          <td class="text-center title"><b><u>SURAT PERINTAH KERJA LEMBUR</u></b></td>
        </tr>
        <tr>
          <td class="text-center"><b>No. <?= $main['spkl_id'] ?></b></td>
        </tr>
      </tbody>
    </table>
    <br>
    <table width="100%">
      <tbody>
        <tr>
          <td>Dengan hormat, </td>
        </tr>
        <tr>
          <td>Dengan ini diperintahkan kepada rekan-rekan di bawah ini :</td>
        </tr>
      </tbody>
    </table>
    <br>
    <table id="table-pegawai" width="100%" border="1">
      <thead>
        <tr>
          <th class="text-center" style="width:40px">No.</th>
          <th class="text-center">Nama</th>
          <th class="text-center">Jabatan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($all_spkl_pegawai as $key => $row) : ?>
          <tr>
            <td class="text-center" style="width:40px"><?= $key + 1 ?></td>
            <td><?= $row['pegawai_nm'] ?></td>
            <td><?= $row['jabatan_nm'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <br>
    <table width="100%">
      <tbody>
        <tr>
          <td>Untuk melakukan kerja lembur mengerjakan pekerjaan sebagai berikut :</td>
        </tr>
      </tbody>
    </table>
    <br>
    <div style="margin-left:40px;">
      <table id="table-detail" width="100%">
        <tbody>
          <tr>
            <td style="width:100px;">Tanggal</td>
            <td style="width:10px;">:</td>
            <td>
              <?= to_date_indo($main['mulai_tgl']) ?>
              <?php if ($main['mulai_tgl'] != $main['selesai_tgl']) : ?>
                s/d <?= to_date_indo($main['selesai_tgl']) ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php if ($main['mulai_jam'] != '00:00:00' && $main['selesai_jam'] != '00:00:00') : ?>
            <tr>
              <td>Waktu</td>
              <td>:</td>
              <td>
                <?= $main['mulai_jam'] ?> s/d <?= $main['selesai_jam'] ?>
              </td>
            </tr>
          <?php endif; ?>
          <?php if ($main['durasi'] != '') : ?>
            <tr>
              <td>Durasi</td>
              <td>:</td>
              <td>
                <?= $main['durasi'] ?>
              </td>
            </tr>
          <?php endif; ?>
          <tr>
            <td>Lokasi</td>
            <td>:</td>
            <td><?= $main['lokasi'] ?></td>
          </tr>
          <tr>
            <td>Pekerjaan</td>
            <td>:</td>
            <td><?= $main['pekerjaan'] ?></td>
          </tr>
          <?php if ($main['berkas_1_url'] != '' || $main['berkas_2_url'] != '' || $main['berkas_3_url'] != '') : ?>
            <tr>
              <td>Berkas</td>
              <td>:</td>
              <td>
                <?php if ($main['berkas_1_url'] != '') : ?>
                  <a href="<?= $main['berkas_1_url'] ?>"><?= $main['berkas_1_judul'] ?></a><br>
                <?php endif; ?>
                <?php if ($main['berkas_2_url'] != '') : ?>
                  <a href="<?= $main['berkas_2_url'] ?>"><?= $main['berkas_2_judul'] ?></a><br>
                <?php endif; ?>
                <?php if ($main['berkas_3_url'] != '') : ?>
                  <a href="<?= $main['berkas_3_url'] ?>"><?= $main['berkas_3_judul'] ?></a><br>
                <?php endif; ?>
              </td>
            </tr>
          <?php endif; ?>
          <?php if ($main['keterangan'] != '') : ?>
            <tr>
              <td>Keterangan</td>
              <td>:</td>
              <td><?= $main['keterangan'] ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <br>
    <table width="100%">
      <tbody>
        <tr>
          <td>Demikian surat perintah kerja lembur ini dibuat agar bisa dilaksanakan dengan sebaik-baiknya.</td>
        </tr>
      </tbody>
    </table>
    <br>
    <table width="100%">
      <tbody>
        <tr>
          <td style="width:60%"></td>
          <td class="text-center">
            Bantul, <?= to_date_indo($main['pembuat_tgl'], 'date') ?>
            <br>
            <?php if ($main['pembuat_ttd'] != '') : ?>
              <img style="width: 150px;" src="<?= 'data:image/png;base64,' . base64_encode(file_get_contents(@$main['pembuat_ttd'])) ?>" st>
              <br>
            <?php else : ?>
              <br><br><br><br><br>
            <?php endif; ?>
            <?= $main['pembuat_nm'] ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</body>

</html>