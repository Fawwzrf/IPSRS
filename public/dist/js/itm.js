/*
01. LOAD PAGE
*/

function _page(uri, type = null, modal_idx = 1) {
    if (type != "blank") {
        loadingShow();
    }

    if (type == "page") {
        uri = uri;
    } else {
        const arrQuestionMark = uri.split("?");
        if (arrQuestionMark.length > 0) {
            uri = arrQuestionMark[0];
        }

        // @kondisi jika ada #, di delete
        const arrHashMark = uri.split("#");
        if (arrHashMark.length > 1) {
            uri = arrHashMark[0];
        }

        const queryString = window.location.search;
        const hashString = window.location.hash;

        uri += queryString;
        if (arrQuestionMark.length > 0) {
            for (let i = 0; i < arrQuestionMark.length; i++) {
                if (i > 0) {
                    uri += "&" + arrQuestionMark[i];
                }
            }
        }

        // if (arrHashMark.length > 1) {
        //   uri += hashString;
        // }

        // @kondisi jika ada #, posisi dipindah di paling belakang
        if (arrHashMark.length > 1) {
            uri += "#" + arrHashMark[1];
        }

        if (type == "search") {
            uri = updateQueryStringParameter(uri, "p", 0);
        }
    }

    if (type == "blank") {
        window.open(uri);
    } else {
        $("#my-modal-" + modal_idx).modal("hide");
        window.history.pushState(null, null, uri);
        $.ajax({
            url: uri,
            type: "GET",
            data: {
                _is_ajax: true,
                _token: _token,
            },
            success: function (resp) {
                loadingHide();
                jQuery.loadScript = function (url, callback) {
                    jQuery.ajax({
                        url: _base_url + "dist/js/itm.load.js",
                        dataType: "script",
                        success: callback,
                        async: false,
                    });
                };
                if (typeof someObject == "undefined")
                    $.loadScript(
                        _base_url + "dist/js/itm.load.js",
                        function () {
                            $("#container").html(resp);
                        }
                    );
                if (window.location.hash) {
                    // @comment by alfian
                    // _tab(window.location.hash.replace("#", ""));
                    var navId = window.location.hash.replace("#", "");
                    var hashSlash = false;
                    var splitNav = "";
                    if (navId.indexOf("/") > -1) {
                        hashSlash = true;
                        splitNav = navId.split("/");
                        var navId = splitNav[0];
                    }

                    if (hashSlash == true) {
                        // @nav
                        $('a[id="nav_' + navId + '"]')
                            .closest("a")
                            .addClass("active")
                            .attr("aria-selected", "true");
                        $('a[id="nav_' + navId + '"]').click();

                        setTimeout(function () {
                            // @subnav
                            if (typeof splitNav[2] === "undefined") {
                                $('a[id="subnav_' + splitNav[1] + '"]').click();
                            } else {
                                $(
                                    'a[id="subnav_' +
                                        splitNav[1] +
                                        "_" +
                                        splitNav[2] +
                                        '"]'
                                ).click();
                            }
                        }, 1000);
                    } else {
                        // @nav
                        $(
                            'a[id="nav_' +
                                window.location.hash.replace("#", "") +
                                '"]'
                        )
                            .closest("a")
                            .addClass("active")
                            .attr("aria-selected", "true");
                        // @add by alfian
                        $(
                            'a[id="nav_' +
                                window.location.hash.replace("#", "") +
                                '"]'
                        ).click();
                    }
                }
            },
        });
    }
}

function _tab(href, data = null) {
    const queryStringSearch = window.location.search;
    let hash = "#" + href;
    history.pushState("", "", hash);
    $("#tab_active_form").val(href);
    $("#tabs-body").addClass("active show");
    $("#tabs-body").html(
        '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    );
    $.post(
        _this_uri + "/" + href + queryStringSearch,
        {
            _is_ajax: true,
            _token: _token,
            data: data,
        },
        function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            if (typeof someObject == "undefined")
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#tabs-body").html(resp);
                });
        },
        "json"
    );
}

function _perpage(p, n) {
    $.post(
        _site_url + "/app/perpage",
        {
            _is_ajax: true,
            _token: _token,
            data: {
                p: p,
                n: n,
            },
        },
        function (res) {
            if ("uri" in res) {
                _page(res.uri, "search");
            }
        },
        "json"
    );
}

function _search(e) {
    e.preventDefault();
    let formId = e.target?.form?.id;
    let formAction = e.target?.form?.action;

    if (typeof e.target.form === "undefined") {
        formId = e.target.parentElement.form.id;
        formAction = e.target.parentElement.form.action;
    }

    let data = objectifyForm($("#" + formId).serializeArray());

    for (let key in data) {
        if (Array.isArray(data[key])) {
            // Ubah array menjadi string dengan format 'value1', 'value2'
            data[key] = data[key].map((item) => `'${item}'`).join(",");
        }
    }

    $.post(
        formAction,
        {
            _is_ajax: true,
            _token: _token,
            data: data,
        },
        function (res) {
            if ("uri" in res) {
                console.log(res);
                _page(res.uri, "search");
            }
        },
        "json"
    );
}

function _searchReset() {
    $.post(
        _base_url + "app/search_reset?n=" + _n,
        {
            _is_ajax: true,
            _token: _token,
        },
        function (res) {
            if ("uri" in res) {
                _page(res.uri, "search");
            }
        },
        "json"
    );
}

