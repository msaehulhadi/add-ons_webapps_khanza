<?php
$file = 'config/sidebar_menu.json';
if (!file_exists($file)) die("File not found");
$json = json_decode(file_get_contents($file), true);

$inject = false;
foreach ($json as &$group) {
    if ($group['title'] == 'Statistik & Indikator') {
        $found = false;
        foreach ($group['submenu'] as $s) {
            if ($s['title'] == 'Antrean Online BPJS') $found = true;
        }
        if (!$found) {
            $group['submenu'][] = [
                'title' => 'Antrean Online BPJS',
                'url' => 'laporan_antrol_bpjs.php',
                'icon' => 'fas fa-mobile-alt'
            ];
            $inject = true;
        }
    }
}
if ($inject) {
    file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));
    echo "Sidebar menu updated!\n";
} else {
    echo "Already exists or group not found.\n";
}
?>
