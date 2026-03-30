<?php
/*
 * File header.php (FINAL FIX - STRUCTURE REFACTOR)
 * - Fix: Menghapus container-fluid/row pembungkus agar Main bisa resize otomatis.
 * - Fitur: Sidebar & Main sejajar (siblings), bukan parent-child dalam grid.
 */

require_once(dirname(__DIR__) . '/config/koneksi.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); 
    exit;
}

$nama_instansi = "Dashboard RS"; 
$logo_src = "core/logo.php";

$sql_setting = "SELECT setting.nama_instansi FROM setting LIMIT 1";
$result_setting = $koneksi->query($sql_setting);
if ($result_setting && $result_setting->num_rows > 0) {
    $row_setting = $result_setting->fetch_assoc();
    $nama_instansi = htmlspecialchars($row_setting['nama_instansi']);
}

$current_page = basename($_SERVER['PHP_SELF']);

function get_collapse_class($pages, $current) {
    return in_array($current, $pages) ? 'show' : '';
}
function is_active($page, $current) {
    return ($page == $current) ? 'active' : '';
}
function get_arrow_class($pages, $current) {
    return in_array($current, $pages) ? '' : 'collapsed'; 
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?> - <?php echo $nama_instansi; ?></title>
    <link rel="icon" href="<?php echo $logo_src; ?>" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 56px;
            --transition-speed: 0.3s;
            --sidebar-bg: #f8f9fa;
            --primary-color: #0d6efd;
        }

        body {
            font-size: .875rem;
            overflow-x: hidden;
            background-color: #f4f6f9;
        }

        /* --- NAVBAR (FIXED TOP) --- */
        .navbar {
            height: var(--header-height);
            z-index: 1030; /* Di atas sidebar */
        }
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
            width: var(--sidebar-width);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- SIDEBAR (INDEPENDENT) --- */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000; /* Di bawah navbar */
            padding-top: var(--header-height); 
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            border-right: 1px solid #dee2e6;
            transition: transform var(--transition-speed) ease-in-out;
            overflow-y: auto;
        }

        /* --- MAIN CONTENT (DYNAMIC WIDTH) --- */
        main {
            display: block; 
            width: auto; 
            margin-left: var(--sidebar-width); 
            padding: 20px;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left var(--transition-speed) ease-in-out;
        }

        /* --- LOGIKA TOGGLE DESKTOP --- */
        body.sidebar-closed .sidebar {
            transform: translateX(-100%); 
        }
        body.sidebar-closed main {
            margin-left: 0; 
        }

        /* --- LOGIKA TOGGLE MOBILE --- */
        @media (max-width: 767.98px) {
            .sidebar { transform: translateX(-100%); }
            main { margin-left: 0; }

            body.sidebar-open .sidebar { transform: translateX(0); box-shadow: 0 0 15px rgba(0,0,0,0.2); }
            
            .sidebar-overlay {
                display: none;
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.5); z-index: 999;
            }
            body.sidebar-open .sidebar-overlay { display: block; }
        }

        .nav-link { color: #333; padding: 8px 16px; font-weight: 500; }
        .nav-link:hover { color: var(--primary-color); background-color: #e9ecef; }
        .nav-link.active { color: var(--primary-color); background-color: #e7f1ff; border-left: 3px solid var(--primary-color); }
        .sidebar-group-header { cursor: pointer; padding: 10px 15px; margin-top: 5px; color: #6c757d; font-size: 0.75rem; font-weight: 700; display: flex; justify-content: space-between; text-transform: uppercase;}
        .sidebar-group-header:hover { color: var(--primary-color); }
        .sidebar-group-header .fa-chevron-down { transition: transform 0.3s; }
        .sidebar-group-header.collapsed .fa-chevron-down { transform: rotate(-90deg); }
        .collapse .nav-flex-column { padding-left: 10px; background-color: #fff; }

        /* ========== GLOBAL LOADING OVERLAY ========== */
        #globalLoadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(3px);
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            transition: opacity 0.2s ease;
        }
        #globalLoadingOverlay .loading-box {
            text-align: center;
            background: #fff;
            border-radius: 16px;
            padding: 36px 48px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            border: 1px solid #e9ecef;
        }
        #globalLoadingOverlay .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3em;
        }
        #globalLoadingOverlay .loading-text {
            margin-top: 16px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0;
        }
        #globalLoadingOverlay .loading-subtext {
            font-size: 0.8rem;
            color: #adb5bd;
            margin-top: 4px;
        }
        @keyframes dotsAnim {
            0%   { content: ''; }
            33%  { content: '.'; }
            66%  { content: '..'; }
            100% { content: '...'; }
        }
        #globalLoadingOverlay .dots::after {
            content: '';
            display: inline-block;
            animation: dotsAnim 1.2s steps(1, end) infinite;
        }
    </style>