function _setUri(uri) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    uri += queryString;
    return uri;
}

function _save(e, params = {}) {
    var callback =
        typeof params.callback === "undefined" ? null : params.callback;
    var modal_idx =
        typeof params.modal_idx === "undefined" ? 1 : params.modal_idx;
    var modal_idx_close =
        typeof params.modal_idx_close === "undefined"
            ? null
            : params.modal_idx_close;

    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  callback: [function callback]
  modal_idx = [1] modal ke berapa
  modal_idx_close = [1,2,3] -> tutup modal ke 1,2 dan 3
  */

    let formId = e.target?.form?.id;
    if (typeof e.target.form === "undefined") {
        formId = e.target?.parentElement?.form?.id;
    }

    const queryString = window.location.search;
    const hashString = window.location.hash;

    $("#" + formId).validate({
        rules: {},
        messages: {},
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.next("label"));
            } else if ($(element).hasClass("chosen-select")) {
                error
                    .insertAfter(element.next(".select2-container"))
                    .addClass("mt-n2 mb-1");
            } else if ($(element).hasClass("select2-ajax")) {
                error
                    .insertAfter(element.next(".select2-container"))
                    .addClass("mt-n2 mb-1");
            } else if (element.prop("type") === "radio") {
                error
                    .appendTo(element.parents(".input-checkbox"))
                    .addClass("d-block");
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        submitHandler: function (form) {
            $("button[type='submit']").attr("disabled", true);
            loadingShow();
            let formAction = e.target?.form?.action;
            if (typeof e.target.form === "undefined") {
                formAction = e.target.parentElement.form.action;
            }
            formAction += queryString;
            formAction += hashString;

            var formData = new FormData(form);
            formData.append("_is_ajax", true);
            formData.append("_token", _token);

            $.ajax({
                type: "POST",
                url: formAction,
                data: formData,
                processData: false,
                contentType: false,
            })
                .done(function (res) {
                    if (res == null) {
                        loadingHide();
                        $("button[type='submit']").attr("disabled", false);
                        _toast("error", "Terjadi kesalahan sistem");
                    } else {
                        var message = res.message;
                        if (res.data !== null) {
                            if (res.data != "") {
                                if (
                                    typeof res.data === "object" ||
                                    Array.isArray(res.data)
                                ) {
                                    message = res.message;
                                } else {
                                    message = res.message + "<br>" + res.data;
                                }
                            }
                        }
                        if (res.status) {
                            _modalHide(modal_idx);

                            if (modal_idx_close != null) {
                                $.each(
                                    modal_idx_close,
                                    function (index, value) {
                                        _modalHide(value);
                                    }
                                );
                            }

                            _toast("success", message);
                            if ("uri" in res) {
                                loadingHide();

                                if (callback != null) {
                                    callback(res);
                                } else {
                                    _page(res.uri, modal_idx);
                                }
                            }
                        } else {
                            loadingHide();
                            $("button[type='submit']").attr("disabled", false);
                            _toast("error", message);
                        }
                    }
                })
                .fail(function (xhr, status, error) {
                    loadingHide();
                    $("button[type='submit']").attr("disabled", false);
                    _toast(
                        "error",
                        "Terjadi kesalahan sistem<br>" +
                            xhr.status +
                            " (" +
                            error +
                            ")"
                    );
                });
            return false;
        },
    });
}

function _reload() {
    location.reload();
}

function _toast(type = "success", message = "") {
    $.toast({
        heading: type == "success" ? "Berhasil" : "Kesalahan",
        text: message,
        icon: type,
        position: "top-right",
    });
}

function _confirm(uri, data = {}) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    var postConfirm = {};
    postConfirm["_is_ajax"] = true;
    postConfirm["_token"] = _token;
    postConfirm["data"] = data;

    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Aksi ini tidak bisa dikembalikan. Data ini mungkin terhubung dengan data lain.",
        icon: "warning",
        customClass: "swal-wide",
        showCancelButton: true,
        cancelButtonColor: "#858F9B",
        cancelButtonText: "Batal",
        confirmButtonColor: "#3376B8",
        confirmButtonText: "Lanjutkan",
    }).then((result) => {
        if (result.isConfirmed) {
            loadingShow();
            $.ajax({
                type: "POST",
                url: uri + "?n=" + urlParams.get("n"),
                dataType: "json",
                data: postConfirm,
                success: function (res) {
                    if (res == null) {
                        loadingHide();
                        _toast("error", "Terjadi kesalahan sistem");
                    } else {
                        var message = res.message;
                        if (res.data !== null) {
                            if (res.data != "") {
                                if (
                                    typeof res.data === "object" ||
                                    Array.isArray(res.data)
                                ) {
                                    message = res.message;
                                } else {
                                    message = res.message + "<br>" + res.data;
                                }
                            }
                        }
                        if (res.status) {
                            _toast("success", message);
                            if ("uri" in res) {
                                _page(res.uri);
                            }
                        } else {
                            loadingHide();
                            _toast("error", message);
                        }
                    }
                },
                error: function (textStatus, errorThrown) {
                    loadingHide();
                    _toast(
                        "error",
                        "Terjadi kesalahan sistem <br> Error : " + errorThrown
                    );
                },
            });
        }
    });
}

