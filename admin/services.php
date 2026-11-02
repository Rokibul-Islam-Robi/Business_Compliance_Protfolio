<?php
/** admin/services.php - Services heading + CRUD for service cards. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('services', 'Services section heading', 'bi-briefcase', [
    'services_title'    => ['label' => 'Section title', 'required' => true, 'max' => 100, 'col' => 6],
    'services_subtitle' => ['label' => 'Section subtitle', 'max' => 255, 'col' => 6],
]);
$form->handle();

$services = new CrudPage('services', 'Services', 'Service', 'bi-briefcase', [
    'icon'        => ['label' => 'Bootstrap icon class', 'type' => 'icon', 'required' => true, 'col' => 6,
                      'help' => 'Browse icons at icons.getbootstrap.com and enter the class name, e.g. bi-activity'],
    'title'       => ['label' => 'Title', 'required' => true, 'max' => 150, 'col' => 6],
    'description' => ['label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'required' => true, 'max' => 1000],
    'sort_order'  => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'   => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['icon' => 'Icon', 'title' => 'Title', 'description' => 'Description', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
]);
$services->handle();

$pageTitle = 'Services';
$activeMenu = 'services';
require __DIR__ . '/partials/header.php';
$form->render();
$services->render();
require __DIR__ . '/partials/footer.php';
