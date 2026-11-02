<?php
/**
 * about.php - About page (profile, skills and bio).
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle   = 'About - ' . setting('site_name', 'DevFolio') . ' Bootstrap Template';
$bodyClass   = 'about-page inner-page';
$isHome      = false;
$activeNav   = 'about';
$bannerTitle = 'About';

$skills = get_skills();

require __DIR__ . '/includes/header.php';
?>
  <main class="main">

    <?php require __DIR__ . '/includes/breadcrumb.php'; ?>

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">
       <div class="inner-card" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-5 gx-xl-5">
          <div class="col-md-6">

            <div class="row justify-content-between align-items-center gy-4 about-profile">
              <div class="col-lg-5">
                <img src="<?= e(setting('about_image', 'img/profile-img.jpg')) ?>" class="img-fluid about-photo" alt="<?= e(setting('about_name')) ?>">
              </div>
              <div class="col-lg-7 about-info">
                <p><strong>Name: </strong> <span><?= e(setting('about_name')) ?></span></p>
                <p><strong>Profile: </strong> <span><?= e(setting('about_profile')) ?></span></p>
                <p><strong>Email: </strong> <span><?= e(setting('about_email')) ?></span></p>
                <p><strong>Phone: </strong> <span><?= e(setting('about_phone')) ?></span></p>
              </div>
            </div>

            <div class="skills-content skills-animation">

              <h5>Skills</h5>

              <?php foreach ($skills as $s): $pct = max(0, min(100, (int) $s['percentage'])); ?>
              <div class="progress">
                <span class="skill"><span><?= e($s['name']) ?></span> <i class="val"><?= $pct ?>%</i></span>
                <div class="progress-bar-wrap">
                  <div class="progress-bar" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div><!-- End Skills Item -->

              <?php endforeach; ?>
            </div>
          </div>

          <div class="col-md-6">
            <div class="about-me">
              <?php if (!inner_title_redundant(setting('about_heading', 'About me'), $bannerTitle)): ?>
              <h4><?= e(setting('about_heading', 'About me')) ?></h4>
              <?php endif; ?>
              <?php foreach (paragraphs(setting('about_text')) as $para): ?>
              <p>
                <?= e($para) ?>
              </p>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

       </div><!-- /inner-card -->
      </div>

    </section><!-- /About Section -->

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