function _delete(uri, data = {}) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    // alertConfirm({
    //   title: "Apakah Anda yakin?",
    //   desc: "Aksi ini tidak bisa dikembalikan. Data ini mungkin terhubung dengan data lain.",
    //   icon: "warning",
    //   buttonConfirm: "Hapus",
    //   uriConfirm: uri,
    //   postConfirm: { _is_ajax: true, _token: _token, data },
    //   uriRedirect: "",
    // });

    var postConfirm = {};
    postConfirm["_is_ajax"] = true;
    postConfirm["_token"] = _token;
    postConfirm["data"] = data;

    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Aksi ini tidak bisa dikembalikan. Data ini mungkin terhubung dengan data lain.",
        icon: "warning",
        customClass: "swal-wide",
        showCancelButton: true,
        cancelButtonColor: "#858F9B",
        cancelButtonText: "Batal",
        confirmButtonColor: "#3376B8",
        confirmButtonText: "Hapus",
    }).then((result) => {
        if (result.isConfirmed) {
            loadingShow();
            $.ajax({
                type: "POST",
                url: uri + "?n=" + urlParams.get("n"),
                dataType: "json",
                data: postConfirm,
                success: function (res) {
                    if (res == null) {
                        loadingHide();
                        _toast("error", "Terjadi kesalahan sistem");
                    } else {
                        var message = res.message;
                        if (res.data !== null) {
                            if (res.data != "") {
                                if (
                                    typeof res.data === "object" ||
                                    Array.isArray(res.data)
                                ) {
                                    message = res.message;
                                } else {
                                    message = res.message + "<br>" + res.data;
                                }
                            }
                        }
                        if (res.status) {
                            _toast("success", message);
                            if ("uri" in res) {
                                _page(res.uri);
                            }
                        } else {
                            loadingHide();
                            _toast("error", message);
                        }
                    }
                },
                error: function (textStatus, errorThrown) {
                    loadingHide();
                    _toast(
                        "error",
                        "Terjadi kesalahan sistem <br> Error : " + errorThrown
                    );
                },
            });
        }
    });
}

function _modalTtd(callback, type = "normal", label = "", name = "") {
    /* DOCUMENTATION / CONFIGURATION : 
  type: 
    - normal (tanda tangan normal tanpa form nama)
    - nama (tanda tangan ada form nama)
  label: nama label untuk form nama
  name: name form untuk form nama
  */
    $("#ttd-modal").modal("show");
    if (type == "nama") {
        $("#box-form-ttd-modal").removeClass("d-none");
        $("#btn-simpan-ttd-modal").attr("onClick", "canvasSave(0, 'nama')");
    } else {
        $("#box-form-ttd-modal").addClass("d-none");
        $("#btn-simpan-ttd-modal").attr("onClick", "canvasSave(0)");
    }
    $("#name-ttd-modal").val("");
    if (name != "") {
        $("#name-ttd-modal").val($('input[name="' + name + '"]').val());
    }
    $("#label-ttd-modal").html(label);

    var canvasOptions = [
        {
            canvas_id: 0,
            canvas_name: "ttd",
            height: 500,
            width: 1450,
            callback: callback,
        },
    ];

    canvasInit(canvasOptions);
}

function _modalTtdHide() {
    $("#ttd-modal").modal("hide");
}

function _modal(e, arg, idx = 1) {
    let title = e.target.innerText;
    if (typeof arg.title === "undefined") {
        title = e.target.innerText;
    } else if (arg.title != "") {
        title = arg.title;
    }
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    arg.uri += queryString;
    $("#modal-size-" + idx)
        .removeClass("modal-xl")
        .removeClass("modal-lg")
        .removeClass("modal-md")
        .removeClass("modal-sm")
        .removeClass("modal-full-width")
        .removeClass("modal-dialog-centered");
    $("#modal-size-" + idx).removeAttr("style");

    $("#modal-title-" + idx).html(title);
    $("#modal-size-" + idx).addClass(arg.size);

    if (typeof arg.customSize !== undefined) {
        $("#modal-size-" + idx).css({ width: arg.customSize });
        $("#modal-size-" + idx).css({ "max-width": arg.customSize });
    }

    if (arg.position == "center") {
        $("#modal-size-" + idx).addClass("modal-dialog-centered");
    }

    $.ajax({
        url: arg.uri,
        type: "GET",
        data: {
            _is_ajax: true,
            _token: _token,
        },
        success: function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                $("#modal-body-" + idx).html(resp);
                // Show modal after content is injected
                $("#my-modal-" + idx).modal("show");
            });
        },
    });
}

function _modalNoEvent(arg, idx = 1) {
    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  uri: [url modal] -> wajib ada
  size: modal-xl, modal-lg, modal-md, modal-sm, modal-full-width
  position: normal, center
  title: title modal

  idx = modal ke
  */
    if (typeof arg.title === "undefined") {
        title = "";
    } else if (arg.title != "") {
        title = arg.title;
    }
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    arg.uri += queryString;
    $("#modal-size-" + idx)
        .removeClass("modal-xl")
        .removeClass("modal-lg")
        .removeClass("modal-md")
        .removeClass("modal-sm")
        .removeClass("modal-full-width")
        .removeClass("modal-dialog-centered");
    $("#modal-size-" + idx).removeAttr("style");

    $("#my-modal-" + idx).modal("show");
    $("#modal-title-" + idx).html(title);
    $("#modal-size-" + idx).addClass(arg.size);

    if (typeof arg.customSize !== undefined) {
        $("#modal-size-" + idx).css({ width: arg.customSize });
        $("#modal-size-" + idx).css({ "max-width": arg.customSize });
    }

    if (arg.position == "center") {
        $("#modal-size-" + idx).addClass("modal-dialog-centered");
    }
    // $("#modal-body-" + idx).html(
    //   '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    // );
    $.post(
        arg.uri,
        {
            _is_ajax: true,
            _token: _token,
        },
        function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            if (typeof someObject == "undefined")
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#modal-body-" + idx).html(resp);
                });
        },
        "json"
    );
}

