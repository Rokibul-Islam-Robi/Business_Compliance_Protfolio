<?php
/**
 * portfolio.php - Portfolio page (filterable project grid).
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle   = 'Portfolio - ' . setting('site_name', 'DevFolio') . ' Bootstrap Template';
$bodyClass   = 'portfolio-page inner-page';
$isHome      = false;
$activeNav   = 'portfolio';
$bannerTitle = 'Portfolio';

$categories = get_categories();
$projects   = get_portfolio_items();

require __DIR__ . '/includes/header.php';
?>
  <main class="main">

    <?php require __DIR__ . '/includes/breadcrumb.php'; ?>

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio section">

      <div class="container">
       <div class="inner-card">
        <?php inner_intro(setting('portfolio_title', 'Portfolio'), setting('portfolio_subtitle'), $bannerTitle); ?>

        <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">

          <ul class="portfolio-filters isotope-filters" data-aos="fade-up" data-aos-delay="100">
            <li data-filter="*" class="filter-active">All</li>
            <?php foreach ($categories as $c): ?>
            <li data-filter=".filter-<?= e($c['slug']) ?>"><?= e($c['name']) ?></li>
            <?php endforeach; ?>
          </ul><!-- End Portfolio Filters -->

          <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="200">

            <?php foreach ($projects as $p): ?>
            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-<?= e($p['category_slug']) ?>">
              <img src="<?= e($p['image_path']) ?>" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4><?= e($p['title']) ?></h4>
                <p><?= e($p['short_description']) ?></p>
                <a href="<?= e($p['image_path']) ?>" title="<?= e($p['title']) ?>" data-gallery="portfolio-gallery-<?= e($p['category_slug']) ?>" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.php?id=<?= (int) $p['id'] ?>" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            <?php endforeach; ?>
          </div><!-- End Portfolio Container -->

        </div>

      </div>

    </section><!-- /Portfolio Section -->

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
