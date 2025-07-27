<script>
    // =========================
    // Helper Functions
    // =========================

    function _toast(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(type === 'error' ? 'Error: ' + message : message);
        }
    }

    function _modalHide() {
        if ($('.modal.show').length) {
            $('.modal.show').modal('hide');
        }
    }

    // =========================
    // Modal Functions
    // =========================

    function openScanModal(orderKerjaId, nParam) {
        let url = _base_url + 'ipsrs/teknisitugas/form_scan_modal/' + orderKerjaId;
        if (nParam) url += '?n=' + nParam;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#scan-modal').remove();
                $('body').append(
                    `<div class="modal fade" id="scan-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Scan Barcode Aset</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                ${response}
                            </div>
                        </div>
                    </div>`
                );
                $('#scan-modal').modal('show');
            },
            error: function(xhr) {
                console.error('Error loading modal:', xhr);
                alert('Gagal memuat form scan barcode. Silakan coba lagi.');
            }
        });
    }

    function showTolakModal(penugasanId) {
        const n_param = new URLSearchParams(window.location.search).get('n');
        const url = _base_url + 'ipsrs/teknisitugas/form_tolak_modal/' + penugasanId;
        _modal(null, {
            uri: url + (n_param ? '?n=' + n_param : ''),
            title: 'Form Tolak Tugas',
            size: 'modal-lg',
            buttons: [{
                text: 'Tutup',
                class: 'btn-secondary',
                click: function(modal) { modal.hide(); }
            }]
        });
    }

    // =========================
    // Success & Failure Handlers
    // =========================

    function handleSuccess(message, redirectUrl) {
        _modalHide();
        _toast("success", message);
        const navParameter = new URLSearchParams(window.location.search).get('n');
        setTimeout(() => {
            if (redirectUrl) {
                window.location.href = redirectUrl + (redirectUrl.includes('?') ? '&' : '?') + 'n=' + navParameter;
            } else {
                window.location.reload();
            }
        }, 1000);
    }

    function handleFailure(xhr, btn) {
        if (btn) btn.prop('disabled', false);
        const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
        _toast('error', errorMsg);
    }

    // =========================
    // Document Ready
    // =========================

    $(document).ready(function() {
        // Re-initialize repeater when modals are shown
        $(document).on('shown.bs.modal', function(e) {
            var modal = $(e.target);
            var repeater = modal.find('#sparepart-repeater');
            if (repeater.length > 0 && typeof repeater.data('repeater-init') === 'undefined' && typeof repeater.repeater === 'function') {
                repeater.repeater({
                    initEmpty: false,
                    show: function() {
                        $(this).slideDown();
                        $(this).find('.repeater-select').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: modal
                        });
                    },
                    hide: function(deleteElement) {
                        $(this).slideUp(deleteElement);
                    }
                });
                repeater.data('repeater-init', true);
                repeater.find('.repeater-select').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: modal
                });
            }
        });

        // =========================
        // Button Handlers (Refactored)
        // =========================

        $(document).on('click', '.btn-task-action, .btn-terima-tugas, .btn-ambil-kembali, .btn-tolak-tugas', function(e) {
            e.preventDefault();
            const $btn = $(this);

            // General task action
            if ($btn.hasClass('btn-task-action')) {
                const url = $btn.data('url');
                const id = $btn.data('id');
                if (!url || !id) {
                    _toast('error', 'Data untuk aksi tidak lengkap.');
                    return;
                }
                $btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Memproses...');
                const navParameter = new URLSearchParams(window.location.search).get('n');
                const postData = {
                    penugasan_id: id,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    n: navParameter
                };
                $.post(url, postData, function(res) {
                    if (res.code === '02') {
                        handleSuccess(res.message, res.redirect_url);
                    } else {
                        _toast('error', res.message || 'Gagal memproses permintaan.');
                        $btn.prop('disabled', false).html($btn.data('original-text') || $btn.text());
                    }
                }, 'json').fail(xhr => handleFailure(xhr, $btn));
                return;
            }

            // Accept/Take Back Task
            if ($btn.hasClass('btn-terima-tugas') || $btn.hasClass('btn-ambil-kembali')) {
                const url = $btn.data('url');
                const penugasanId = $btn.data('penugasan-id');
                const n_param = new URLSearchParams(window.location.search).get('n') || '';
                let actionText = $btn.hasClass('btn-terima-tugas') ? 'Terima Tugas ini?' : 'Ambil Kembali Tugas ini?';

                Swal.fire({
                    title: 'Konfirmasi',
                    text: actionText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: _token,
                                penugasan_id: penugasanId,
                                n: n_param
                            },
                            dataType: 'json',
                            success: function(res) {
                                Swal.fire({
                                    icon: res.code === '02' ? 'success' : 'error',
                                    title: res.code === '02' ? 'Berhasil' : 'Gagal',
                                    text: res.message || (res.code === '02' ? 'Data berhasil diubah' : 'Terjadi kesalahan')
                                }).then(() => {
                                    if (res.code === '02' && res.redirect_url) {
                                        window.location.href = res.redirect_url;
                                    } else {
                                        $btn.prop('disabled', false).html($btn.hasClass('btn-terima-tugas') ?
                                            '<i class="fas fa-check"></i> Terima Tugas' :
                                            '<i class="fas fa-undo"></i> Ambil Kembali Tugas'
                                        );
                                    }
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                                $btn.prop('disabled', false).html($btn.hasClass('btn-terima-tugas') ?
                                    '<i class="fas fa-check"></i> Terima Tugas' :
                                    '<i class="fas fa-undo"></i> Ambil Kembali Tugas'
                                );
                            }
                        });
                    }
                });
                return;
            }

            // Reject Task
            if ($btn.hasClass('btn-tolak-tugas')) {
                const penugasanId = $btn.data('penugasan-id');
                const n = $btn.data('n') || '';
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Tolak tugas ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const modalUrl = _base_url + 'ipsrs/teknisitugas/form_tolak_modal/' + penugasanId + '?n=' + n + '&_is_ajax=true';
                        $.ajax({
                            url: modalUrl,
                            type: 'GET',
                            headers: { 'X-CSRF-TOKEN': _token },
                            success: function(response) {
                                $('#my-modal-1').modal('hide');
                                $('#tolak-modal').remove();
                                $('body').append(
                                    `<div class="modal fade" id="tolak-modal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-md" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tolak Tugas</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                ${response}
                                            </div>
                                        </div>
                                    </div>`
                                );
                                $('#tolak-modal').modal('show');
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'Gagal memuat form tolak tugas', 'error');
                            }
                        });
                    }
                });
                return;
            }
        });

        // AJAX form submit (Form Tolak Tugas)
        $(document).on('submit', '#form-ajax', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type=submit]');
            $btn.prop('disabled', true);
            const navParameter = new URLSearchParams(window.location.search).get('n');
            let formData = $form.serialize() + '&n=' + navParameter;
            $.post($form.attr('action'), formData, function(res) {
                Swal.fire({
                    icon: res.code === '02' ? 'success' : 'error',
                    title: res.code === '02' ? 'Berhasil' : 'Gagal',
                    text: res.message || (res.code === '02' ? '' : 'Gagal memproses permintaan.')
                }).then(() => {
                    if (res.code === '02' && res.redirect_url) {
                        window.location.href = res.redirect_url;
                    } else {
                        window.location.reload();
                    }
                });
                if (res.code !== '02') $btn.prop('disabled', false);
            }, 'json').fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan pada server.'
                });
                $btn.prop('disabled', false);
            });
        });
    });

    // Cancel Accept Task with SweetAlert2
    function batalTerima(penugasanId) {
        const n_param = new URLSearchParams(window.location.search).get('n') || '';
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Batalkan penerimaan tugas ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${_base_url}ipsrs/teknisitugas/batal_terima`,
                    type: 'POST',
                    data: {
                        _token: _token,
                        penugasan_id: penugasanId,
                        n: n_param
                    },
                    dataType: 'json',
                    success: function(res) {
                        Swal.fire({
                            icon: res.code === '02' ? 'success' : 'error',
                            title: res.code === '02' ? 'Berhasil' : 'Gagal',
                            text: res.message
                        }).then(() => {
                            if (res.code === '02' && res.redirect_url) {
                                window.location.href = res.redirect_url;
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }
</script>