function _modalHide(idx = 1) {
    $("#my-modal-" + idx).modal("hide");
}

function _modalPrint(e, arg, idx = 1) {
    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  uri: [url modal] -> wajib ada
  size: modal-xl, modal-lg, modal-md, modal-sm, modal-full-width
  position: normal, center
  title: title modal

  idx = modal ke
  */
    let uri = _site_url + "/app/printpage/print_modal";
    let title = e.target.innerText;
    if (typeof arg.title === "undefined") {
        title = e.target.innerText;
    } else if (arg.title != "") {
        title = arg.title;
    }
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    uri += queryString;
    $("#modal-size-" + idx)
        .removeClass("modal-xl")
        .removeClass("modal-lg")
        .removeClass("modal-md")
        .removeClass("modal-sm")
        .removeClass("modal-full-width")
        .removeClass("modal-dialog-centered");
    $("#modal-size-" + idx).removeAttr("style");

    $("#my-modal-" + idx).modal("show");
    $("#modal-title-" + idx).html(title);
    $("#modal-size-" + idx).addClass(arg.size);

    if (typeof arg.customSize !== undefined) {
        $("#modal-size-" + idx).css({ width: arg.customSize });
        $("#modal-size-" + idx).css({ "max-width": arg.customSize });
    }

    if (arg.position == "center") {
        $("#modal-size-" + idx).addClass("modal-dialog-centered");
    }
    // $("#modal-body-" + idx).html(
    //   '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    // );
    $.post(
        uri,
        {
            _is_ajax: true,
            _token: _token,
            _uri: arg.uri + queryString,
        },
        function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            if (typeof someObject == "undefined")
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#modal-body-" + idx).html(resp);
                });
        },
        "json"
    );
}

function _modalPrintTTE(e, arg, idx = 1) {
    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  uri: [url modal] -> wajib ada
  size: modal-xl, modal-lg, modal-md, modal-sm, modal-full-width
  position: normal, center
  title: title modal

  idx = modal ke
  */
    let uri = _site_url + "/app/printpage/print_tte_modal";
    let title = e.target.innerText;
    if (typeof arg.title === "undefined") {
        title = e.target.innerText;
    } else if (arg.title != "") {
        title = arg.title;
    }
    new_uri = arg.uri;
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    uri += queryString;
    new_uri += queryString;
    $("#modal-size-" + idx)
        .removeClass("modal-xl")
        .removeClass("modal-lg")
        .removeClass("modal-md")
        .removeClass("modal-sm")
        .removeClass("modal-full-width")
        .removeClass("modal-dialog-centered");
    $("#modal-size-" + idx).removeAttr("style");

    $("#my-modal-" + idx).modal("show");
    $("#modal-title-" + idx).html(title);
    $("#modal-size-" + idx).addClass(arg.size);

    if (typeof arg.customSize !== undefined) {
        $("#modal-size-" + idx).css({ width: arg.customSize });
        $("#modal-size-" + idx).css({ "max-width": arg.customSize });
    }

    if (arg.position == "center") {
        $("#modal-size-" + idx).addClass("modal-dialog-centered");
    }
    // $("#modal-body-" + idx).html(
    //   '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    // );
    $.post(
        new_uri,
        {
            _is_ajax: true,
            _token: _token,
            _uri: arg.uri + queryString,
        },
        function (resp) {
            $.post(
                uri,
                {
                    _is_ajax: true,
                    _token: _token,
                    _uri: resp["url"],
                },
                function (resp) {
                    jQuery.loadScript = function (url, callback) {
                        jQuery.ajax({
                            url: _base_url + "dist/js/itm.load.js",
                            dataType: "script",
                            success: callback,
                            async: false,
                        });
                    };
                    if (typeof someObject == "undefined")
                        $.loadScript(
                            _base_url + "dist/js/itm.load.js",
                            function () {
                                $("#modal-body-" + idx).html(resp);
                            }
                        );
                },
                "json"
            );
        },
        "json"
    );
}

function _offcanvas(e, arg, type = "end") {
    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  uri: [url modal] -> wajib ada
  title: [judul] -> tidak wajib
  */
    let title = e.target.innerText;
    if (typeof arg.title === "undefined") {
        title = e.target.innerText;
    } else if (arg.title != "") {
        title = arg.title;
    }
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    arg.uri += queryString;

    if (type == "end") {
        var offcanvas = document.getElementById("offcanvasEnd");
        var offcanvasBody = "offcanvas-end-body";
        var offcanvasTitle = "offcanvas-end-title";
    } else {
        var offcanvas = document.getElementById("offcanvasStart");
        var offcanvasBody = "offcanvas-start-body";
        var offcanvasTitle = "offcanvas-start-title";
    }

    // Size
    if (typeof arg.width !== "undefined") {
        $(offcanvas).css("width", arg.width);
    }
    // End Size

    var bsOffCanvas = new bootstrap.Offcanvas(offcanvas);
    bsOffCanvas.show();

    $("#" + offcanvasTitle).html(title);
    $("#" + offcanvasBody).html(
        '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    );
    $.post(
        arg.uri,
        {
            _is_ajax: true,
            _token: _token,
        },
        function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            if (typeof resp !== "undefined") {
                $("#" + offcanvasTitle).html(resp.title);
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasBody).html(resp);
                });
            } else {
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasBody).html(
                        "<div class='alert alert-danger'>Error load content!</div>"
                    );
                });
            }
        },
        "json"
    );
}

