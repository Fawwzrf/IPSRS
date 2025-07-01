$(document).ready(function () {
  // code with document.ready
  $(".table-responsive").on("show.bs.dropdown", function () {
    $(".table-responsive").css("overflow", "inherit");
  });

  $(".table-responsive").on("hide.bs.dropdown", function () {
    $(".table-responsive").css("overflow", "auto");
  });

  // $(".select2").each(function () {
  //   $(this).select2({
  //     theme: "bootstrap-5",
  //     dropdownParent: $(this).parent(),
  //     width: $(this).data("width")
  //       ? $(this).data("width")
  //       : $(this).hasClass("w-100")
  //       ? "100%"
  //       : "style",
  //   });
  // });

  $(".chosen-select").each(function () {
    $(this).select2({
      theme: "bootstrap-5",
      dropdownParent: $(this).parent(),
      width: $(this).data("width")
        ? $(this).data("width")
        : $(this).hasClass("w-100")
        ? "100%"
        : "style",
    });
  });

  $(".select2-ajax").each(function () {
    const queryString = window.location.search;
    if (typeof $(this).data("inputlength") === "undefined") {
      var inputLength = '2';
    }else{
      var inputLength = $(this).data("inputlength");
    }
    $(this).select2({
      theme: "bootstrap-5",
      dropdownParent: $(this).parent(),
      width: $(this).data("width")
        ? $(this).data("width")
        : $(this).hasClass("w-100")
        ? "100%"
        : "style",
      minimumInputLength: inputLength,
      ajax: {
        url: _this_uri + "/" + $(this).data("url") + queryString,
        dataType: "json",

        data: function (params) {
          return {
            term: params.term,
          };
        },
        processResults: function (data) {
          return {
            results: data,
          };
        },
      },
    });
  });

  $(".page-link").click(function (e) {
    e.preventDefault();
    let href = $(this).attr("href");
    _page(href, "page");
  });

  $(".datepicker").daterangepicker(
    {
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: false,
      timePicker24Hour: false,
      autoApply: true,
      locale: {
        format: "DD-MM-YYYY",
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
    function (start, end, label) {
      // code if apply
    }
  );

  $(".timerangepicker")
    .daterangepicker({
      timePicker: true,
      timePicker24Hour: true,
      timePickerIncrement: 2,
      locale: {
        format: "HH:mm",
      },
    })
    .on("show.daterangepicker", function (ev, picker) {
      picker.container.find(".calendar-table").hide();
    });

  $(".timepicker")
    .daterangepicker({
      singleDatePicker: true,
      timePicker: true,
      timePicker24Hour: true,
      timePickerIncrement: 2,
      locale: {
        format: "HH:mm",
      },
    })
    .on("show.daterangepicker", function (ev, picker) {
      picker.container.find(".calendar-table").hide();
    });

  $(".datepicker-international").daterangepicker(
    {
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: false,
      timePicker24Hour: false,
      autoApply: true,
      locale: {
        format: "YYYY-MM-DD",
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
    function (start, end, label) {
      // code if apply
    }
  );

  $(".datepicker-notauto").daterangepicker(
    {
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: false,
      timePicker24Hour: false,
      autoApply: false,
      autoUpdateInput: false,
      locale: {
        format: "DD-MM-YYYY",
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
    function (start, end, label) {
      // code if apply
    }
  );

  $(".datepicker-notauto").on("apply.daterangepicker", function (ev, picker) {
    $(this).val(picker.startDate.format("DD-MM-YYYY"));
  });

  $(".datepicker-notauto").on("cancel.daterangepicker", function (ev, picker) {
    $(this).val("");
  });

  $(".datetimepicker").daterangepicker(
    {
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: true,
      timePicker24Hour: true,
      autoApply: true,
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
    function (start, end, label) {
      // code if apply
    }
  );

  $(".datetimepicker-notauto").daterangepicker(
    {
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: true,
      timePicker24Hour: true,
      autoApply: false,
      autoUpdateInput: false,
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
    function (start, end, label) {
      // code if apply
    }
  );

  $(".datetimepicker-notauto").on(
    "apply.daterangepicker",
    function (ev, picker) {
      $(this).val(picker.startDate.format("DD-MM-YYYY HH:mm:ss"));
    }
  );

  $(".datetimepicker-notauto").on(
    "cancel.daterangepicker",
    function (ev, picker) {
      $(this).val("");
    }
  );

  setInterval(() => {
    $(".datetimepicker.autoupdate").val(moment().format("DD-MM-YYYY HH:mm:ss"));
  }, 1000);

  setInterval(() => {
    $(".datetimeautoupdate").val(moment().format("DD-MM-YYYY HH:mm:ss"));
  }, 1000);

  /*
  Autonumeric
  */
  $(".autonumeric").autoNumeric({
    aSep: ".",
    aDec: ",",
    vMax: "999999999999999",
    vMin: "0",
  });
  $(".autonumeric-float").autoNumeric({
    aSep: ".",
    aDec: ",",
    aForm: true,
    vMax: "999999999999999999.99",
    vMin: "-999999999999999999.99",
    mDec: "4",
    aPad: false,
  });

  $(".autonumeric-float.keepzero").each(function () {
    $(this).on("change", function () {
      $(this).val(numId(numClear(this.value), true));
    });
    $(this).trigger("change");
  });

  setInterval(() => {
    $(".blink").fadeOut(500).fadeIn(500);
  }, 1000);

  let optionsTinyMCE = {
    selector: ".tinymce-area",
    height: 250,
    menubar: false,
    statusbar: false,
    plugins: "lists image",
    toolbar:
      " bold italic underline alignleft aligncenter alignright alignjustify numlist bullist",
    content_style:
      "body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; -webkit-font-smoothing: antialiased; }",
  };
  tinyMCE.init(optionsTinyMCE);

  let optionsTinyMCECPPT = {
    selector: ".tinymce-cppt-area",
    height: 600,
    menubar: false,
    statusbar: false,
    plugins: "lists image",
    toolbar:
      "bold italic underline alignleft aligncenter alignright alignjustify numlist bullist strikethrough",
    // images_upload_url: 'postAcceptor.php',
    // images_upload_handler: function(blobInfo, success, failure) {
    //   setTimeout(function() {
    //     success('http://moxiecode.cachefly.net/tinymce/v9/images/logo.png');
    //   }, 2000);
    // },
    // plugins: [
    //   'advlist autolink lists link image charmap print preview anchor',
    //   'searchreplace visualblocks code fullscreen',
    //   'insertdatetime media table paste code help wordcount'
    // ],
    // toolbar: 'undo redo | formatselect | ' +
    //   'bold italic backcolor | alignleft aligncenter ' +
    //   'alignright alignjustify | bullist numlist outdent indent | ' +
    //   'removeformat',
    content_style:
      "body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; -webkit-font-smoothing: antialiased; }",
  };
  tinyMCE.init(optionsTinyMCECPPT);

  let optionsTinyMCEJenisDokumen = {
    selector: ".tinymce-jenis-dokumen-area",
    height: 400,
    content_css: _base_url + "dist/css/itm.tinymce.css",
    // plugins: "table code",
    // menubar: false,
    // toolbar: "table bold italic underline alignleft aligncenter alignright fontsize code",
    plugins: [
      'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
      'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media',
      'table', 'emoticons', 'template', 'help'
    ],
    toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
      'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
      'forecolor backcolor emoticons | help',
    menu: {
      favs: { title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons' }
    },
    menubar: 'favs file edit view insert format tools table help'
  };
  tinyMCE.init(optionsTinyMCEJenisDokumen);

  let optionsTinyMCEEletter = {
    selector: ".tinymce-eletter-area",
    height: 400,
    table_use_colgroups: false,
    content_css: _base_url + "dist/css/itm.tinymce.css",
    // plugins: "table code",
    // menubar: false,
    // toolbar: "table bold italic underline alignleft aligncenter alignright fontsize code",
    plugins: [
      'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
      'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media',
      'table', 'emoticons', 'template', 'help'
    ],
    toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
      'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
      'forecolor backcolor emoticons | help',
    menu: {
      favs: { title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons' }
    },
    menubar: 'favs file edit view insert format tools table help'
  };
  tinyMCE.init(optionsTinyMCEEletter);

  /*
  Tooltip datatable
  */
  $(document).ajaxComplete(function () {
    // Required for Bootstrap tooltips in DataTables
    $('[data-bs-toggle="tooltip"]').tooltip({
      html: true,
      delay: { show: 100, hide: 0 },
    });
  });


  /*
  Voice To Text
  */
  var recognition = '';
  $(document).keydown(function (event) {
    // 
    // Key Press = ALT + SHIFT
    //
    var altKey = event.altKey;
    var keycode = event.which || event.keyCode;

    if (altKey && keycode === 16) {
      if (recognition == '') {
        console.log('Running Voice To Text');
        inputVoice();
      } else {
        console.log('Stop Voice To Text');
        recognition.stop();
        recognition = '';
      };
    }
  });

  function inputVoice() {
    recognition = new webkitSpeechRecognition() || new SpeechRecognition();
    recognition.lang = 'id-ID';
    recognition.continuous = true;
    recognition.interimResults = true;

    recognition.onresult = function (event) {
      var interimTranscript = '';
      var finalTranscript = '';

      for (var i = event.resultIndex; i < event.results.length; i++) {
        var transcript = event.results[i][0].transcript;

        if (event.results[i].isFinal) {
          finalTranscript += transcript;
        } else {
          interimTranscript += transcript;
        }
      }
      let input = $.trim($(':focus').val()) + ' ' + $.trim(finalTranscript);
      $(':focus').val($.trim(input));
    }
    recognition.start();
  }

});
