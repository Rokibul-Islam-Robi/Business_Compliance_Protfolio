<?php
/**
 * index.php - DevFolio one-page portfolio (dynamic).
 * Every section is rendered from the MySQL database.
 */
require_once __DIR__ . '/includes/functions.php';

// Contact form CSRF token (validated in forms/contact.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Index - ' . setting('site_name', 'DevFolio') . ' Bootstrap Template';
$bodyClass = 'index-page';
$isHome    = true;
$activeNav = 'hero';

// Load all dynamic data up-front
$skills       = get_skills();
$services     = get_services();
$stats        = get_stats();
$categories   = get_categories();
$projects     = get_portfolio_items();
$pricing      = get_pricing();
$faqs         = get_faqs();
$testimonials = get_testimonials();

require __DIR__ . '/includes/header.php';
?>
  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

      <img src="<?= e(setting('hero_image', 'img/hero-img.jpg')) ?>" class="hero-bg-img" alt="" data-aos="fade-in">

      <div class="container d-flex flex-column align-items-center justify-content-center text-center hero-content" data-aos="fade-up" data-aos-delay="100">
        <h2><?= e(setting('hero_title')) ?></h2>
        <p><span class="typed" data-typed-items="<?= e(setting('hero_typed_items')) ?>"></span></p>
        <?php if (setting('hero_subtitle') !== ''): /* optional - managed in admin > Hero */ ?>
        <p style="font-size:18px;margin-top:10px;opacity:.9"><?= e(setting('hero_subtitle')) ?></p>
        <?php endif; ?>
        <?php if (setting('hero_cta_text') !== ''): ?>
        <a href="<?= e(safe_url(setting('hero_cta_url'))) ?>" style="display:inline-block;margin-top:24px;padding:11px 32px;border-radius:50px;background:var(--accent-color,#0d83fd);color:var(--contrast-color,#fff);font-weight:500;text-decoration:none"><?= e(setting('hero_cta_text')) ?></a>
        <?php endif; ?>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">
          <div class="col-md-6">

            <div class="row justify-content-between align-items-center gy-4 about-profile-wrap">
              <div class="col-lg-5 text-center text-lg-start">
                <img src="<?= e(setting('about_image', 'img/profile-img.jpg')) ?>" class="img-fluid about-profile-img" alt="">
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
              <h4><?= e(setting('about_heading', 'About me')) ?></h4>
              <?php foreach (paragraphs(setting('about_text')) as $para): ?>
              <p>
                <?= e($para) ?>
              </p>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Resume Section -->
    <section id="resume" class="resume section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2><?= e(setting('resume_title', 'Resume')) ?></h2>
        <p><?= e(setting('resume_subtitle')) ?></p>
      </div><!-- End Section Title -->

      <?php
      /** Render one resume entry (title, period, subtitle, description, bullet list). */
      $renderResumeItem = static function (array $r, string $extraClass = ''): void { ?>
            <div class="resume-item<?= $extraClass ?>">
              <h4><?= e($r['title']) ?></h4>
              <?php if ($r['period'] !== null && $r['period'] !== ''): ?>
              <h5><?= e($r['period']) ?></h5>
              <?php endif; ?>
              <?php if ($r['subtitle'] !== null && $r['subtitle'] !== ''): ?>
              <p><em><?= e($r['subtitle']) ?></em></p>
              <?php endif; ?>
              <?php if ($r['description'] !== null && $r['description'] !== ''): ?>
              <p><?= e($r['description']) ?></p>
              <?php endif; ?>
              <?php $bul = lines($r['bullets']); if ($bul): ?>
              <ul>
                <?php foreach ($bul as $b): ?>
                <li><?= e($b) ?></li>
                <?php endforeach; ?>
              </ul>
              <?php endif; ?>
            </div><!-- Edn Resume Item -->

      <?php }; ?>
      <div class="container">

        <div class="row">

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <h3 class="resume-title"><?= e(setting('resume_summary_heading', 'Sumary')) ?></h3>
            <?php foreach (get_resume_items('summary') as $r) { $renderResumeItem($r, ' pb-0'); } ?>

            <h3 class="resume-title"><?= e(setting('resume_education_heading', 'Education')) ?></h3>
            <?php foreach (get_resume_items('education') as $r) { $renderResumeItem($r); } ?>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <h3 class="resume-title"><?= e(setting('resume_experience_heading', 'Professional Experience')) ?></h3>
            <?php foreach (get_resume_items('experience') as $r) { $renderResumeItem($r); } ?>
          </div>

        </div>

      </div>

    </section><!-- /Resume Section -->

    <!-- Services Section -->
    <section id="services" class="services section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2><?= e(setting('services_title', 'Services')) ?></h2>
        <p><?= e(setting('services_subtitle')) ?></p>
      </div><!-- End Section Title -->

      <div class="container">

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

      </div>

    </section><!-- /Services Section -->

    <!-- Stats Section -->
    <section id="stats" class="stats section accent-background">

      <img src="<?= e(setting('stats_image', 'img/stats-bg.jpg')) ?>" alt="" data-aos="fade-in">

      <div class="container position-relative" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <?php foreach ($stats as $st): $n = (int) $st['value']; ?>
          <div class="col-6 col-md-6 col-lg-3">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="<?= $n ?>" data-purecounter-duration="0" class="purecounter"><?= $n ?></span>
              <p><?= e($st['label']) ?></p>
            </div>
          </div><!-- End Stats Item -->

          <?php endforeach; ?>
        </div>

      </div>

    </section><!-- /Stats Section -->

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2><?= e(setting('portfolio_title', 'Portfolio')) ?></h2>
        <p><?= e(setting('portfolio_subtitle')) ?></p>
      </div><!-- End Section Title -->

      <div class="container">

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

    <!-- Pricing Section -->
    <section id="pricing" class="pricing section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2><?= e(setting('pricing_title', 'Pricing')) ?></h2>
        <p><?= e(setting('pricing_subtitle')) ?></p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4 gx-lg-5">

          <?php foreach ($pricing as $pl): ?>
          <div class="col-lg-6">
            <div class="pricing-item d-flex justify-content-between">
              <h3><?= e($pl['title']) ?></h3>
              <h4>$<?= number_format((float) $pl['price'], 2) ?></h4>
            </div>
          </div><!-- End Pricing Item -->

          <?php endforeach; ?>
        </div>

      </div>

    </section><!-- /Pricing Section -->

    <!-- Faq Section -->
    <section id="faq" class="faq section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="content px-xl-5">
              <h3><span>Frequently Asked </span><strong>Questions</strong></h3>
              <p>
                <?= e(setting('faq_intro')) ?>
              </p>
            </div>
          </div>

          <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">

            <div class="faq-container">
              <?php foreach ($faqs as $i => $f): ?>
              <div class="faq-item<?= $i === 0 ? ' faq-active' : '' ?>">
                <h3><span class="num"><?= $i + 1 ?>.</span> <span><?= e($f['question']) ?></span></h3>
                <div class="faq-content">
                  <p><?= e($f['answer']) ?></p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <?php endforeach; ?>
            </div>

          </div>
        </div>

      </div>

    </section><!-- /Faq Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section accent-background">

      <img src="<?= e(setting('testimonials_bg', 'img/testimonials-bg.jpg')) ?>" class="testimonials-bg" alt="">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              }
            }
          </script>
          <div class="swiper-wrapper">

            <?php foreach ($testimonials as $t): $stars = max(0, min(5, (int) $t['rating'])); ?>
            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="<?= e($t['image_path']) ?>" class="testimonial-img" alt="">
                <h3><?= e($t['name']) ?></h3>
                <h4><?= e($t['position']) ?></h4>
                <div class="stars">
                  <?= str_repeat('<i class="bi bi-star-fill"></i>', $stars) ?>

                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span><?= e($t['quote']) ?></span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <?php endforeach; ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2><?= e(setting('contact_title', 'Contact')) ?></h2>
        <p><?= e(setting('contact_subtitle')) ?></p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="info-wrap" data-aos="fade-up" data-aos-delay="200">
          <div class="row gy-5">

            <div class="col-lg-4">
              <div class="info-item d-flex align-items-center">
                <i class="bi bi-geo-alt flex-shrink-0"></i>
                <div>
                  <h3>Address</h3>
                  <p><?= e(setting('contact_address')) ?></p>
                </div>
              </div>
            </div><!-- End Info Item -->

            <div class="col-lg-4">
              <div class="info-item d-flex align-items-center">
                <i class="bi bi-telephone flex-shrink-0"></i>
                <div>
                  <h3>Call Us</h3>
                  <p><?= e(setting('contact_phone')) ?></p>
                </div>
              </div>
            </div><!-- End Info Item -->

            <div class="col-lg-4">
              <div class="info-item d-flex align-items-center">
                <i class="bi bi-envelope flex-shrink-0"></i>
                <div>
                  <h3>Email Us</h3>
                  <p><?= e(setting('contact_email')) ?></p>
                </div>
              </div>
            </div><!-- End Info Item -->

          </div>
        </div>

        <!-- Posts via AJAX (js/main.js) to forms/contact.php, which validates and stores in MySQL -->
        <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="300">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <!-- Honeypot: real visitors never see/fill this; bots usually do -->
          <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;">
          <div class="row gy-4">

            <div class="col-md-6">
              <input type="text" name="name" class="form-control" placeholder="Your Name" maxlength="100" required>
            </div>

            <div class="col-md-6 ">
              <input type="email" class="form-control" name="email" placeholder="Your Email" maxlength="150" required>
            </div>

            <div class="col-md-12">
              <input type="text" class="form-control" name="subject" placeholder="Subject" maxlength="200" required>
            </div>

            <div class="col-md-12">
              <textarea class="form-control" name="message" rows="6" placeholder="Message" maxlength="5000" required></textarea>
            </div>

            <div class="col-md-12 text-center">
              <div class="loading">Loading</div>
              <div class="error-message"></div>
              <div class="sent-message">Your message has been sent. Thank you!</div>

              <button type="submit">Send Message</button>
            </div>

          </div>
        </form><!-- End Contact Form -->

      </div>

    </section><!-- /Contact Section -->

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
