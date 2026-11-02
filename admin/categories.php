<?php
/** admin/categories.php - Portfolio filter categories (App, Product, Branding ...). */
require __DIR__ . '/bootstrap.php';

$cats = new CrudPage('portfolio_categories', 'Project categories', 'Category', 'bi-tags', [
    'name'       => ['label' => 'Name', 'required' => true, 'max' => 100, 'col' => 6],
    'slug'       => ['label' => 'Slug', 'max' => 100, 'col' => 6, 'help' => 'Auto-generated from the name if left empty.'],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'  => ['label' => 'Show as filter', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['name' => 'Name', 'slug' => 'Slug', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
    // build a clean slug (lowercase letters, digits, hyphens)
    'prepare'    => static function (array $data, ?array $existing): array {
        $slug = slugify((string) ($data['slug'] ?: $data['name']));
        $data['slug'] = $slug !== '' ? $slug : 'category';
        return $data;
    },
]);
$cats->handle();

$pageTitle = 'Project Categories';
$activeMenu = 'categories';
require __DIR__ . '/partials/header.php';
?>
<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>A category cannot be deleted while projects still use it. Reassign or delete those projects first.</div>
<?php
$cats->render();
require __DIR__ . '/partials/footer.php';
