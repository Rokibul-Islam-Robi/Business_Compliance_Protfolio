<?php
/** admin/contact.php - Contact details (shown on the site) + CRUD for social links. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('contact', 'Contact information', 'bi-geo-alt', [
    'contact_title'    => ['label' => 'Section title', 'required' => true, 'max' => 100, 'col' => 6],
    'contact_subtitle' => ['label' => 'Section subtitle', 'max' => 255, 'col' => 6],
    'contact_address'  => ['label' => 'Address', 'max' => 255],
    'contact_phone'    => ['label' => 'Phone', 'max' => 50, 'col' => 6],
    'contact_email'    => ['label' => 'Email', 'type' => 'email', 'max' => 150, 'col' => 6],
]);
$form->handle();

$social = new CrudPage('social_links', 'Social links (footer)', 'Social link', 'bi-share', [
    'icon'       => ['label' => 'Bootstrap icon class', 'type' => 'icon', 'required' => true, 'col' => 6,
                     'help' => 'e.g. bi-twitter-x, bi-facebook, bi-instagram, bi-linkedin, bi-github, bi-youtube'],
    'url'        => ['label' => 'URL', 'type' => 'url', 'required' => true, 'col' => 6, 'placeholder' => 'https://…'],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'  => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['icon' => 'Icon', 'url' => 'URL', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
]);
$social->handle();

$pageTitle = 'Contact & Social';
$activeMenu = 'contact';
require __DIR__ . '/partials/header.php';
$form->render();
$social->render();
require __DIR__ . '/partials/footer.php';
