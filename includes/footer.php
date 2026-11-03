<?php
/**
 * includes/footer.php - footer, scroll-top, preloader and JS bundle.
 */
require_once __DIR__ . '/functions.php';
$siteName = setting('site_name', 'DevFolio');
?>
  <footer id="footer" class="footer accent-background">

    <div class="container">
      <div class="social-links d-flex justify-content-center">
        <?php foreach (get_social_links() as $s): ?>
        <a href="<?= e(safe_url($s['url'])) ?>"><i class="bi <?= e($s['icon']) ?>"></i></a>
        <?php endforeach; ?>
      </div>
      <div class="credits">
        Copyright &amp; Developed by <a href="https://cognovait.com/" target="_blank" rel="noopener">Cognovait</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/aos.js"></script>
  <script src="js/typed.umd.js"></script>
  <script src="js/waypoints.js"></script>
  <script src="js/purecounter_vanilla.js"></script>
  <script src="js/glightbox.min.js"></script>
  <script src="js/imagesloaded.pkgd.min.js"></script>
  <script src="js/isotope.pkgd.min.js"></script>
  <script src="js/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="js/main.js"></script>

</body>

</html>
