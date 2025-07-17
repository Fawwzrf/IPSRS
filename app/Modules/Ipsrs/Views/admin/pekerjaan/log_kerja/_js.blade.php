<script type="text/javascript">
    $(document).ready(function() {
        // Inisialisasi plugin pada halaman utama (jika ada)
        if ($('#datatable-main').length) {
            // Inisialisasi DataTable
            tabel = $('#datatable-main').DataTable({
                "language": { url: _base_url + 'dist/libs/DataTables/id.json' },
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "ordering": true,
                "order": [ [1, 'desc'] ],
                "ajax": {
                    "url": "<?= $uri . '/ajax_datatables?n=' . request('n') ?>",
                    "type": "POST"
                },
                "deferRender": true,
                "aLengthMenu": _datatableLengthMenu,
                "pageLength": 10,
                "bFilter": false,
                "columns": [
                    { "data": null, "orderable": false, "className": "text-center", 
                      "render": function (data, type, row, meta) { 
                          return meta.row + meta.settings._iDisplayStart + 1; 
                      }
                    },
                    { "data": "order_kerja_id" },
                    { "data": "teknisi_nm" },
                    { 
                        "data": "tgl_selesai", 
                        "render": function(data) { 
                            return data ? toDate(data) : '-'; 
                        }
                    },
                    { 
                        "data": "hasil", 
                        "render": function(data) {
                            if (!data) return '<span class="badge bg-secondary">-</span>';
                            
                            var badgeClass = { 
                                'berhasil': 'bg-success', 
                                'perlu_tindak_lanjut': 'bg-warning', 
                                'tidak_berhasil': 'bg-danger' 
                            };
                            
                            return '<span class="badge ' + (badgeClass[data] || 'bg-secondary') + '">' + 
                                   data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + 
                                   '</span>';
                        }
                    },
                    { 
                        "data": "foto_count", 
                        "className": "text-center",
                        "render": function(data) {
                            return data > 0 ? 
                                '<span class="badge bg-primary">' + data + ' foto</span>' : 
                                '<span class="badge bg-secondary">Tidak ada</span>';
                        }
                    },
                    { 
                        "data": "log_kerja_id", 
                        "orderable": false,
                        "render": function(data, type, row) {
                            return '<button class="btn btn-sm btn-info" onclick="viewDetails(\'' + data + '\')"><i class="fas fa-eye"></i> Lihat Detail</button>';
                        }
                    }
                ]
            });
        }
    });

    // Inisialisasi plugin-plugin untuk modal
    $(document).on('shown.bs.modal', function (e) {
        const modal = $(this);
        
        // Cek apakah modal ini adalah modal log kerja
        if (!modal.find('#form-log-kerja').length) return;
        
        // Inisialisasi plugin AutoNumeric
        modal.find('.autonumeric').autoNumeric('init', {
            aSep: '.',
            aDec: ',',
            mDec: '0'
        });
        
        // Inisialisasi plugin Repeater untuk sparepart
        modal.find('#sparepart-repeater').repeater({
            initEmpty: true,
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
        
        // Inisialisasi Select2 untuk semua select di dalam modal
        modal.find('select').select2({
            theme: 'bootstrap-5',
            dropdownParent: modal
        });
    });

    // Handler untuk form submit
    $(document).off('submit', '#form-log-kerja').on('submit', '#form-log-kerja', function(e) {
        e.preventDefault();

        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const url = form.attr('action');
        const n_param = form.find('input[name="n"]').val();
        const assetId = form.find('input[name="asset_id"]').val();
        
        btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Menyimpan...');
        
        const formData = new FormData(this);
        
        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                const responseData = res.data || res;
                
                if (responseData.code == '01' || responseData.code == '02' || res.status === true) {
                    _toast('success', responseData.message || res.message || 'Data berhasil disimpan.');
                    _modalHide();
                    
                    // Redirect ke halaman yang sesuai
                    redirectAfterSuccess(n_param, assetId, res);
                } else {
                    _toast('error', responseData.message || 'Gagal memproses data.');
                    btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Laporan');
                }
            },
            error: function(xhr) {
                const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? 
                                xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
                _toast('error', errorMsg);
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Laporan');
            }
        });
    });

    // Fungsi untuk menangani redirect setelah simpan berhasil
    function redirectAfterSuccess(n_param, assetId, response) {
        // Prioritas 1: Redirect ke detail aset jika assetId tersedia
        if (assetId && n_param) {
            const detailUrl = new URL(`${window.location.protocol}//${window.location.host}/master/asset/detail/${assetId}`);
            detailUrl.searchParams.set('n', n_param);
            window.location.href = detailUrl.toString();
            return;
        }
        
        // Prioritas 2: Gunakan URI dari server response
        if (response.uri) {
            try {
                if (n_param) {
                    const redirectUrl = new URL(response.uri);
                    redirectUrl.searchParams.set('n', n_param);
                    window.location.href = redirectUrl.toString();
                } else {
                    window.location.href = response.uri;
                }
            } catch (e) {
                console.error("Error saat mengolah URI redirect:", e);
                // Fallback jika URL parsing gagal
                window.location.href = response.uri + (response.uri.includes('?') ? '&' : '?') + 'n=' + n_param;
            }
            return;
        }
        
        // Prioritas 3: Reload halaman dengan parameter 'n' yang sama
        if (n_param) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('n', n_param);
            window.location.href = currentUrl.toString();
        } else {
            window.location.reload();
        }
    }
</script>