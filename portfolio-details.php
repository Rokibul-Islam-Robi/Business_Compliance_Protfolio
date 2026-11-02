<?php
/**
 * portfolio-details.php?id=N - single project page (dynamic).
 */
require_once __DIR__ . '/includes/functions.php';

// Validate the id strictly: positive integer only
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$project = $id ? get_portfolio_item((int) $id) : null;

if (!$project) {
    http_response_code(404);
    exit('Project not found. <a href="index.php#portfolio">Back to portfolio</a>');
}

$images      = get_portfolio_images((int) $project['id'], $project['image_path']);
$testimonial = $project['testimonial_id'] ? get_testimonial((int) $project['testimonial_id']) : null;
$paras       = paragraphs($project['details_text']);

$pageTitle = 'Portfolio Details - ' . setting('site_name', 'DevFolio') . ' Bootstrap Template';
$bodyClass = 'portfolio-details-page';
$isHome    = false;
$activeNav = 'portfolio';

require __DIR__ . '/includes/header.php';
?>
  <main class="main">

    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Portfolio Details</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current">Portfolio Details</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Portfolio Details Section -->
    <section id="portfolio-details" class="portfolio-details section">

      <div class="container" data-aos="fade-up">

        <div class="portfolio-details-slider swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "navigation": {
                "nextEl": ".swiper-button-next",
                "prevEl": ".swiper-button-prev"
              },
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              }
            }
          </script>
          <div class="swiper-wrapper align-items-center">

            <?php foreach ($images as $img): ?>
            <div class="swiper-slide">
              <img src="<?= e($img) ?>" alt="">
            </div>

            <?php endforeach; ?>
          </div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
          <div class="swiper-pagination"></div>
        </div>

        <div class="row justify-content-between gy-4 mt-4">

          <div class="col-lg-8" data-aos="fade-up">
            <div class="portfolio-description">
              <h2><?= e($project['details_title'] ?: $project['title']) ?></h2>
              <?php foreach ($paras as $i => $para): ?>
              <p>
                <?= e($para) ?>
              </p>

              <?php if ($i === 1 && $testimonial): /* quote block sits after the 2nd paragraph, as in the template */ ?>
              <div class="testimonial-item">
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span><?= e($testimonial['quote']) ?></span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
                <div>
                  <img src="<?= e($testimonial['image_path']) ?>" class="testimonial-img" alt="">
                  <h3><?= e($testimonial['name']) ?></h3>
                  <h4><?= e($testimonial['position']) ?></h4>
                </div>
              </div>

              <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="col-lg-3" data-aos="fade-up" data-aos-delay="100">
            <div class="portfolio-info">
              <h3>Project information</h3>
              <ul>
                <li><strong>Category</strong> <?= e($project['category_name']) ?></li>
                <li><strong>Client</strong> <?= e($project['client']) ?></li>
                <li><strong>Project date</strong> <?= e($project['project_date'] ? date('d F, Y', strtotime($project['project_date'])) : '') ?></li>
                <li><strong>Project URL</strong> <a href="<?= e(safe_url($project['project_url'])) ?>"><?= e(preg_replace('#^https?://#i', '', (string) $project['project_url'])) ?></a></li>
                <li><a href="<?= e(safe_url($project['project_url'])) ?>" class="btn-visit align-self-start">Visit Website</a></li>
              </ul>
            </div>
          </div>

        </div>

      </div>

    </section><!-- /Portfolio Details Section -->

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