function _offcanvas_static(e, arg, type = "end") {
    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  uri: [url modal] -> wajib ada
  title: [judul] -> tidak wajib
  */

    var containerSelection = $("#boxContainerSelection");
    var offcanvasStaticTitle = arg.title;
    var offcanvasStaticHeader = arg.header;
    var offcanvasStaticBody = arg.body;
    $("#" + offcanvasStaticHeader).html(
        '<h6 class="card-title"><a href="javascript:void(0)" class="btn btn-secondary" onclick="_offcanvas_static_close()">X</a>&nbsp;&nbsp;' +
            offcanvasStaticTitle +
            "</h6>"
    );
    $("#" + offcanvasStaticBody).html(
        '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    );
    $.post(
        arg.uri,
        {
            _is_ajax: true,
            _token: _token,
        },
        function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            if (typeof resp !== "undefined") {
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasStaticBody).html(resp);
                });
            } else {
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasStaticBody).html(
                        "<div class='alert alert-danger'>Error load content!</div>"
                    );
                });
            }
        },
        "json"
    );
    // open
    $("#boxContainerMain").addClass("col-lg-9").removeClass("col-lg-12");
    $("#boxContainerSidebar").removeClass("d-none");
    $(window).scrollTop(0);
}

function _offcanvas_static_close() {
    $("#boxContainerMain").removeClass("col-lg-9").addClass("col-lg-12");
    $("#boxContainerSidebar").addClass("d-none");
}

function _offcanvas_static_full_right(e, arg, type = "end") {
    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  uri: [url modal] -> wajib ada
  title: [judul] -> tidak wajib
  */

    var containerSelection = $("#boxContainerSelection");
    var offcanvasStaticTitle = arg.title;
    var offcanvasStaticHeader = arg.header;
    var offcanvasStaticBody = arg.body;
    $("#" + offcanvasStaticHeader).html(
        '<h6 class="card-title"><a href="javascript:void(0)" class="btn btn-secondary" onclick="_offcanvas_static_full_right_close()">X</a>&nbsp;&nbsp;' +
            offcanvasStaticTitle +
            "</h6>"
    );
    $("#" + offcanvasStaticBody).html(
        '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    );
    $.post(
        arg.uri,
        {
            _is_ajax: true,
            _token: _token,
        },
        function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            if (typeof resp !== "undefined") {
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasStaticBody).html(resp);
                });
            } else {
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasStaticBody).html(
                        "<div class='alert alert-danger'>Error load content!</div>"
                    );
                });
            }
        },
        "json"
    );
    // open
    $("#boxContainerFullMain").addClass("col-lg-9").removeClass("col-lg-12");
    $("#boxContainerFullRightSidebar").removeClass("d-none");
    $("#boxContainerFullLeftSidebar").addClass("d-none");
    $("#boxImageFull").addClass("d-none");
    $("#boxDetailPasienFull")
        .removeClass("col-lg-11 col-md-10")
        .addClass("col-lg-12");
    $(window).scrollTop(0);
}

function _offcanvas_static_full_right_close() {
    $("#boxContainerFullMain").removeClass("col-lg-9").addClass("col-lg-12");
    $("#boxContainerFullRightSidebar").addClass("d-none");
    $("#boxContainerFullLeftSidebar").addClass("d-none");
    $("#boxImageFull").removeClass("d-none");
    $("#boxDetailPasienFull")
        .removeClass("col-lg-12")
        .addClass("col-lg-11 col-md-10");
}

function _offcanvas_static_full_left(e, arg, type = "end") {
    /* DOCUMENTATION / CONFIGURATION : 
  event: [event] -> wajib ada
  uri: [url modal] -> wajib ada
  title: [judul] -> tidak wajib
  */

    var containerSelection = $("#boxContainerSelection");
    var offcanvasStaticTitle = arg.title;
    var offcanvasStaticHeader = arg.header;
    var offcanvasStaticBody = arg.body;
    $("#" + offcanvasStaticHeader).html(
        '<h6 class="card-title"><a href="javascript:void(0)" class="btn btn-secondary" onclick="_offcanvas_static_full_left_close()">X</a>&nbsp;&nbsp;' +
            offcanvasStaticTitle +
            "</h6>"
    );
    $("#" + offcanvasStaticBody).html(
        '<div class="text-center"><div class="spinner-border spinner-lg"></div><br><h3 class="mt-1">Loading<span class="animated-dots"></span></h3></div>'
    );
    $.post(
        arg.uri,
        {
            _is_ajax: true,
            _token: _token,
        },
        function (resp) {
            jQuery.loadScript = function (url, callback) {
                jQuery.ajax({
                    url: _base_url + "dist/js/itm.load.js",
                    dataType: "script",
                    success: callback,
                    async: false,
                });
            };
            if (typeof resp !== "undefined") {
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasStaticBody).html(resp);
                });
            } else {
                $.loadScript(_base_url + "dist/js/itm.load.js", function () {
                    $("#" + offcanvasStaticBody).html(
                        "<div class='alert alert-danger'>Error load content!</div>"
                    );
                });
            }
        },
        "json"
    );
    // open
    $("#boxContainerFullMain").addClass("col-lg-9").removeClass("col-lg-12");
    $("#boxContainerFullLeftSidebar").removeClass("d-none");
    $("#boxContainerFullRightSidebar").addClass("d-none");
    $("#boxImageFull").addClass("d-none");
    $("#boxDetailPasienFull")
        .removeClass("col-lg-11 col-md-10")
        .addClass("col-lg-12");
    $(window).scrollTop(0);
}

