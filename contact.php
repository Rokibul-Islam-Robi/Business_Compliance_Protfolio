<?php
/**
 * contact.php - Contact page (info cards + message form).
 */
require_once __DIR__ . '/includes/functions.php';

// Contact form CSRF token (validated in forms/contact.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle   = 'Contact - ' . setting('site_name', 'DevFolio') . ' Bootstrap Template';
$bodyClass   = 'contact-page inner-page';
$isHome      = false;
$activeNav   = 'contact';
$bannerTitle = 'Contact';

require __DIR__ . '/includes/header.php';
?>
  <main class="main">

    <?php require __DIR__ . '/includes/breadcrumb.php'; ?>

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <div class="container">
       <div class="inner-card" data-aos="fade-up" data-aos-delay="100">
        <?php inner_intro(setting('contact_title', 'Contact'), setting('contact_subtitle'), $bannerTitle); ?>

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
