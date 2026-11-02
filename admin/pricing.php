<?php
/** admin/pricing.php - Pricing heading + CRUD for price rows. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('pricing', 'Pricing section heading', 'bi-cash-coin', [
    'pricing_title'    => ['label' => 'Section title', 'required' => true, 'max' => 100, 'col' => 6],
    'pricing_subtitle' => ['label' => 'Section subtitle', 'max' => 255, 'col' => 6],
]);
$form->handle();

$pricing = new CrudPage('pricing_plans', 'Price list', 'Price', 'bi-cash-coin', [
    'title'      => ['label' => 'Service name', 'required' => true, 'max' => 150, 'col' => 6],
    'price'      => ['label' => 'Price ($)', 'type' => 'decimal', 'required' => true, 'col' => 6],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'  => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['title' => 'Service', 'price' => 'Price', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
]);
$pricing->handle();

$pageTitle = 'Pricing';
$activeMenu = 'pricing';
require __DIR__ . '/partials/header.php';
$form->render();
$pricing->render();
require __DIR__ . '/partials/footer.php';
