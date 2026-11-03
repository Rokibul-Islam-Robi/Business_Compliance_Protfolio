<?php
/**
 * includes/header.php - <head>, sticky header and navigation.
 *
 * Variables the including page may set BEFORE requiring this file:
 *   $pageTitle  string  <title> text
 *   $bodyClass  string  class on <body>   (e.g. "index-page")
 *   $isHome     bool    true on index.php (anchors stay on-page)
 *   $activeNav  string  hero|about|services|portfolio (nav item to highlight)
 */
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Index - DevFolio Bootstrap Template';
$bodyClass = $bodyClass ?? 'index-page';
$isHome    = $isHome ?? true;
$activeNav = $activeNav ?? 'hero';

$siteName = setting('site_name', 'DevFolio');
$siteLogo = setting('site_logo', '');

// Main navigation comes from the CMS (admin > Menu). A link is highlighted when
// its target page matches the current script (portfolio-details.php counts as
// the Portfolio item too).
$navItems      = get_nav_items();
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$navLinkActive = static function (string $url) use ($currentScript): bool {
    $path = strtok($url, '#?') ?: '';
    if ($path === '' || !str_ends_with($path, '.php')) {
        return false;
    }
    if ($path === $currentScript) {
        return true;
    }
    // sub-pages such as portfolio-details.php inherit their section highlight
    $slug = substr($path, 0, -4);
    return str_starts_with($currentScript, $slug . '-');
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e(setting('meta_description')) ?>">
  <meta name="keywords" content="<?= e(setting('meta_keywords')) ?>">

  <meta name="robots" content="noindex, nofollow">

  <!-- Favicons -->
  <link href="img/favicon.png" rel="icon">
  <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap-icons.css" rel="stylesheet">
  <link href="css/aos.css" rel="stylesheet">
  <link href="css/glightbox.min.css" rel="stylesheet">
  <link href="css/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="css/main.css?v=2.1" rel="stylesheet">

  <!-- =======================================================
  * Template Name: DevFolio
  * Template URL: https://bootstrapmade.com/devfolio-bootstrap-portfolio-html-template/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="<?= e($bodyClass) ?>">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center">
        <?php if (!empty($siteLogo) && is_file(ROOT_PATH . '/' . $siteLogo)): ?>
          <img src="<?= e($siteLogo) ?>" alt="<?= e($siteName) ?>">
        <?php else: ?>
          <h1 class="sitename"><?= e($siteName) ?></h1>
        <?php endif; ?>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <?php foreach ($navItems as $item): $isActive = $navLinkActive((string) $item['url']); ?>
          <li><a href="<?= e(nav_url($item['url'])) ?>"<?= $isActive ? ' class="active"' : '' ?>><?= e($item['label']) ?></a></li>
          <?php endforeach; ?>
          <?php if (!$navItems): ?>
          <li><a href="index.php">Home</a></li>
          <?php endif; ?>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

