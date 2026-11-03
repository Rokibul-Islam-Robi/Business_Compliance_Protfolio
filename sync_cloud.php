<?php
// One-time migration endpoint to sync Cloud Database
$secret = $_GET['secret'] ?? '';
if ($secret !== 'robi_sync_2026') {
    http_response_code(403);
    exit('Access denied.');
}

require_once __DIR__ . '/db.php';

try {
    $pdo = Database::pdo();
    $sqlFile = __DIR__ . '/Database/database.sql';
    if (!is_file($sqlFile)) {
        exit('database.sql not found.');
    }
    $sql = file_get_contents($sqlFile);
    
    // Disable foreign key checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    require_once __DIR__ . '/src/Installer.php';
    
    // Drop all tables so clean recreation occurs
    $allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allTables as $t) {
        if ($t !== 'php_sessions') {
            $pdo->exec("DROP TABLE IF EXISTS `$t`");
        }
    }
    
    foreach (Installer::split($sql) as $stmt) {
        if (preg_match('/^(DROP\s|USE\s|CREATE\s+DATABASE\s)/i', $stmt)) {
            continue;
        }
        $pdo->exec($stmt);
    }
    
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "<h1>SUCCESS: Cloud Database (TiDB / Vercel) updated successfully with all your data!</h1>";
} catch (Throwable $e) {
    echo "<h1>Error: " . htmlspecialchars($e->getMessage()) . "</h1>";
}
