<?php
/** admin/settings.php - Site-wide settings. */
require __DIR__ . '/bootstrap.php';

// Handle Logo Upload / Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    if ($action === 'upload_logo') {
        Csrf::require();
        $file = $_FILES['site_logo'] ?? null;
        if (!Uploader::hasFile($file)) {
            Flash::add('danger', 'Please choose a logo file to upload.');
            redirect('settings.php');
        }

        try {
            $oldLogo = setting('site_logo', '');
            $newLogo = Uploader::saveLogo($file);

            Database::execute(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                [':k' => 'site_logo', ':v' => $newLogo]
            );

            if ($oldLogo !== '' && $oldLogo !== $newLogo) {
                Uploader::delete($oldLogo);
            }

            Flash::add('success', 'Website logo updated successfully.');
        } catch (RuntimeException $e) {
            Flash::add('danger', $e->getMessage());
        }
        redirect('settings.php');
    }

    if ($action === 'delete_logo') {
        Csrf::require();
        $oldLogo = setting('site_logo', '');
        if ($oldLogo !== '') {
            Uploader::delete($oldLogo);
            Database::execute(
                'UPDATE site_settings SET setting_value = "" WHERE setting_key = "site_logo"'
            );
        }
        Flash::add('success', 'Website logo deleted. The site is now using the text logo fallback.');
        redirect('settings.php');
    }
}

$form = new SettingsForm('general', 'General settings', 'bi-gear', [
    'site_name'         => ['label' => 'Site name (fallback logo & footer)', 'required' => true, 'max' => 60],
    'meta_description'  => ['label' => 'Meta description (SEO)', 'type' => 'textarea', 'rows' => 3, 'max' => 300],
    'meta_keywords'     => ['label' => 'Meta keywords (SEO)', 'max' => 300],
    'page_banner_image' => ['label' => 'Page banner image (About / Services / Portfolio / Contact)', 'type' => 'image',
                            'help' => 'Background of the breadcrumb banner on inner pages. Wide images (1920x600) look best.'],
]);
$form->handle();

$currentLogo = setting('site_logo', '');
$siteName    = setting('site_name', 'DevFolio');

$pageTitle = 'Site Settings';
$activeMenu = 'settings';
require __DIR__ . '/partials/header.php';
?>

<!-- Logo Management Card -->
<div class="card admin-card mb-4">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <i class="bi bi-image me-2"></i><h2 class="h6 mb-0">Website Logo</h2>
    </div>
    <?php if (!empty($currentLogo) && is_file(ROOT_PATH . '/' . $currentLogo)): ?>
      <span class="badge bg-success">Active Logo</span>
    <?php else: ?>
      <span class="badge bg-secondary">Text Fallback Active</span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <div class="row align-items-center g-4">
      <div class="col-md-4 text-center">
        <label class="form-label d-block text-muted small text-uppercase fw-semibold">Current / Live Preview</label>
        <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-center" style="min-height: 130px;">
          <?php if (!empty($currentLogo) && is_file(ROOT_PATH . '/' . $currentLogo)): ?>
            <img id="logoPreview" src="<?= e(admin_img($currentLogo)) ?>" alt="Logo Preview" style="max-height: 70px; max-width: 100%; object-fit: contain;">
            <div id="noLogoPlaceholder" class="text-muted small d-none">
              <i class="bi bi-image fs-3 d-block mb-1 text-secondary"></i>
              No logo uploaded<br><span class="badge bg-secondary">Text Fallback: <?= e($siteName) ?></span>
            </div>
          <?php else: ?>
            <img id="logoPreview" src="" alt="Logo Preview" style="max-height: 70px; max-width: 100%; object-fit: contain; display: none;">
            <div id="noLogoPlaceholder" class="text-muted small">
              <i class="bi bi-image fs-3 d-block mb-1 text-secondary"></i>
              No logo uploaded<br><span class="badge bg-secondary">Text Fallback: <?= e($siteName) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-8">
        <form method="post" enctype="multipart/form-data" class="mb-3">
          <?= Csrf::field() ?>
          <input type="hidden" name="_action" value="upload_logo">
          
          <label class="form-label fw-semibold" for="logoInput">Upload Website Logo</label>
          <div class="input-group mb-2">
            <input type="file" class="form-control" id="logoInput" name="site_logo" accept=".png,.jpg,.jpeg,.svg,image/png,image/jpeg,image/svg+xml" required>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-upload me-1"></i>Upload Logo
            </button>
          </div>
          <div class="form-text">
            Allowed formats: <strong>PNG, JPG, JPEG, SVG</strong>. Maximum file size: <strong>2 MB</strong>.
          </div>
        </form>

        <?php if (!empty($currentLogo) && is_file(ROOT_PATH . '/' . $currentLogo)): ?>
        <form method="post" onsubmit="return confirm('Are you sure you want to delete the website logo? The header will fallback to the text site name.');">
          <?= Csrf::field() ?>
          <input type="hidden" name="_action" value="delete_logo">
          <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-trash3 me-1"></i>Delete Current Logo
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var logoInput = document.getElementById('logoInput');
  if (!logoInput) return;

  logoInput.addEventListener('change', function (e) {
    var file = e.target.files && e.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
      alert('File size exceeds the 2 MB limit. Please select a smaller file.');
      this.value = '';
      return;
    }

    var reader = new FileReader();
    reader.onload = function (ev) {
      var preview = document.getElementById('logoPreview');
      var placeholder = document.getElementById('noLogoPlaceholder');
      if (preview) {
        preview.src = ev.target.result;
        preview.style.display = 'block';
      }
      if (placeholder) {
        placeholder.style.display = 'none';
      }
    };
    reader.readAsDataURL(file);
  });
});
</script>

<?php
$form->render();
require __DIR__ . '/partials/footer.php';
