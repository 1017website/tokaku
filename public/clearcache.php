<?php
// HAPUS FILE INI SETELAH DIPAKAI!

$base = __DIR__ . '/..';

$files = [
    $base . '/bootstrap/cache/config.php',
    $base . '/bootstrap/cache/services.php',
    $base . '/bootstrap/cache/packages.php',
    $base . '/bootstrap/cache/routes-v7.php',
    $base . '/bootstrap/cache/events.php',
];

echo "<pre style='font:14px monospace;padding:20px;background:#0f172a;color:#e2e8f0;'>";
echo "=== TOKAKU CACHE CLEANER ===\n\n";

foreach ($files as $file) {
    $name = basename($file);
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✅ Deleted: $name\n";
        } else {
            echo "❌ Failed to delete: $name\n";
        }
    } else {
        echo "⚪ Not found: $name\n";
    }
}

// Clear view cache
$viewCache = $base . '/storage/framework/views';
if (is_dir($viewCache)) {
    $count = 0;
    foreach (glob($viewCache . '/*.php') as $f) {
        unlink($f);
        $count++;
    }
    echo "\n✅ Cleared $count compiled views\n";
}

echo "\n✅ SELESAI! Hapus file clearcache.php sekarang.\n";
echo "</pre>";
