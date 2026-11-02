<?php
/**
 * admin/partials/header.php - <head>, sidebar, top bar and flash alerts.
 * Expects: $pageTitle (string), $activeMenu (string)
 */
$pageTitle  = $pageTitle  ?? 'Dashboard';
$activeMenu = $activeMenu ?? '';
$adminUser  = Auth::user();
$unread     = (int) Database::fetchValue('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
$siteName   = (string) Database::fetchValue("SELECT setting_value FROM site_settings WHERE setting_key = 'site_name'") ?: 'DevFolio';

/** [label, file, icon, key] grouped by heading.
 *  NOTE: all variables here use an "admin/nav" prefix on purpose - this file is included
 *  *after* each page created its own objects ($menu, $form, $skills ...), so generic
 *  names would silently overwrite them (that was the "render() on array" error). */
$adminNav = [
  'Overview' => [['Dashboard', 'index.php', 'bi-speedometer2', 'dashboard']],
  'Page sections' => [
    ['Hero', 'hero.php', 'bi-stars', 'hero'],
    ['About & Skills', 'about.php', 'bi-person-badge', 'about'],
    ['Resume', 'resume.php', 'bi-file-earmark-person', 'resume'],
    ['Services', 'services.php', 'bi-briefcase', 'services'],
    ['Counters', 'stats.php', 'bi-bar-chart-line', 'stats'],
    ['Projects', 'projects.php', 'bi-images', 'projects'],
    ['Categories', 'categories.php', 'bi-tags', 'categories'],
    ['Pricing', 'pricing.php', 'bi-cash-coin', 'pricing'],
    ['FAQ', 'faq.php', 'bi-question-circle', 'faq'],
    ['Testimonials', 'testimonials.php', 'bi-chat-quote', 'testimonials'],
    ['Contact & Social', 'contact.php', 'bi-geo-alt', 'contact'],
    ['Menu', 'menu.php', 'bi-menu-button-wide', 'menu'],
  ],
  'Inbox' => [['Messages', 'messages.php', 'bi-envelope', 'messages']],
  'System' => [
    ['Site Settings', 'settings.php', 'bi-gear', 'settings'],
    ['My Account', 'account.php', 'bi-shield-lock', 'account'],
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($pageTitle) ?> · <?= e($siteName) ?> Admin</title>
  <link rel="icon" href="../img/favicon.png">
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/admin.css" rel="stylesheet">
</head>
<body class="admin-body">

<div class="sidebar-backdrop" data-sidebar-close></div>
<aside class="admin-sidebar" id="adminSidebar">
  <a href="index.php" class="brand"><i class="bi bi-layers-half"></i><span><?= e($siteName) ?> <small>CMS</small></span></a>
  <nav class="side-nav">
    <?php foreach ($adminNav as $navGroup => $navLinks): ?>
      <div class="side-heading"><?= e($navGroup) ?></div>
      <?php foreach ($navLinks as [$navLabel, $navFile, $navIcon, $navKey]): ?>
        <a href="<?= e($navFile) ?>" class="side-link<?= $activeMenu === $navKey ? ' active' : '' ?>">
          <i class="bi <?= e($navIcon) ?>"></i><span><?= e($navLabel) ?></span>
          <?php if ($navKey === 'messages' && $unread > 0): ?><span class="badge rounded-pill text-bg-danger ms-auto"><?= $unread ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <a href="../index.php" target="_blank" rel="noopener" class="side-view-site"><i class="bi bi-box-arrow-up-right me-2"></i>View website</a>
</aside>

<div class="admin-main">
  <header class="admin-topbar">
    <button class="btn btn-light d-lg-none" type="button" data-sidebar-toggle aria-label="Toggle menu"><i class="bi bi-list fs-5"></i></button>
    <h1 class="h5 mb-0 flex-grow-1 text-truncate"><?= e($pageTitle) ?></h1>
    <div class="dropdown">
      <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="avatar"><?= e(strtoupper(substr((string) ($adminUser['username'] ?? 'A'), 0, 1))) ?></span>
        <span class="d-none d-sm-inline"><?= e($adminUser['username'] ?? '') ?></span><i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><a class="dropdown-item" href="account.php"><i class="bi bi-shield-lock me-2"></i>My account</a></li>
        <li><a class="dropdown-item" href="../index.php" target="_blank" rel="noopener"><i class="bi bi-eye me-2"></i>View website</a></li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <form method="post" action="logout.php" class="m-0"><?= Csrf::field() ?>
            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
          </form>
        </li>
      </ul>
    </div>
  </header>

  <main class="admin-content">
    <?php foreach (Flash::pull() as $f): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show shadow-sm" role="alert" data-autodismiss="<?= $f['type'] === 'success' ? '5000' : '0' ?>">
        <i class="bi bi-<?= $f['type'] === 'success' ? 'check-circle' : ($f['type'] === 'danger' ? 'x-octagon' : 'info-circle') ?> me-2"></i><?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endforeach; ?>
