<?php
/**
 * services.php - Services page.
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle   = 'Services - ' . setting('site_name', 'DevFolio') . ' Bootstrap Template';
$bodyClass   = 'services-page inner-page';
$isHome      = false;
$activeNav   = 'services';
$bannerTitle = 'Services';

$services = get_services();

require __DIR__ . '/includes/header.php';
?>
  <main class="main">

    <?php require __DIR__ . '/includes/breadcrumb.php'; ?>

    <!-- Services Section -->
    <section id="services" class="services section">

      <div class="container">
       <div class="inner-card">
        <?php inner_intro(setting('services_title', 'Services'), setting('services_subtitle'), $bannerTitle); ?>

        <div class="row gy-4">

          <?php foreach ($services as $i => $sv): ?>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i + 1) * 100 ?>">
            <div class="service-item position-relative">
              <div class="icon">
                <i class="bi <?= e($sv['icon']) ?>"></i>
              </div>
              <a href="#" class="stretched-link">
                <h3><?= e($sv['title']) ?></h3>
              </a>
              <p><?= e($sv['description']) ?></p>
            </div>
          </div><!-- End Service Item -->

          <?php endforeach; ?>
        </div>

       </div><!-- /inner-card -->
      </div>

    </section><!-- /Services Section -->

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