function _offcanvas_static_full_left_close() {
    $("#boxContainerFullMain").removeClass("col-lg-9").addClass("col-lg-12");
    $("#boxContainerFullLeftSidebar").addClass("d-none");
    $("#boxContainerFullRightSidebar").addClass("d-none");
    $("#boxImageFull").removeClass("d-none");
    $("#boxDetailPasienFull")
        .removeClass("col-lg-12")
        .addClass("col-lg-11 col-md-10");
}

/*
02. CUSTOM
*/
function toDate(date = "", sp = "-", tp = "", sp2 = " ") {
    result = "";
    if (date != "" && date != null && date != "null") {
        if (tp == "date") {
            arr_date = date.split(" ");
            date = arr_date[0];
        } else if (tp == "full_date") {
            arr_date = date.split(" ");
            date = arr_date[0];
            time = arr_date[1];
        }
        arr = date.split("-");
        if (sp != "") {
            result = arr[2] + sp + arr[1] + sp + arr[0];
        } else {
            result = arr[2] + "-" + arr[1] + "-" + arr[0];
        }
        if (tp == "full_date") {
            if (sp2 != "") {
                result = result + sp2 + time;
            } else {
                result = result + " " + $time;
            }
        }
    } else {
        result = "";
    }
    return result;
}

function toTime(time = "", sp = ":", tp = "", sp2 = "") {
    result = "";
    if (time != "" && time != null && time != "null") {
        if (tp == "hour_minute") {
            arr_time = time.split(":");
            hour = arr_time[0];
            minute = arr_time[1];
        }
        arr = time.split(":");
        if (sp != "") {
            result = arr[2] + sp + arr[1] + sp + arr[0];
        } else {
            result = arr[2] + ":" + arr[1] + ":" + arr[0];
        }
        if (tp == "hour_minute") {
            if (sp2 != "") {
                result = hour + sp2 + minute;
            } else {
                result = hour + ":" + minute;
            }
        }
    } else {
        result = "";
    }
    return result;
}

function hitungUmur(val) {
    var tanggal = toDate(val);
    if (tanggal != "") {
        var umur = getAge(tanggal);
        $("[name='umur_thn']").val(umur.years);
        $("[name='umur_bln']").val(umur.months);
        $("[name='umur_hari']").val(umur.days);
    } else {
        $("[name='umur_thn']").val("0");
        $("[name='umur_bln']").val("0");
        $("[name='umur_hari']").val("0");
    }
}

function getAge(dateString) {
    var now = new Date();
    var today = new Date(now.getYear(), now.getMonth(), now.getDate());

    var yearNow = now.getYear();
    var monthNow = now.getMonth();
    var dateNow = now.getDate();

    var dob = new Date(
        dateString.substring(0, 4),
        dateString.substring(5, 7) - 1,
        dateString.substring(8, 10)
    );

    var yearDob = dob.getYear();
    var monthDob = dob.getMonth();
    var dateDob = dob.getDate();
    var age = {};
    var ageString = "";
    var yearString = "";
    var monthString = "";
    var dayString = "";

    yearAge = yearNow - yearDob;

    if (monthNow >= monthDob) var monthAge = monthNow - monthDob;
    else {
        yearAge--;
        var monthAge = 12 + monthNow - monthDob;
    }

    if (dateNow >= dateDob) var dateAge = dateNow - dateDob;
    else {
        monthAge--;
        var dateAge = 31 + dateNow - dateDob;

        if (monthAge < 0) {
            monthAge = 11;
            yearAge--;
        }
    }

    age = {
        years: yearAge,
        months: monthAge,
        days: dateAge,
    };

    return age;
}

function isInt(n) {
    return Number(n) === n && n % 1 === 0;
}

function isFloat(n) {
    return Number(n) === n && n % 1 !== 0;
}

function numClear(x) {
    if (x === null || x == "" || x === undefined) {
        return 0;
    } else {
        x = x.toString();
        x = x.replace(/\./g, "");
        if (x.includes(",")) {
            x = x.replace(",", ".");
            x = parseFloat(x);
        } else {
            x = parseInt(x);
        }

        return x;
    }
}

function numId(bilangan, comma = false) {
    if (bilangan === null) {
        return 0;
    } else {
        var negativ = false;

        if (bilangan < 0) {
            bilangan = bilangan * -1;
            negativ = true;
        }

        bilangan = parseFloat(bilangan);
        if (comma == true) {
            bilangan = bilangan.toFixed(2);
        }

        var number_string = bilangan.toString(),
            split = number_string.split("."),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{1,3}/gi);

        if (ribuan) {
            separator = sisa ? "." : "";
            rupiah += separator + ribuan.join(".");
        }

        if (comma == true) {
            rupiah =
                split[1] != undefined
                    ? rupiah + "," + split[1]
                    : rupiah + ",00";
        } else {
            rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
        }

        // Cetak hasil
        if (negativ == true) {
            rupiah = "-" + rupiah;
        }
        return rupiah;
    }
}

