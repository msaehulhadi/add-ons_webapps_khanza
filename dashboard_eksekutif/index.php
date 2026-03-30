<?php
/*
 * File: index.php (REVISI V2)
 * - Added: Fetch Nama Instansi dari Database.
 * - Added: Favicon & Logo (mengarah ke core/logo.php).
 * - UI: Layout dipercantik dengan logo di tengah.
 */

// 1. Koneksi Database (Hanya untuk ambil Nama RS)
// Menggunakan require_once agar jika file tidak ada, script berhenti (safety)
// Pastikan path 'config/koneksi.php' sesuai dengan struktur folder Anda.
if (file_exists('config/koneksi.php')) {
    require_once('config/koneksi.php');
}

$nama_instansi = "Rumah Sakit"; // Default fallback jika DB gagal
if (isset($koneksi)) {
    $sql_setting = "SELECT nama_instansi FROM setting LIMIT 1";
    $result_setting = $koneksi->query($sql_setting);
    if ($result_setting && $result_setting->num_rows > 0) {
        $row_setting = $result_setting->fetch_assoc();
        $nama_instansi = htmlspecialchars($row_setting['nama_instansi']);
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?php echo $nama_instansi; ?></title>
    
    <link rel="icon" href="core/logo.php" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f0f2f5; /* Warna background sedikit lebih gelap agar kartu menonjol */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            border: none;
            border-radius: 10px; /* Sudut lebih bulat */
        }
        .login-logo {
            max-width: 80px;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0px 4px 4px rgba(0, 0, 0, 0.1));
        }
        .btn-primary {
            background-color: #0d6efd;
            padding: 10px;
            font-weight: 600;
        }
        .hover-primary:hover {
            color: #0d6efd !important;
        }
        /* Timeline CSS */
        .timeline {
            position: relative;
            padding: 1rem 0;
            margin: 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20px;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 50px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #0d6efd;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #0d6efd;
            z-index: 1;
        }
        .timeline-date {
            font-size: 0.85rem;
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 0.5rem;
            display: block;
        }
        .timeline-content {
            background: #fff;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .timeline-content h5 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0px;
            color: #212529;
        }
        .modal-changelog-body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="card login-card shadow">
    <div class="card-body p-4">
        
        <div class="text-center mb-4">
            <img src="core/logo.php" alt="Logo RS" class="login-logo">
            <h5 class="fw-bold text-dark mb-1"><?php echo $nama_instansi; ?></h5>
            <span class="text-muted small text-uppercase ls-1">Dashboard Eksekutif</span>
        </div>
        
        <hr class="my-4">

        <form action="core/login_process.php" method="POST">
            
            <?php
            // Menampilkan pesan error jika login gagal
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger text-center p-2 small" role="alert">Username atau Password salah!</div>';
            }
            ?>

            <div class="mb-3">
                <label for="username" class="form-label small fw-bold text-secondary">Username / NIP</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukan ID Pengguna" required autofocus>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label small fw-bold text-secondary">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukan Kata Sandi" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk Aplikasi
                </button>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-muted">&copy; <?php echo date('Y'); ?> SIMKES Khanza Dashboard | <a href="#" data-bs-toggle="modal" data-bs-target="#changelogModal" class="text-decoration-none text-secondary hover-primary"><i class="fas fa-history"></i> Change Log</a></small>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Modal Changelog -->
<div class="modal fade" id="changelogModal" tabindex="-1" aria-labelledby="changelogModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="changelogModalLabel"><i class="fas fa-history me-2"></i>Riwayat Pengembangan Sistem</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body modal-changelog-body">
        <div class="p-2">
            <div id="changelog-container" class="timeline">
                <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat riwayat pengembangan...</div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const changelogModal = document.getElementById('changelogModal');
    if (changelogModal) {
        changelogModal.addEventListener('show.bs.modal', function () {
            const container = document.getElementById('changelog-container');
            if (container.dataset.loaded === 'true') return;
            
            fetch('change_log.md?v=' + new Date().getTime())
              .then(response => {
                  if(!response.ok) throw new Error("Gagal memuat log");
                  return response.text();
              })
              .then(text => {
                 const regex = /## \s*\[([^\]]+)\]\s*—\s*([^\n]+)\s+###\s*([^\n]+)\s+((?:-[^\n]+\s*)+)/g;
                 let matches = [];
                 let match;
                 while ((match = regex.exec(text)) !== null) {
                     matches.push(match);
                 }
                 
                 if (matches.length === 0) {
                     container.innerHTML = '<div class="alert alert-warning">Belum ada catatan changelog dengan format yang sesuai.</div>';
                     return;
                 }
                 
                 // Reverse untuk menampilkan yang terbaru di atas (Timeline Vertikal)
                 matches.reverse(); 
                 
                 let html = '';
                 matches.forEach(m => {
                      let version = m[1];
                      let date = m[2].trim();
                      let types = m[3].trim();
                      let itemsText = m[4].trim();
                      
                      let itemsHtml = itemsText.split('\n').filter(i => i.trim() !== '').map(i => {
                          let t = i.trim();
                          if (t.startsWith('-')) t = t.substring(1).trim();
                          // Parse format bold markdown
                          t = t.replace(/\*\*([^\*]+)\*\*/g, '<strong>$1</strong>');
                          return '<li class="mb-1">' + t + '</li>';
                      }).join('');
                      
                      html += `
                      <div class="timeline-item">
                         <span class="timeline-date"><i class="far fa-clock me-1"></i> ${date}</span>
                         <div class="timeline-content shadow-sm">
                             <h5><span class="badge bg-primary me-2">${version}</span> <small class="text-muted" style="font-size: 0.8rem;">${types}</small></h5>
                             <ul class="mb-0 mt-3 ps-3" style="font-size: 0.9rem; color: #495057;">
                                 ${itemsHtml}
                             </ul>
                         </div>
                      </div>`;
                 });
                 container.innerHTML = html;
                 container.dataset.loaded = 'true';
              })
              .catch(err => {
                  container.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> ${err.message}</div>`;
              });
        });
    }
});
</script>
</body>
</html>