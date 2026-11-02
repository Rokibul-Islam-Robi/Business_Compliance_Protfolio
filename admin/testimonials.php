<?php
/** admin/testimonials.php - Section background + CRUD for client reviews. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('testimonials', 'Testimonials background', 'bi-image', [
    'testimonials_bg' => ['label' => 'Background image', 'type' => 'image', 'help' => 'JPG, PNG, WEBP or GIF, max 3 MB.'],
]);
$form->handle();

$testimonials = new CrudPage('testimonials', 'Client reviews', 'Testimonial', 'bi-chat-quote', [
    'name'       => ['label' => 'Client name', 'required' => true, 'max' => 100, 'col' => 6],
    'position'   => ['label' => 'Designation', 'required' => true, 'max' => 100, 'col' => 6, 'placeholder' => 'CEO & Founder'],
    'image_path' => ['label' => 'Client photo', 'type' => 'image', 'required' => true, 'help' => 'Square photos look best (JPG/PNG/WEBP/GIF, max 3 MB).'],
    'rating'     => ['label' => 'Rating', 'type' => 'select', 'required' => true, 'default' => '5', 'col' => 4,
                     'options' => ['5' => '5 stars', '4' => '4 stars', '3' => '3 stars', '2' => '2 stars', '1' => '1 star']],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 4],
    'is_active'  => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 4],
    'quote'      => ['label' => 'Review text', 'type' => 'textarea', 'rows' => 5, 'required' => true, 'max' => 1500],
], [
    'columns'    => ['image_path' => 'Photo', 'name' => 'Name', 'position' => 'Designation', 'rating' => 'Rating', 'is_active' => 'Status'],
]);
$testimonials->handle();

$pageTitle = 'Testimonials';
$activeMenu = 'testimonials';
require __DIR__ . '/partials/header.php';
$testimonials->render();
$form->render();
require __DIR__ . '/partials/footer.php';
