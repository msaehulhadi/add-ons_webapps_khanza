<?php
/**
 * installation.php — Web Installer mapping_satu_sehat
 * Dibatasi HANYA untuk Super Admin.
 */
require_once 'conf.php';
require_once 'auth_check.php';
require_login();

// Hanya Super Admin yang boleh akses installer
if (empty($_SESSION['is_admin'])) {
    die("<div style='padding:20px; text-align:center; font-family:sans-serif;'>
            <h2 style='color:#ef4444;'>Akses Ditolak</h2>
            <p>Hanya Super Admin yang diizinkan mengakses Database Installer.</p>
            <a href='index.php'>Kembali ke Dashboard</a>
         </div>");
}

$action = $_GET['action'] ?? '';

// =========================================================
// ACTION: RUN IMPORT
// =========================================================
if ($action === 'run_import') {
    // Karena file kfa dan loinc ukurannya cukup besar, matikan time limit & memory limit
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    header('Content-Type: application/json');

    $step = $_GET['step'] ?? '';
    $sql_dir = __DIR__ . DIRECTORY_SEPARATOR . 'tambahan_table' . DIRECTORY_SEPARATOR;

    try {
        if ($step === 'form') {
            $sql = file_get_contents($sql_dir . 'satu_sehat_ref_form.sql');
            $pdo->exec($sql);
            echo json_encode(['status' => 'success', 'message' => 'satu_sehat_ref_form berhasil!']);
            exit;
        }
        if ($step === 'route') {
            $sql = file_get_contents($sql_dir . 'satu_sehat_ref_route.sql');
            $pdo->exec($sql);
            echo json_encode(['status' => 'success', 'message' => 'satu_sehat_ref_route berhasil!']);
            exit;
        }
        if ($step === 'snomed') {
            $sql = file_get_contents($sql_dir . 'satu_sehat_ref_snomed.sql');
            $pdo->exec($sql);
            echo json_encode(['status' => 'success', 'message' => 'satu_sehat_ref_snomed berhasil!']);
            exit;
        }
        if ($step === 'kfa') {
            // File KFA sangat besar. Kita eksekusi string SQL-nya sekaligus karena biasanya MySQL mampu.
            $sql = file_get_contents($sql_dir . 'satu_sehat_ref_kfa.sql');
            $pdo->exec($sql);
            echo json_encode(['status' => 'success', 'message' => 'satu_sehat_ref_kfa berhasil diimpor!']);
            exit;
        }
        if ($step === 'loinc') {
            // File LOINC juga sangat besar.
            $sql = file_get_contents($sql_dir . 'satu_sehat_ref_loinc.sql');
            $pdo->exec($sql);
            echo json_encode(['status' => 'success', 'message' => 'satu_sehat_ref_loinc berhasil diimpor!']);
            exit;
        }
        if ($step === 'numerator') {
            $sql = file_get_contents($sql_dir . 'satu_sehat_ref_numerator.sql');
            $pdo->exec($sql);
            echo json_encode(['status' => 'success', 'message' => 'satu_sehat_ref_numerator berhasil!']);
            exit;
        }
        if ($step === 'denominator') {
            $sql = file_get_contents($sql_dir . 'satu_sehat_ref_denominator.sql');
            $pdo->exec($sql);
            echo json_encode(['status' => 'success', 'message' => 'satu_sehat_ref_denominator berhasil!']);
            exit;
        }
        if ($step === 'tables') {
            // Create target mapping tables
            $tables = [
                "CREATE TABLE IF NOT EXISTS `satu_sehat_mapping_obat` (
                    `kode_brng` varchar(15) NOT NULL, `obat_code` varchar(30) DEFAULT NULL, `obat_system` varchar(100) NOT NULL DEFAULT 'http://sys-ids.kemkes.go.id/kfa', `obat_display` varchar(200) DEFAULT NULL, `form_code` varchar(50) DEFAULT NULL, `form_system` varchar(100) DEFAULT NULL, `form_display` varchar(100) DEFAULT NULL, `route_code` varchar(50) DEFAULT NULL, `route_system` varchar(100) DEFAULT NULL, `route_display` varchar(100) DEFAULT NULL, `denominator_code` varchar(20) DEFAULT NULL, `denominator_system` varchar(100) DEFAULT NULL, PRIMARY KEY (`kode_brng`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1",

                "CREATE TABLE IF NOT EXISTS `satu_sehat_mapping_lab` (
                    `id_template` int(11) NOT NULL, `code` varchar(50) DEFAULT NULL, `system` varchar(100) DEFAULT 'http://loinc.org', `display` varchar(255) DEFAULT NULL, `sampel_code` varchar(50) DEFAULT NULL, `sampel_system` varchar(100) DEFAULT 'http://snomed.info/sct', `sampel_display` varchar(255) DEFAULT NULL, PRIMARY KEY (`id_template`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1",

                "CREATE TABLE IF NOT EXISTS `satu_sehat_mapping_radiologi` (
                    `kd_jenis_prw` varchar(15) NOT NULL, `code` varchar(15) DEFAULT NULL, `system` varchar(100) NOT NULL DEFAULT 'http://loinc.org', `display` varchar(80) DEFAULT NULL, `sampel_code` varchar(15) NOT NULL DEFAULT '', `sampel_system` varchar(100) NOT NULL DEFAULT 'http://snomed.info/sct', `sampel_display` varchar(80) NOT NULL DEFAULT '', PRIMARY KEY (`kd_jenis_prw`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1",

                "CREATE TABLE IF NOT EXISTS `satu_sehat_mapping_vaksin` (
                    `kode_brng` varchar(15) NOT NULL, `vaksin_code` varchar(15) DEFAULT NULL, `vaksin_system` varchar(100) NOT NULL DEFAULT 'http://sys-ids.kemkes.go.id/kfa', `vaksin_display` varchar(80) DEFAULT NULL, `route_code` varchar(30) DEFAULT NULL, `route_system` varchar(100) DEFAULT NULL, `route_display` varchar(80) DEFAULT NULL, `dose_quantity_code` varchar(15) DEFAULT NULL, `dose_quantity_system` varchar(80) DEFAULT NULL, `dose_quantity_unit` varchar(15) DEFAULT NULL, PRIMARY KEY (`kode_brng`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1"
            ];
            foreach ($tables as $t) {
                $pdo->exec($t);
            }
            echo json_encode(['status' => 'success', 'message' => 'Tabel mapping utama berhasil dibuat!']);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Langkah tidak dikenali.']);
        exit;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Ignore "table already exists" error or standard warning if any.
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit;
    }
}

// Cek status database saat ini
$missing_tables = [];
$check_tables = [
    'satu_sehat_ref_form', 'satu_sehat_ref_route', 'satu_sehat_ref_kfa', 'satu_sehat_ref_loinc', 'satu_sehat_ref_snomed',
    'satu_sehat_ref_numerator', 'satu_sehat_ref_denominator',
    'satu_sehat_mapping_obat', 'satu_sehat_mapping_lab', 'satu_sehat_mapping_radiologi', 'satu_sehat_mapping_vaksin'
];

foreach ($check_tables as $tb) {
    try {
        $res = $pdo->query("SHOW TABLES LIKE '$tb'");
        if ($res->rowCount() === 0) {
            $missing_tables[] = $tb;
        }
    } catch (Exception $e) {
        $missing_tables[] = $tb;
    }
}
$is_safe = (count($missing_tables) === 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Installer — <?= htmlspecialchars($APP_INSTANSI, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="shortcut icon" href="logo.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .hero { background: linear-gradient(135deg, #047857, #10b981); color: white; padding: 2.5rem 0; margin-bottom: 2rem; border-radius: 0 0 24px 24px; box-shadow: 0 10px 30px rgba(16,185,129,0.2); }
        .hero h2 { font-weight: 800; font-size: 1.8rem;}
        .nav-back { color: #a7f3d0; text-decoration: none; font-size: 0.9rem; font-weight: 500; display: inline-block; margin-bottom: 1rem; transition: color 0.2s;}
        .nav-back:hover { color: #fff; }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .step-box { padding: 1rem 1.5rem; border-radius: 12px; background: #f1f5f9; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; }
        .step-box.active { background: #eff6ff; border-color: #bfdbfe; }
        .step-box.success { background: #f0fdf4; border-color: #bbf7d0; }
        .step-box.error { background: #fef2f2; border-color: #fecaca; }
        
        .status-icon { font-size: 1.25rem; }
        .status-icon.success { color: #22c55e; }
        .status-icon.error { color: #ef4444; }
        
        .footer-credit { text-align: center; padding: 2rem; font-size: .72rem; color: #94a3b8; cursor: pointer; transition: all 0.2s; }
        .footer-credit:hover { color: #6366f1; background: rgba(99, 102, 241, 0.05); }
        .footer-credit a { color: #059669; text-decoration: none; font-weight: 600; }
        .footer-credit a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="hero">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="index.php" class="nav-back"><i class="fa fa-arrow-left me-2"></i>Kembali ke Dashboard</a>
                <h2 class="mb-1"><i class="fa-solid fa-database me-2 text-emerald-300"></i>Web Installer Database</h2>
                <p class="mb-0 text-emerald-100">Setup tabel referensi & mapping <?= htmlspecialchars($APP_INSTANSI, ENT_QUOTES, 'UTF-8') ?> (Khusus Super Admin)</p>
                
                <?php if (!$is_safe): ?>
                <div class="alert alert-warning border-0 mt-3 mb-0 py-2 small d-flex align-items-center" style="background: rgba(255,255,255,0.2); color: #fff; border-radius: 10px;">
                    <i class="fa fa-triangle-exclamation me-2"></i>
                    Status: Beberapa tabel referensi belum terdeteksi. Silakan jalankan instalasi.
                </div>
                <?php endif; ?>
            </div>
            <div class="text-end d-none d-md-block">
                <i class="fa fa-shield-halved fa-2x text-emerald-200"></i>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4">
                <?php if ($is_safe): ?>
                    <div class="text-center py-4">
                        <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                        <h4 class="fw-bold">Database Sudah Lengkap!</h4>
                        <p class="text-muted">Semua tabel referensi dan mapping yang dibutuhkan sudah terinstall di database Khanza Anda. Tidak ada tindakan lebih lanjut yang diperlukan.</p>
                        <hr>
                        <a href="index.php" class="btn btn-emerald px-4 shadow-sm" style="background:#10b981; color:white; border-radius:20px; font-weight:600;">Kembali beraktivitas</a>
                    </div>
                <?php else: ?>
                    <h5 class="fw-bold mb-3">Beberapa Tabel Belum Ditemukan:</h5>
                    <div class="mb-4">
                        <?php foreach($missing_tables as $mt): ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle me-1 mb-1 px-3 py-2"><?= $mt ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    <p class="text-muted small mb-4">
                        <i class="fa fa-info-circle text-primary me-1"></i> Klik tombol <strong>Mulai Instalasi</strong> di bawah. Script akan mengimpor file SQL (KFA, LOINC, SNOMED) dan membuat tabel target secara berurutan. Harap biarkan tab ini terbuka sampai proses selesai (terutama saat impor KFA yang cukup besar).
                    </p>

                    <!-- Indikator Proses -->
                    <div id="install-steps">
                        <div class="step-box" id="step-form" data-step="form">
                            <div class="fw-semibold text-secondary">1. Import Tabel: satu_sehat_ref_form.sql</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                        <div class="step-box" id="step-route" data-step="route">
                            <div class="fw-semibold text-secondary">2. Import Tabel: satu_sehat_ref_route.sql</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                        <div class="step-box" id="step-snomed" data-step="snomed">
                            <div class="fw-semibold text-secondary">3. Import Tabel: satu_sehat_ref_snomed.sql</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                        <div class="step-box" id="step-loinc" data-step="loinc">
                            <div class="fw-semibold text-secondary">4. Import Tabel: satu_sehat_ref_loinc.sql (Besar)</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                        <div class="step-box" id="step-kfa" data-step="kfa">
                            <div class="fw-semibold text-secondary">5. Import Tabel: satu_sehat_ref_kfa.sql (Besar)</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                        <div class="step-box" id="step-tables" data-step="tables">
                            <div class="fw-semibold text-secondary">6. Create: Tabel Mapping (Obat, Lab, Rad, Vaksin)</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                        <div class="step-box" id="step-numerator" data-step="numerator">
                            <div class="fw-semibold text-secondary">7. Import Tabel: satu_sehat_ref_numerator.sql (Satuan UCUM)</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                        <div class="step-box" id="step-denominator" data-step="denominator">
                            <div class="fw-semibold text-secondary">8. Import Tabel: satu_sehat_ref_denominator.sql (HL7 DrugForm)</div>
                            <i class="fa fa-circle status-icon pending"></i>
                        </div>
                    </div>

                    <div class="text-center mt-4 pt-3 border-top">
                        <button class="btn px-5 py-2 shadow" id="btnMulai" style="background: linear-gradient(135deg, #047857, #10b981); color: white; border-radius: 30px; font-weight: 700;">
                            <i class="fa fa-play me-2"></i> Mulai Instalasi
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const queue = ['form', 'route', 'snomed', 'loinc', 'kfa', 'tables', 'numerator', 'denominator'];
    
    function processStep(index) {
        if (index >= queue.length) {
            $('#btnMulai').html('<i class="fa fa-check"></i> Selesai!').removeClass('btn-primary').addClass('btn-success');
            Swal.fire('Instalasi Selesai!', 'Database mapping Satu Sehat sudah siap digunakan.', 'success').then(() => {
                window.location.reload();
            });
            return;
        }

        const stepId = queue[index];
        const box = $('#step-' + stepId);
        const icon = box.find('.status-icon');
        const text = box.find('div');

        // Marking as running
        box.addClass('active');
        text.removeClass('text-secondary').addClass('text-dark');
        icon.removeClass('pending fa-circle').addClass('running fa-spinner fa-spin');

        // AJAX Request
        $.ajax({
            url: 'installation.php?action=run_import&step=' + stepId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                icon.removeClass('running fa-spinner fa-spin');
                box.removeClass('active');
                if (res.status === 'success') {
                    // Success
                    box.addClass('success');
                    text.addClass('text-success');
                    icon.addClass('success fa-check-circle');
                    
                    // Lanjut ke antrean berikutnya
                    processStep(index + 1);
                } else {
                    // Error tapi kita abaikan dan lanjut aja krn mungkin table already exist.
                    box.addClass('error');
                    text.addClass('text-danger');
                    icon.addClass('error fa-exclamation-triangle');
                    console.log("Warning on " + stepId + ": " + res.message);
                    
                    // Tetap lanjut
                    processStep(index + 1);
                }
            },
            error: function(xhr, status, error) {
                icon.removeClass('running fa-spinner fa-spin');
                box.removeClass('active');
                
                // Biasanya error kalo PHP timeout atau memory exhaust
                box.addClass('error');
                text.addClass('text-danger');
                icon.addClass('error fa-times-circle');
                
                Swal.fire('Error!', 'Proses terhenti di tahap: ' + stepId + '. Server mungkin timeout.', 'error');
                $('#btnMulai').html('Coba Lagi').prop('disabled', false);
            }
        });
    }

    $('#btnMulai').click(function() {
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Menginstall...');
        
        // Reset the UI before starting
        $('.step-box').removeClass('active success error');
        $('.fw-semibold').removeClass('text-dark text-success text-danger').addClass('text-secondary');
        $('.status-icon').removeClass('running success error fa-spinner fa-spin fa-check-circle fa-times-circle fa-exclamation-triangle')
                         .addClass('pending fa-circle');

        processStep(0); // Mulai dari antrean pertama
    });
});
</script>

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
        <div class="modal-content border-0 shadow-lg text-dark" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4 px-4">
                <div class="mb-3">
                    <img src="https://raw.githubusercontent.com/ichsanleonhart/add-ons_webapps_khanza/main/qris-ichsan.png" class="img-fluid rounded-3 shadow-sm" style="max-width: 280px;" alt="QRIS Donasi">
                </div>
                <h5 class="fw-bold text-primary mb-3">Apresiasi & Dukungan</h5>
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
</body>
</html>
