<?php
header('Content-Type: text/plain');

echo "--- opcache ini ---\n";
echo "opcache.enable=" . (ini_get('opcache.enable') ?: 'unset') . "\n";
echo "opcache.validate_timestamps=" . (ini_get('opcache.validate_timestamps') ?: 'unset') . "\n";
echo "opcache.revalidate_freq=" . (ini_get('opcache.revalidate_freq') ?: 'unset') . "\n";

$envPath = __DIR__.'/../app/backend/.env';
echo "\n--- raw .env (this process's view) ---\n";
if (file_exists($envPath)) {
    foreach (file($envPath) as $line) {
        if (str_starts_with($line, 'DB_HOST=')) {
            echo trim($line)."\n";
        }
    }
} else {
    echo "not found at $envPath\n";
}

$configCachePath = __DIR__.'/../app/backend/bootstrap/cache/config.php';
echo "\n--- cached config.php (this process's view) ---\n";
if (file_exists($configCachePath)) {
    echo "mtime: ".date('Y-m-d H:i:s', filemtime($configCachePath))."\n";
    $cfg = include $configCachePath;
    echo "cached DB_HOST: ".($cfg['database']['connections']['mysql']['host'] ?? 'N/A')."\n";
} else {
    echo "not found at $configCachePath\n";
}

if (isset($_GET['reset']) && function_exists('opcache_reset')) {
    echo "\n--- opcache_reset() ---\n";
    echo opcache_reset() ? "true\n" : "false\n";
}

echo "\n--- direct PDO test using cached config values ---\n";
try {
    $host = $cfg['database']['connections']['mysql']['host'] ?? '127.0.0.1';
    $db = $cfg['database']['connections']['mysql']['database'] ?? '';
    $user = $cfg['database']['connections']['mysql']['username'] ?? '';
    $pass = $cfg['database']['connections']['mysql']['password'] ?? '';
    $pdo = new PDO("mysql:host={$host};port=3306;dbname={$db}", $user, $pass, [PDO::ATTR_TIMEOUT => 3]);
    echo "PDO connect OK, session count: ".$pdo->query('select count(*) from sessions')->fetchColumn()."\n";
} catch (\Throwable $e) {
    echo "PDO connect FAILED: ".$e->getMessage()."\n";
}