</head>
<body>

<!-- ===== GLOBAL LOADING OVERLAY ===== -->
<div id="globalLoadingOverlay">
    <div class="loading-box">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="loading-text">Memuat data<span class="dots"></span></p>
        <p class="loading-subtext">Mohon tunggu, proses pengambilan data sedang berlangsung</p>
    </div>
</div>
<!-- ===== END LOADING OVERLAY ===== -->

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">
      <img src="<?php echo $logo_src; ?>" alt="Logo" width="25" height="25" class="d-inline-block align-text-top me-2">
      <?php echo $nama_instansi; ?>
  </a>
  
  <button class="btn btn-link text-white d-none d-md-block ms-2" id="sidebarToggleDesktop">
      <i class="fas fa-bars fa-lg"></i>
  </button>
  
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" id="sidebarToggleMobile" style="right: 10px; top: 15px;">
    <span class="navbar-toggler-icon"></span>
  </button>
  
  <div class="w-100"></div>
  
  <div class="navbar-nav d-flex flex-row">
    <div class="nav-item text-nowrap">
      <span class="nav-link px-3 text-white small">Halo, <?php echo htmlspecialchars($_SESSION['nama_user']); ?></span>
    </div>
    <div class="nav-item text-nowrap">
      <a class="nav-link px-3 text-danger" href="core/logout.php" title="Keluar"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</header>

<div class="sidebar-overlay" id="mobileOverlay"></div>