function numSys(x) {
    if (x === null || x == "" || x === undefined) {
        return 0;
    } else {
        x = x.replace(/\./g, "");
        x = x.replace(",", ".");
        return x;
    }
}

// function objectifyForm(formArray) {
//   //serialize data function
//   var returnArray = {};
//   for (var i = 0; i < formArray.length; i++) {
//     returnArray[formArray[i]["name"]] = formArray[i]["value"];
//   }
//   return returnArray;
// }

function objectifyForm(formArray) {
    var returnArray = {};
    for (var i = 0; i < formArray.length; i++) {
        var key = formArray[i]["name"];
        if (key.indexOf("[]") > -1) {
            key = key.replace("[]", "");
            if (!(key in returnArray)) {
                returnArray[key] = [];
            }
            returnArray[key].push(formArray[i]["value"]);
        } else {
            returnArray[formArray[i]["name"]] = formArray[i]["value"];
        }
    }
    return returnArray;
}

function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == " ") {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function addLeadingZeros(num, totalLength) {
    if (num < 0) {
        const withoutMinus = String(num).slice(1);
        return "-" + withoutMinus.padStart(totalLength, "0");
    }

    return String(num).padStart(totalLength, "0");
}

function exactAge(birthdate, nowdate = null) {
    let startDate = new Date(new Date(birthdate).toISOString().substr(0, 10));
    var endingDate = null;
    if (nowdate == null) {
        endingDate = new Date().toISOString().substr(0, 10); // YYYY-MM-DD
    } else {
        endingDate = new Date(nowdate).toISOString().substr(0, 10); // YYYY-MM-DD
    }

    let endDate = new Date(endingDate);
    if (startDate > endDate) {
        const swap = startDate;
        startDate = endDate;
        endDate = swap;
    }
    const startYear = startDate.getFullYear();

    // Leap years
    const february =
        (startYear % 4 === 0 && startYear % 100 !== 0) || startYear % 400 === 0
            ? 29
            : 28;
    const daysInMonth = [31, february, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    let yearDiff = endDate.getFullYear() - startYear;
    let monthDiff = endDate.getMonth() - startDate.getMonth();
    let dayDiff = endDate.getDate() - startDate.getDate();

    if (monthDiff < 0) {
        yearDiff--;
        monthDiff += 12;
    }

    if (dayDiff < 0) {
        if (monthDiff > 0) {
            monthDiff--;
        } else {
            yearDiff--;
            monthDiff = 11;
        }
        dayDiff += daysInMonth[startDate.getMonth()];
    }

    return {
        years: yearDiff,
        months: monthDiff,
        days: dayDiff,
    };
}

/*
Loading
*/

function loadingShow() {
    setTimeout(function () {
        $("#offcanvasBottom").addClass("show");
        $(
            '<div class="offcanvas-backdrop fade show" id="offcanvas-loading-page"></div>'
        ).appendTo(document.body);
    }, 150);
}

function loadingHide() {
    setTimeout(function () {
        $("#offcanvasBottom").removeClass("show");
        // $("#offcanvas-loading-page").remove();
        $(".offcanvas-backdrop").remove();
    }, 150);
}

/*
Alert
*/

function alertInfo(arg) {
    /* DOCUMENTATION / CONFIGURATION : 
  title: [title]
  desc: [deskripsi/text]
  icon: success/error/warning/info/question
  */
    Swal.fire({
        title: arg.title,
        text: arg.desc,
        icon: arg.icon,
        customClass: "swal-wide",
        showCancelButton: false,
        confirmButtonColor: "#3376B8",
        confirmButtonText: "Ok",
    });
}

function alertConfirm(arg) {
    /* DOCUMENTATION / CONFIGURATION : 
  title: [title]
  desc: [deskripsi/text]
  icon: success/error/warning/info/question
  buttonConfirm [title button confirm default 'Hapus']
  uriConfirm [url ajax]
  postConfirm: [array] [data post yang dikirimkan]
  uriRedirect: [url redirect after ajax]
  */

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    var postConfirm = {};
    postConfirm = arg.postConfirm;
    postConfirm["_is_ajax"] = true;
    postConfirm["_token"] = _token;
    if (arg.uriRedirect != "") {
        postConfirm["uriRedirect"] = arg.uriRedirect;
    }

    Swal.fire({
        title: arg.title,
        text: arg.desc,
        icon: arg.icon,
        customClass: "swal-wide",
        showCancelButton: true,
        cancelButtonColor: "#858F9B",
        cancelButtonText: "Batal",
        confirmButtonColor: "#3376B8",
        confirmButtonText: arg.buttonConfirm,
    }).then((result) => {
        if (result.isConfirmed) {
            loadingShow();
            $.ajax({
                type: "POST",
                url: arg.uriConfirm + "?n=" + urlParams.get("n"),
                dataType: "json",
                data: postConfirm,
                success: function (res) {
                    if (res == null) {
                        loadingHide();
                        _toast("error", "Terjadi kesalahan sistem");
                    } else {
                        var message = res.message;
                        if (res.data !== null) {
                            if (res.data != "") {
                                if (
                                    typeof res.data === "object" ||
                                    Array.isArray(res.data)
                                ) {
                                    message = res.message;
                                } else {
                                    message = res.message + "<br>" + res.data;
                                }
                            }
                        }
                        if (res.status) {
                            _toast("success", message);
                            if ("uri" in res) {
                                _page(res.uri);
                            }
                        } else {
                            loadingHide();
                            _toast("error", message);
                        }
                    }
                },
                error: function (textStatus, errorThrown) {
                    loadingHide();
                    _toast(
                        "error",
                        "Terjadi kesalahan sistem <br> Error : " + errorThrown
                    );
                },
            });
        }
    });
}

function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf("?") !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, "$1" + key + "=" + value + "$2");
    } else {
        return uri + separator + key + "=" + value;
    }
}

