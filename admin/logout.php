<?php
/** admin/logout.php - POST only (CSRF-protected sign out). */
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::require();
    Auth::logout();
    Flash::add('success', 'You have been signed out.');
}
redirect('login.php');
