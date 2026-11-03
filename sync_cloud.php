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
    
    // Execute SQL script
    require_once __DIR__ . '/src/Installer.php';
    
    // Drop existing content tables first so fresh data is written
    $tables = ['site_settings', 'resume_items', 'services', 'pricing_plans', 'faqs', 'skills', 'stats', 'testimonials', 'portfolio_items', 'portfolio_images', 'portfolio_categories', 'social_links'];
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
    }
    
    // Split and execute statements
    foreach (Installer::split($sql) as $stmt) {
        if (preg_match('/^(DROP\s|USE\s|CREATE\s+DATABASE\s)/i', $stmt)) {
            continue;
        }
        $pdo->exec($stmt);
    }
    
    echo "<h1>SUCCESS: Cloud Database (TiDB / Vercel) updated successfully with all your data!</h1>";
} catch (Throwable $e) {
    echo "<h1>Error: " . htmlspecialchars($e->getMessage()) . "</h1>";
}
