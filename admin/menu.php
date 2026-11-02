<?php
/** admin/menu.php - Main website navigation items (create / edit / delete). */
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';

// Reject invalid URLs early, with a clear message, before CrudPage saves them.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_crud'] ?? '') === 'nav_menu' && ($_POST['_action'] ?? '') === 'save') {
    $rawUrl = trim((string) ($_POST['url'] ?? ''));
    if ($rawUrl !== '' && $rawUrl !== '#' && nav_url($rawUrl) === '#') {
        Flash::add('danger', 'URL is invalid. Use a page link (about.php), an #anchor, or a full http(s):// address.');
        redirect('menu.php');
    }
}

$navPage = new CrudPage('nav_menu', 'Navigation menu', 'Menu item', 'bi-menu-button-wide', [
    'label'      => ['label' => 'Label', 'required' => true, 'max' => 50, 'col' => 6],
    'url'        => ['label' => 'URL', 'required' => true, 'max' => 255, 'col' => 6,
                     'placeholder' => 'about.php, #contact or https://…',
                     'help' => 'Relative page (about.php), in-page anchor (#contact) or full http(s) address.'],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'  => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['label' => 'Label', 'url' => 'URL', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
    // normalise the URL one last time (defence in depth)
    'prepare'    => static function (array $data, ?array $existing): array {
        $data['url'] = nav_url((string) $data['url']);
        return $data;
    },
]);
$navPage->handle();

$pageTitle = 'Navigation Menu';
$activeMenu = 'menu';
require __DIR__ . '/partials/header.php';

$siteLogo = setting('site_logo', '');
$siteName = setting('site_name', 'DevFolio');
?>
<div class="card admin-card mb-3">
  <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
      <span class="text-muted small fw-semibold text-uppercase">Website Logo:</span>
      <?php if (!empty($siteLogo) && is_file(ROOT_PATH . '/' . $siteLogo)): ?>
        <img src="<?= e(admin_img($siteLogo)) ?>" alt="Logo Preview" style="max-height:28px;max-width:120px;object-fit:contain;">
        <span class="badge bg-success">Active Image Logo</span>
      <?php else: ?>
        <strong class="text-primary"><?= e($siteName) ?></strong>
        <span class="badge bg-secondary">Text Fallback Active</span>
      <?php endif; ?>
    </div>
    <a href="settings.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-image me-1"></i>Upload / Manage Logo</a>
  </div>
</div>
<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>These items appear in the website header. Use the sort order to rearrange them; deactivate or delete an item to remove it from the site.</div>
<?php
$navPage->render();
require __DIR__ . '/partials/footer.php';
