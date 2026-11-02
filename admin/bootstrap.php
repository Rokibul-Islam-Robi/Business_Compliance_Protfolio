<?php
/**
 * admin/bootstrap.php - included at the very top of EVERY admin page.
 * Loads the framework, starts the hardened session and enforces login
 * (unless the page defines ADMIN_PUBLIC before including this file).
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store, no-cache, must-revalidate');

Auth::startSession();

if (!defined('ADMIN_PUBLIC')) {
    Auth::requireLogin();
}