function ifNull(value) {
    if (value === undefined || value === null || value === "null") {
        return "";
    } else {
        return value;
    }
}

function emptyToZero(value) {
    if (
        value === "" ||
        value === undefined ||
        value === null ||
        value === "null"
    ) {
        return 0;
    } else {
        return value;
    }
}

function titleCase(str) {
    if (str == null) {
        return "";
    } else {
        var splitStr = str.toLowerCase().split(" ");
        for (var i = 0; i < splitStr.length; i++) {
            splitStr[i] =
                splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
        }
        return splitStr.join(" ");
    }
}

function pad(num, size) {
    num = num.toString();
    while (num.length < size) num = "0" + num;
    return num;
}

function currentDateTime() {
    var date = new Date();
    var tahun = date.getFullYear();
    var bulan = date.getMonth();
    var tanggal = date.getDate();
    var hari = date.getDay();
    var jam = date.getHours();
    var menit = date.getMinutes();
    var detik = date.getSeconds();

    var tampilTanggal = pad(tanggal, 2) + "-" + pad(bulan, 2) + "-" + tahun;
    var tampilWaktu = pad(jam, 2) + ":" + pad(menit, 2) + ":" + pad(detik, 2);

    return tampilTanggal + " " + tampilWaktu;
}

function toUpper(val) {
    if (val !== null) {
        val = val.toUpperCase();
    }
    return val;
}

function eklaim_status(value) {
    let res;
    switch (value) {
        case "0":
            res = "Sudah Dikirim";
            break;
        case "1":
            res = "Proses Grouping";
            break;
        case "2":
            res = "Finalisasi";
            break;
        case "3":
            res = "DC Kemkes";
            break;
        default:
            res = "Belum Dikirim";
            break;
    }
    return res;
}

$(window).scroll(function () {
    if ($(window).width() > 769) {
        var sticky = $(".sticky-custom"),
            scroll = $(window).scrollTop();

        if (scroll >= 75) sticky.addClass("fixed-sticky-custom");
        else sticky.removeClass("fixed-sticky-custom");
    }
});

function isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function getArrayIndexForKey(arr, key, val) {
    for (var i = 0; i < arr.length; i++) {
        if (arr[i][key] == val) return i;
    }
    return -1;
}

function get_bulan(bln) {
    switch (bln) {
        case "01":
            return "Januari";
            break;
        case "02":
            return "Februari";
            break;
        case "03":
            return "Maret";
            break;
        case "04":
            return "April";
            break;
        case "05":
            return "Mei";
            break;
        case "06":
            return "Juni";
            break;
        case "07":
            return "Juli";
            break;
        case "08":
            return "Agustus";
            break;
        case "09":
            return "September";
            break;
        case "10":
            return "Oktober";
            break;
        case "11":
            return "November";
            break;
        case "12":
            return "Desember";
            break;
    }
}

function number_to_time(val) {
    switch (val) {
        case 1:
            return "01:00";
            break;
        case 2:
            return "02:00";
            break;
        case 3:
            return "03:00";
            break;
        case 4:
            return "04:00";
            break;
        case 5:
            return "05:00";
            break;
        case 6:
            return "06:00";
            break;
        case 7:
            return "07:00";
            break;
        case 8:
            return "08:00";
            break;
        case 9:
            return "09:00";
            break;
        case 10:
            return "10:00";
            break;
        case 11:
            return "11:00";
            break;
        case 12:
            return "12:00";
            break;
        case 13:
            return "13:00";
            break;
        case 14:
            return "14:00";
            break;
        case 15:
            return "15:00";
            break;
        case 16:
            return "16:00";
            break;
        case 17:
            return "17:00";
            break;
        case 18:
            return "18:00";
            break;
        case 19:
            return "19:00";
            break;
        case 20:
            return "20:00";
            break;
        case 21:
            return "21:00";
            break;
        case 22:
            return "22:00";
            break;
        case 23:
            return "23:00";
            break;
        case 24:
            return "24:00";
            break;
    }
}

function deleteDatetimepicker(name) {
    $('input[name="' + name + '"]').val("");
}

function deleteSelectAjax(name) {
    var ppaId = {
        id: "",
        text: "",
    };
    var ppaIdOption = new Option(ppaId.text, ppaId.id, false, false);
    $("select[name='" + name + "']")
        .append(ppaIdOption)
        .trigger("change");
    $("select[name='" + name + "']")
        .val(ppaId.id)
        .trigger("change");
}

function replaceUpperComma(text) {
    // return text.replace('&#039;','');
    return text.replace("'", "");
}

function dateDayname(id) {
    switch (id) {
        case 0:
            return "Minggu";
        case 1:
            return "Senin";
        case 2:
            return "Selasa";
        case 3:
            return "Rabu";
        case 4:
            return "Kamis";
        case 5:
            return "Jumat";
        case 6:
            return "Sabtu";
        default:
            return "-";
    }
}
