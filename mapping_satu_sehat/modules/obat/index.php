<?php
/**
 * modules/obat/index.php — Halaman Mapping Obat (KFA Satu Sehat)
 * Refactored dari mapping_obat_satusehat/index.php
 * Menggunakan DataTables server-side pagination (FIX: tidak ada LIMIT 100 lagi)
 */
require_once '../../conf.php';
require_once '../../auth_check.php';
check_module_access('satu_sehat_mapping_obat'); // RBAC Guard
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapping Obat — <?= htmlspecialchars($APP_INSTANSI, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="shortcut icon" href="../../logo.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .navbar-brand { font-weight: 700; }
        .select2-container { z-index: 9999; }
        .nav-back { color: #6366f1; font-weight: 500; text-decoration: none; font-size: .875rem; }
        .nav-back:hover { color: #4338ca; }
        .page-header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; border-radius: 16px; padding: 1.5rem 2rem; margin-bottom: 1.5rem; }
        .page-header h4 { font-weight: 700; margin: 0; }
        .page-header p { margin: 0.25rem 0 0; opacity: 0.8; font-size: .875rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .footer-credit { text-align: center; padding: 1.5rem; font-size: .72rem; color: #94a3b8; cursor: pointer; transition: all 0.2s; }
        .footer-credit:hover { color: #6366f1; background: rgba(99, 102, 241, 0.05); }
        .footer-credit a { color: #6d28d9; text-decoration: none; font-weight: 600; }
        .footer-credit a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-light bg-white border-bottom px-3 py-2 mb-4">
    <a class="nav-back" href="../../index.php">
        <i class="fa fa-arrow-left me-2"></i>Dashboard
    </a>
    <span class="navbar-brand text-primary mb-0" style="font-size:1rem">
        <i class="fa-solid fa-pills me-2"></i> Mapping Obat — <?= htmlspecialchars($APP_INSTANSI, ENT_QUOTES, 'UTF-8') ?>
    </span>
    <div>
        <span class="text-muted small me-3"><i class="fa fa-user me-1"></i><?= htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></span>
        <a href="../../logout.php" class="text-danger small text-decoration-none">
            <i class="fa fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="page-header">
        <h4><i class="fa-solid fa-pills me-2"></i> Mapping Obat ke KFA Satu Sehat</h4>
        <p>Klik tombol Mapping pada baris obat untuk menetapkan kode KFA, rute, dan satuan.</p>
    </div>

    <!-- Panel Pencarian Server-Side -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-primary small">Cari Nama Obat (Server)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="keyword_obat" class="form-control"
                               placeholder="Ketik nama obat lalu tekan Enter atau klik Tampilkan...">
                        <button class="btn btn-primary px-4" id="btnCariServer">
                            <i class="fa fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                    <div class="form-text">Kosongkan untuk menampilkan <strong>semua data</strong> (server-side paging). Gunakan filter DataTables untuk menyaring di halaman ini.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelObat" class="table table-striped table-hover table-bordered w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">Kode RS</th>
                            <th width="30%">Nama Obat (RS)</th>
                            <th width="40%">Detail Mapping (KFA, Rute, Satuan)</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mapping Obat -->
<div class="modal fade" id="modalMap" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Form Mapping Obat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="m_kode_brng">
                <div class="alert alert-light border d-flex align-items-center mb-3">
                    <i class="fa fa-capsules fa-2x me-3 text-warning"></i>
                    <div>
                        <div class="fw-bold fs-5" id="m_nama_brng_label">Nama Obat</div>
                        <small class="text-muted" id="m_kode_brng_label">Kode RS</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-primary">1. Kode KFA (Kamus Farmasi & Alkes)</label>
                    <div class="input-group">
                        <select class="form-select" id="select_kfa" style="width:85%"></select>
                        <a href="https://kfa-browser.kemkes.go.id" target="_blank" class="btn btn-outline-secondary" title="Buka KFA Browser">
                            <i class="fa fa-external-link-alt"></i>
                        </a>
                    </div>
                    <input type="hidden" id="kfa_display_hidden">
                    <input type="text" class="form-control mt-2" id="kfa_display_manual" placeholder="Atau ketik nama KFA manual jika tidak ditemukan...">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">2. Bentuk Sediaan (Form)</label>
                        <select class="form-select select2-static" id="select_form" required>
                            <option value="">-- Pilih Bentuk --</option>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM satu_sehat_ref_form ORDER BY display ASC");
                            while ($f = $stmt->fetch()) {
                                echo "<option value='" . htmlspecialchars($f['code'].'|'.$f['display'], ENT_QUOTES, 'UTF-8') . "'>"
                                   . htmlspecialchars($f['display'], ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($f['code'], ENT_QUOTES, 'UTF-8') . ")</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-danger">3. Rute Pemberian (ATC)</label>
                        <select class="form-select select2-static" id="select_route" required>
                            <option value="">-- Pilih Rute --</option>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM satu_sehat_ref_route");
                            while ($r = $stmt->fetch()) {
                                echo "<option value='" . htmlspecialchars($r['code'].'|'.$r['display'], ENT_QUOTES, 'UTF-8') . "'>"
                                   . htmlspecialchars($r['display'], ENT_QUOTES, 'UTF-8') . " (ATC: " . htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') . ")</option>";
                            }
                            ?>
                        </select>
                        <div class="form-text text-danger small">System: http://www.whocc.no/atc</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">4. Satuan Numerator (Kekuatan)</label>
                        <select class="form-select select2-tags" id="select_numerator">
                            <option value="">-- Pilih Unit --</option>
                            <option value="mg">mg (Miligram)</option>
                            <option value="mL">mL (Mililiter)</option>
                            <option value="g">g (Gram)</option>
                            <option value="ug">ug (Mikrogram)</option>
                            <option value="[IU]">[IU] (International Unit)</option>
                            <option value="%">% (Persen)</option>
                        </select>
                        <div id="num_badge" class="badge bg-secondary mt-1">System: Auto</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">5. Satuan Denominator (Penyajian)</label>
                        <select class="form-select select2-tags" id="select_denominator">
                            <option value="">-- Pilih Unit --</option>
                            <option value="TAB">TAB (Tablet)</option>
                            <option value="CAP">CAP (Kapsul)</option>
                            <option value="PCS">PCS (Pcs)</option>
                            <option value="BOTOL">BOTOL (Botol)</option>
                            <option value="VIAL">VIAL (Vial)</option>
                            <option value="AMPUL">AMPUL (Ampul)</option>
                            <option value="SACHET">SACHET (Sachet)</option>
                            <option value="TUBE">TUBE (Tube)</option>
                            <option value="SUPP">SUPP (Supp)</option>
                            <option value="mL">mL (khusus cair)</option>
                        </select>
                        <div id="den_badge" class="badge bg-secondary mt-1">System: Auto</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanObat">
                    <i class="fa fa-save me-1"></i> Simpan Mapping
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Footer copyright (Anti-Tampering — JANGAN DIHAPUS) -->
<div class="footer-credit" id="footer-credit-block" onclick="new bootstrap.Modal(document.getElementById('modalSaweria')).show();">
    &copy; <a href="https://saweria.co/ichsanleonhart" target="_blank" onclick="event.stopPropagation();">Ichsan Leonhart</a> &nbsp;·&nbsp;
    <a href="https://wa.me/6285726123777" target="_blank" onclick="event.stopPropagation();">6285726123777</a> &nbsp;·&nbsp;
    <a href="https://t.me/IchsanLeonhart" target="_blank" onclick="event.stopPropagation();">@IchsanLeonhart</a> &nbsp;·&nbsp;
    <a href="https://raw.githubusercontent.com/ichsanleonhart/add-ons_webapps_khanza/main/qris-ichsan.png" target="_blank" onclick="event.stopPropagation();">QRIS Donasi</a>
    — <a href="https://saweria.co/ichsanleonhart" target="_blank" onclick="event.stopPropagation();">saweria.co/ichsanleonhart</a>
</div>

<!-- Modal Saweria (Uneg-uneg Mengemis) -->
<div class="modal fade" id="modalSaweria" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4 px-4">
                <div class="mb-3">
                    <img src="https://raw.githubusercontent.com/ichsanleonhart/add-ons_webapps_khanza/main/qris-ichsan.png" class="img-fluid rounded-3 shadow-sm" style="max-width: 280px;" alt="QRIS Donasi">
                </div>
                <h5 class="fw-bold text-primary mb-3">Apresiasi & Dukungan Donasi</h5>
                <p class="text-muted small px-2 mb-4" style="line-height: 1.6;">
                    Halo rekan-rekan IT dan Super Admin. Terima kasih telah menggunakan aplikasi pemetaan Satu Sehat ini.<br><br>
                    Jika aplikasi ini membantu mempermudah pekerjaan Anda, mohon bantuannya untuk sedikit memberikan apresiasi / "traktiran kopi" agar saya tetap semangat melakukan maintenance dan update fitur lainnya. Berapapun dukungan Anda sangat berarti bagi kelangsungan pengembangan aplikasi ini.<br><br>
                    <strong>Terima kasih banyak atas dukungannya! 🙏</strong>
                </p>
                <div class="d-grid gap-2">
                    <a href="https://saweria.co/ichsanleonhart" target="_blank" class="btn btn-primary py-2 fw-bold" style="background:linear-gradient(135deg, #4f46e5, #7c3aed); border:none;">
                        <i class="fa-solid fa-heart me-2"></i> Dukung via Saweria.co
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const CSRF_TOKEN = '<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>';

$(function() {
    // 1. Init DataTables — Server-Side Pagination (fix bug LIMIT 100)
    var table = $('#tabelObat').DataTable({
        "processing": true,
        "serverSide": true, // TRUE: DataTables mengelola paging/filter di server
        "ajax": {
            "url": "ajax.php?action=load_table",
            "data": function(d) {
                d.keyword = $('#keyword_obat').val(); // Kirim keyword ke server
            }
        },
        "dom": "<'row mb-3'<'col-md-2'l><'col-md-6 text-center'B><'col-md-4'f>>" +
               "<'row'<'col-md-12'tr>>" +
               "<'row'<'col-md-5'i><'col-md-7'p>>",
        "buttons": [
            { extend: 'excelHtml5', text: '<i class="fa fa-file-excel"></i> Export Excel', className: 'btn btn-success btn-sm' }
        ],
        "columns": [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3, className: "text-center", orderable: true },
            { data: 4, className: "text-center", orderable: false }
        ],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        "language": {
            "search": "Filter di halaman ini:",
            "processing": "<i class='fa fa-spinner fa-spin'></i> Memuat...",
            "zeroRecords": "Tidak ada data yang cocok",
            "info": "Menampilkan _START_-_END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(difilter dari _MAX_ total)",
            "lengthMenu": "Tampilkan _MENU_ baris"
        }
    });

    // 2. Event tombol cari server
    $('#btnCariServer').click(function() { table.ajax.reload(); });
    $('#keyword_obat').on('keyup', function(e) {
        if (e.key === 'Enter') table.ajax.reload();
    });

    // 3. Select2 Form & Route
    $('.select2-static').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalMap') });
    $('.select2-tags').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalMap'), tags: true });

    // 4. Select2 KFA (AJAX Search)
    $('#select_kfa').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalMap'),
        placeholder: 'Ketik Kode atau Nama KFA...',
        minimumInputLength: 2,
        ajax: {
            url: 'ajax.php?action=search_kfa',
            dataType: 'json',
            delay: 250,
            processResults: function(data) { return { results: data.results }; },
            cache: true
        }
    }).on('select2:select', function(e) {
        $('#kfa_display_hidden').val(e.params.data.display_name);
        $('#kfa_display_manual').val(e.params.data.display_name);
    });

    // 5. Buka Modal
    $('#tabelObat tbody').on('click', '.btn-map', function() {
        var data = $(this).data('json');
        $('#m_kode_brng').val(data.kode_brng);
        $('#m_nama_brng_label').text(data.nama_brng);
        $('#m_kode_brng_label').text(data.kode_brng);

        $('#select_kfa').val(null).trigger('change');
        if (data.obat_code) {
            var opt = new Option(data.obat_code + ' - ' + data.obat_display, data.obat_code, true, true);
            $('#select_kfa').append(opt).trigger('change');
            $('#kfa_display_hidden').val(data.obat_display);
            $('#kfa_display_manual').val(data.obat_display);
            $('#select_form').val(data.form_code + '|' + data.form_display).trigger('change');
            $("#select_route option").each(function() {
                if ($(this).val().startsWith(data.route_code)) $(this).prop('selected', true).trigger('change');
            });
            $('#select_numerator').val(data.numerator_code).trigger('change');
            $('#select_denominator').val(data.denominator_code).trigger('change');
        } else {
            $('#kfa_display_manual').val('');
            $('#select_numerator').val(null).trigger('change');
            $('#select_denominator').val(null).trigger('change');
        }
        var modal = new bootstrap.Modal(document.getElementById('modalMap'));
        modal.show();
    });

    // 6. Auto-detect unit system (visual)
    function detectSystem(val) {
        var ucum = ['mg', 'ml', 'g', 'ug', 'mcg', 'l', 'iu', '[iu]', '%', 'mmol', 'mol', 'mg/ml', 'mg/g'];
        return ucum.includes((val || '').toLowerCase()) ? {bg:'bg-success',txt:'UCUM'} : {bg:'bg-primary',txt:'DrugForm'};
    }
    $('#select_numerator').on('change', function() {
        var d = detectSystem($(this).val());
        $('#num_badge').removeClass('bg-secondary bg-primary bg-success').addClass(d.bg).text('System: '+d.txt);
    });
    $('#select_denominator').on('change', function() {
        var d = detectSystem($(this).val());
        $('#den_badge').removeClass('bg-secondary bg-primary bg-success').addClass(d.bg).text('System: '+d.txt);
    });

    // 7. Simpan mapping via AJAX (dengan CSRF)
    $('#btnSimpanObat').click(function() {
        var btn = $(this);
        var origHtml = btn.html();
        btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

        $.post('ajax.php?action=save_mapping', {
            csrf_token:        CSRF_TOKEN,
            kode_brng:         $('#m_kode_brng').val(),
            kfa_code:          $('#select_kfa').val() || '',
            kfa_display_hidden: $('#kfa_display_hidden').val(),
            kfa_display_manual: $('#kfa_display_manual').val(),
            form_code:         $('#select_form').val() || '',
            route_code:        $('#select_route').val() || '',
            numerator_code:    $('#select_numerator').val() || '',
            denominator_code:  $('#select_denominator').val() || ''
        }, function(resp) {
            btn.html(origHtml).prop('disabled', false);
            if (resp.status === 'success') {
                // Inline feedback: ubah warna tombol jadi hijau sebentar
                btn.html('<i class="fa fa-check"></i> Tersimpan!').addClass('btn-success').removeClass('btn-primary');
                setTimeout(function() {
                    btn.html(origHtml).removeClass('btn-success').addClass('btn-primary');
                    bootstrap.Modal.getInstance(document.getElementById('modalMap')).hide();
                    table.ajax.reload(null, false);
                }, 1500);
            } else {
                Swal.fire('Gagal!', resp.message, 'error');
            }
        }, 'json').fail(function() {
            btn.html(origHtml).prop('disabled', false);
            Swal.fire('Error!', 'Koneksi server gagal.', 'error');
        });
    });

    // Anti-Tampering
    setInterval(function() {
        var el = document.getElementById('footer-credit-block');
        if (!el) { document.body.innerHTML = ''; return; }
        var html = el.innerHTML;
        var cs = window.getComputedStyle(el);
        var checks = [atob('SWNoc2FuIExlb25oYXJ0'),atob('c2F3ZXJpYS5jby9pY2hzYW5sZW9uaGFydA=='),atob('NjI4NTcyNjEyMzc3Nw=='),atob('QEljaHNhbkxlb25oYXJ0'),atob('aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL2ljaHNhbmxlb25oYXJ0L2FkZC1vbnNfd2ViYXBwc19raGFuemEvbWFpbi9xcmlzLWljaHNhbi5wbmc=')];
        if (cs.display==='none'||cs.visibility==='hidden'||cs.opacity==='0') { document.body.innerHTML=''; return; }
        for(var i=0;i<checks.length;i++) { if(html.indexOf(checks[i])===-1) { document.body.innerHTML=''; return; } }
    }, 3000);
});
</script>
</body>
</html>
