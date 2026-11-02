<?php
/** admin/hero.php - Hero section: title, subtitle, typing text, background image, CTA button. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('hero', 'Hero section', 'bi-stars', [
    'hero_title'       => ['label' => 'Main title', 'required' => true, 'max' => 150, 'help' => 'Example: I am Morgan Freeman'],
    'hero_typed_items' => ['label' => 'Rotating typing text', 'required' => true, 'max' => 500,
                           'help' => 'Comma-separated list shown with the typing animation, e.g. Designer, Developer, Freelancer'],
    'hero_subtitle'    => ['label' => 'Subtitle (optional)', 'max' => 255, 'help' => 'Small extra line under the typing text. Leave empty to hide it (original design).'],
    'hero_cta_text'    => ['label' => 'Button text (optional)', 'max' => 40, 'col' => 6, 'help' => 'Leave empty to hide the button (original design).'],
    'hero_cta_url'     => ['label' => 'Button link', 'type' => 'url', 'max' => 255, 'col' => 6, 'placeholder' => '#contact or https://…'],
    'hero_image'       => ['label' => 'Background image', 'type' => 'image', 'help' => 'JPG, PNG, WEBP or GIF, max 3 MB. Wide images (1920×1080) look best.'],
]);
$form->handle();

$pageTitle = 'Hero Section';
$activeMenu = 'hero';
require __DIR__ . '/partials/header.php';
$form->render();
require __DIR__ . '/partials/footer.php';