<nav id="sidebarMenu" class="sidebar">
  <div class="pt-3 pb-5">

    <!-- CHANGE LOG BUTTON START -->
    <div class="px-3 mb-3 text-center">
        <button type="button" class="btn btn-sm btn-primary w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#changelogModal">
            <i class="fas fa-history me-1"></i> Log Pengembangan
        </button>
    </div>
    <!-- CHANGE LOG BUTTON END -->

    <ul class="nav flex-column mb-2">
      <li class="nav-item">
        <a class="nav-link <?php echo is_active('dashboard.php', $current_page); ?>" href="dashboard.php">
          <i class="fas fa-home me-2 text-primary" style="width: 20px;"></i> Dashboard Utama
        </a>
      </li>
    </ul>

    <?php
    // BACA FILE JSON SIDEBAR
    $sidebar_json_file = dirname(__DIR__) . '/config/sidebar_menu.json';
    $sidebar_menus = [];
    if (file_exists($sidebar_json_file)) {
        $json_data = file_get_contents($sidebar_json_file);
        $sidebar_menus = json_decode($json_data, true);
    }

    // LOOPING MENU DARI JSON
    if (!empty($sidebar_menus)) {
        foreach ($sidebar_menus as $menu) {
            // Lewati jika grup/menu diset tidak aktif (is_active = false)
            if (isset($menu['is_active']) && $menu['is_active'] === false) {
                continue;
            }

            if (isset($menu['is_group']) && $menu['is_group']) {
                $group_id = $menu['id'];
                $group_title = $menu['title'];
                
                // Kumpulkan semua URL dari item dalam grup ini untuk fungsi collapse/active
                $group_urls = [];
                if (isset($menu['items']) && is_array($menu['items'])) {
                    foreach ($menu['items'] as $item) {
                        if (isset($item['url'])) {
                            $group_urls[] = $item['url'];
                        }
                    }
                }
                ?>
                <div class="sidebar-group-header <?php echo get_arrow_class($group_urls, $current_page); ?>" data-bs-toggle="collapse" data-bs-target="#<?php echo $group_id; ?>">
                    <span><?php echo htmlspecialchars($group_title); ?></span> <i class="fas fa-chevron-down"></i>
                </div>
                <div class="collapse <?php echo get_collapse_class($group_urls, $current_page); ?>" id="<?php echo $group_id; ?>">
                    <ul class="nav flex-column nav-flex-column">
                        <?php
                        if (isset($menu['items']) && is_array($menu['items'])) {
                            foreach ($menu['items'] as $item) {
                                // Lewati substem jika diset tidak aktif
                                if (isset($item['is_active']) && $item['is_active'] === false) {
                                    continue;
                                }
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo is_active($item['url'], $current_page); ?>" href="<?php echo htmlspecialchars($item['url']); ?>">
                                        <i class="<?php echo htmlspecialchars($item['icon']); ?> me-2" style="width: 20px;"></i> <?php echo htmlspecialchars($item['title']); ?>
                                    </a>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </div>
                <?php
            } else {
                // Untuk menu single (jika ada selain dashboard, meskipun dashboard diatas sudah hardcoded)
                ?>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link <?php echo is_active($menu['url'], $current_page); ?>" href="<?php echo htmlspecialchars($menu['url']); ?>">
                            <i class="<?php echo htmlspecialchars($menu['icon']); ?> me-2" style="width: 20px;"></i> <?php echo htmlspecialchars($menu['title']); ?>
                        </a>
                    </li>
                </ul>
                <?php
            }
        }
    }
    ?>

	<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'Super Admin') { ?>
    <div class="sidebar-group-header <?php echo get_arrow_class(['laporan_audit_trail.php', 'manage_users.php', 'setting_sidebar.php'], $current_page); ?>" data-bs-toggle="collapse" data-bs-target="#menuSuperAdmin">
        <span><i class="fas fa-user-shield me-1"></i> Super Admin</span> <i class="fas fa-chevron-down"></i>
    </div>
    <div class="collapse <?php echo get_collapse_class(['laporan_audit_trail.php', 'manage_users.php', 'setting_sidebar.php'], $current_page); ?>" id="menuSuperAdmin">
		<ul class="nav flex-column nav-flex-column">
            <li class="nav-item"><a class="nav-link <?php echo is_active('laporan_audit_trail.php', $current_page); ?>" href="laporan_audit_trail.php"><i class="fas fa-shield-alt me-2" style="width: 20px;"></i> Audit Trail</a></li>
			<li class="nav-item"><a class="nav-link <?php echo is_active('manage_users.php', $current_page); ?>" href="manage_users.php"><i class="fas fa-users-cog me-2" style="width: 20px;"></i> Manage Users</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('setting_sidebar.php', $current_page); ?>" href="setting_sidebar.php"><i class="fas fa-sliders-h me-2" style="width: 20px;"></i> Setting Sidebar</a></li>
		</ul>
	</div>
	<?php } ?>
	
    <br><br>
  </div>
</nav>

<!-- Modal Changelog -->
<div class="modal fade" id="changelogModal" tabindex="-1" aria-labelledby="changelogModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="changelogModalLabel"><i class="fas fa-history me-2"></i>Riwayat Pengembangan Sistem</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body modal-changelog-body" style="background-color: #f8f9fa;">
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

<style>
/* CSS Tambahan khusus untuk Timeline Modal */
.timeline { position: relative; padding: 1rem 0; margin: 0; }
.timeline::before { content: ''; position: absolute; top: 0; bottom: 0; left: 20px; width: 2px; background: #dee2e6; }
.timeline-item { position: relative; margin-bottom: 2rem; padding-left: 50px; }
.timeline-item::before { content: ''; position: absolute; left: 14px; top: 5px; width: 14px; height: 14px; border-radius: 50%; background: #0d6efd; border: 3px solid #fff; box-shadow: 0 0 0 2px #0d6efd; z-index: 1; }
.timeline-date { font-size: 0.85rem; font-weight: bold; color: #6c757d; margin-bottom: 0.5rem; display: block; }
.timeline-content { background: #fff; padding: 1rem; border-radius: 8px; border: 1px solid #e9ecef; }
.timeline-content h5 { font-size: 1rem; font-weight: 700; margin-bottom: 0px; color: #212529; }
</style>

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

<main>
    <div class="container-fluid">

<script>
    // Script untuk Sidebar Toggle & Persistence
    document.addEventListener("DOMContentLoaded", function() {
        const body = document.body;
        const toggleDesktop = document.getElementById('sidebarToggleDesktop');
        const toggleMobile = document.getElementById('sidebarToggleMobile');
        const overlay = document.getElementById('mobileOverlay');

        // 1. Cek Preferensi User (LocalStorage)
        const savedState = localStorage.getItem('sidebarState');
        if (savedState === 'closed') {
            body.classList.add('sidebar-closed');
        }

        // 2. Toggle Desktop
        if(toggleDesktop) {
            toggleDesktop.addEventListener('click', function() {
                body.classList.toggle('sidebar-closed');
                // Simpan state
                if (body.classList.contains('sidebar-closed')) {
                    localStorage.setItem('sidebarState', 'closed');
                } else {
                    localStorage.setItem('sidebarState', 'open');
                }
            });
        }

        // 3. Toggle Mobile
        if(toggleMobile) {
            toggleMobile.addEventListener('click', function() {
                body.classList.toggle('sidebar-open');
            });
        }

        // 4. Tutup Sidebar Mobile saat klik overlay
        if(overlay) {
            overlay.addEventListener('click', function() {
                body.classList.remove('sidebar-open');
            });
        }
    });
</script>