<?php
/** admin/about.php - About details + profile image, and CRUD for skills / progress bars. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('about', 'About section', 'bi-person-badge', [
    'about_heading' => ['label' => 'Heading', 'required' => true, 'max' => 100, 'col' => 6],
    'about_name'    => ['label' => 'Name', 'required' => true, 'max' => 100, 'col' => 6],
    'about_profile' => ['label' => 'Profile / job title', 'max' => 100, 'col' => 6],
    'about_email'   => ['label' => 'Email', 'type' => 'email', 'max' => 150, 'col' => 6],
    'about_phone'   => ['label' => 'Phone', 'max' => 50, 'col' => 6],
    'about_text'    => ['label' => 'Bio', 'type' => 'textarea', 'rows' => 8, 'max' => 5000, 'help' => 'Separate paragraphs with a blank line.'],
    'about_image'   => ['label' => 'Profile image', 'type' => 'image', 'help' => 'JPG, PNG, WEBP or GIF, max 3 MB.'],
]);
$form->handle();

$skills = new CrudPage('skills', 'Skills (progress bars)', 'Skill', 'bi-sliders', [
    'name'       => ['label' => 'Skill name', 'required' => true, 'max' => 100, 'col' => 6],
    'percentage' => ['label' => 'Level (0–100 %)', 'type' => 'number', 'required' => true, 'min' => 0, 'maxval' => 100, 'default' => 80, 'col' => 6],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6, 'help' => 'Lower numbers appear first.'],
    'is_active'  => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['name' => 'Skill', 'percentage' => 'Level %', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
]);
$skills->handle();

$pageTitle = 'About & Skills';
$activeMenu = 'about';
require __DIR__ . '/partials/header.php';
$form->render();
$skills->render();
require __DIR__ . '/partials/footer.php';
