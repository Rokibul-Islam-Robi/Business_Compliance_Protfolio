<?php
/**
 * src/bootstrap.php - shared start-up for the public site and the admin panel.
 * Defines paths, loads the DB class + helpers and registers a tiny class autoloader.
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/uploads');

require_once ROOT_PATH . '/db.php';
require_once __DIR__ . '/helpers.php';

// Autoload classes: class "Crud" => src/Crud.php
spl_autoload_register(static function (string $class): void {
    if (preg_match('/^[A-Za-z]+$/', $class)) {
        $file = __DIR__ . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
