<?php
// HAPUS FILE INI SETELAH SELESAI!
echo "<pre style='font-family:monospace;font-size:14px;padding:20px;'>";
echo "=== TOKAKU DIAGNOSTIK ===\n\n";

echo "PHP: " . PHP_VERSION . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? '-') . "\n\n";

$base = __DIR__ . '/..';

$checks = [
    'vendor/autoload.php'                          => 'Vendor autoload',
    'vendor/maatwebsite'                           => 'maatwebsite/excel',
    'vendor/barryvdh'                              => 'barryvdh/dompdf',
    'vendor/phpoffice'                             => 'phpoffice (excel dep)',
    'bootstrap/cache/config.php'                   => 'Config cache',
    'bootstrap/cache/services.php'                 => 'Services cache',
    'composer.lock'                                => 'composer.lock',
];

foreach ($checks as $path => $label) {
    $full   = $base . '/' . $path;
    $exists = file_exists($full) || is_dir($full);
    echo ($exists ? '✅' : '❌') . " $label ($path)\n";
}

echo "\n--- .env ---\n";
if (file_exists($base . '/.env')) {
    $env = file_get_contents($base . '/.env');
    foreach (['APP_ENV', 'APP_DEBUG', 'SESSION_DRIVER', 'CACHE_DRIVER'] as $key) {
        preg_match("/^$key=(.+)/m", $env, $m);
        echo "$key = " . trim($m[1] ?? '?') . "\n";
    }
}

echo "\n--- Vendor top-level packages ---\n";
$vendorDir = $base . '/vendor';
if (is_dir($vendorDir)) {
    $dirs = array_filter(scandir($vendorDir), fn($d) => $d[0] !== '.' && is_dir($vendorDir.'/'.$d));
    sort($dirs);
    foreach ($dirs as $d) echo "  $d\n";
} else {
    echo "vendor/ TIDAK ADA!\n";
}

echo "\n--- Config cache content (APP_NAME) ---\n";
$configCache = $base . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    $config = include $configCache;
    echo "app.name = " . ($config['app']['name'] ?? '?') . "\n";
    echo "Cache dibuat: " . date('d M Y H:i', filemtime($configCache)) . "\n";
} else {
    echo "Tidak ada config cache\n";
}

echo "</pre>";
