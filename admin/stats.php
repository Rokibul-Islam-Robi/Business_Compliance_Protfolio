<?php
/** admin/stats.php - Counters section: background image + CRUD for numbers. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('stats', 'Counters background', 'bi-image', [
    'stats_image' => ['label' => 'Background image', 'type' => 'image', 'help' => 'JPG, PNG, WEBP or GIF, max 3 MB.'],
]);
$form->handle();

$stats = new CrudPage('stats', 'Counters', 'Counter', 'bi-bar-chart-line', [
    'label'      => ['label' => 'Label', 'required' => true, 'max' => 100, 'col' => 6, 'placeholder' => 'Happy clients'],
    'value'      => ['label' => 'Number', 'type' => 'number', 'required' => true, 'min' => 0, 'maxval' => 2000000000, 'col' => 6],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'  => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['label' => 'Label', 'value' => 'Number', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
]);
$stats->handle();

$pageTitle = 'Counters / Stats';
$activeMenu = 'stats';
require __DIR__ . '/partials/header.php';
$stats->render();
$form->render();
require __DIR__ . '/partials/footer.php';
