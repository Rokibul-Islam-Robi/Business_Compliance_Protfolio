<?php
// api/index.php - Front Controller for Vercel Serverless PHP

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = trim($uri, '/');

// Base directory is the project root (one level above api/)
$baseDir = dirname(__DIR__);

// Handle dynamic uploaded file serving from TiDB / MySQL Database
if (str_starts_with($uri, 'uploads/')) {
    $diskFile = $baseDir . '/' . $uri;
    if (is_file($diskFile)) {
        $mime = mime_content_type($diskFile) ?: 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($diskFile);
        exit;
    }

    // Fallback: check Database for serverless persistence
    try {
        require_once $baseDir . '/db.php';
        $row = Database::fetchOne('SELECT mime_type, data FROM uploaded_files WHERE path = :p', [':p' => $uri]);
        if ($row && !empty($row['data'])) {
            header('Content-Type: ' . ($row['mime_type'] ?: 'image/jpeg'));
            header('Cache-Control: public, max-age=31536000');
            echo $row['data'];
            exit;
        }
    } catch (Throwable $e) {}

    http_response_code(404);
    exit('Image not found.');
}

// Handle admin routes
if (str_starts_with($uri, 'admin')) {
    $target = $baseDir . '/' . $uri;
    if (is_file($target)) {
        chdir(dirname($target));
        require $target;
        exit;
    }
    if (is_file($target . '.php')) {
        chdir(dirname($target));
        require $target . '.php';
        exit;
    }
    if (is_dir($target) && is_file($target . '/index.php')) {
        chdir($target);
        require $target . '/index.php';
        exit;
    }
}

// Handle root / specific PHP scripts
if ($uri === '' || $uri === 'index' || $uri === 'index.php') {
    chdir($baseDir);
    require $baseDir . '/index.php';
    exit;
}

$directFile = $baseDir . '/' . $uri;
if (is_file($directFile) && pathinfo($directFile, PATHINFO_EXTENSION) === 'php') {
    chdir(dirname($directFile));
    require $directFile;
    exit;
}

if (is_file($directFile . '.php')) {
    chdir(dirname($directFile));
    require $directFile . '.php';
    exit;
}

// Fallback to index.php
chdir($baseDir);
require $baseDir . '/index.php';
