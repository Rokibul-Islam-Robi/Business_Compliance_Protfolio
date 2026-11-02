<?php
/**
 * includes/breadcrumb.php - banner section with background image + breadcrumbs.
 *
 * Variables the including page may set BEFORE requiring this file:
 *   $bannerTitle  string  page title shown in the banner (required)
 *   $bannerImage  string  optional background image override
 */
require_once __DIR__ . '/functions.php';

$bannerTitle = $bannerTitle ?? 'Page';
$bannerImage = $bannerImage ?? setting('page_banner_image', 'img/Hero-image.jpg');
?>
    <!-- Page Title -->
    <div class="page-title page-banner" data-aos="fade" style="background-image: url('<?= e($bannerImage) ?>')">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0"><?= e($bannerTitle) ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current"><?= e($bannerTitle) ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->
