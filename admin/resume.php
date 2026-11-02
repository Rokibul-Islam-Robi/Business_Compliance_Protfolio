<?php
/** admin/resume.php - Resume headings + CRUD for summary / education / experience entries. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('resume', 'Resume section headings', 'bi-file-earmark-person', [
    'resume_title'              => ['label' => 'Section title', 'required' => true, 'max' => 100, 'col' => 6],
    'resume_subtitle'           => ['label' => 'Section subtitle', 'max' => 255, 'col' => 6],
    'resume_summary_heading'    => ['label' => 'Left column – summary heading', 'max' => 100, 'col' => 4],
    'resume_education_heading'  => ['label' => 'Left column – education heading', 'max' => 100, 'col' => 4],
    'resume_experience_heading' => ['label' => 'Right column – experience heading', 'max' => 100, 'col' => 4],
]);
$form->handle();

$items = new CrudPage('resume_items', 'Resume entries', 'Entry', 'bi-file-earmark-person', [
    'section'     => ['label' => 'Block', 'type' => 'select', 'required' => true, 'col' => 4,
                      'options' => ['summary' => 'Summary', 'education' => 'Education', 'experience' => 'Professional experience']],
    'title'       => ['label' => 'Title / name', 'required' => true, 'max' => 255, 'col' => 8],
    'period'      => ['label' => 'Period', 'nullable' => true, 'max' => 100, 'col' => 4, 'placeholder' => '2019 - Present'],
    'subtitle'    => ['label' => 'Italic line (institution / company / summary)', 'type' => 'textarea', 'rows' => 2, 'nullable' => true, 'col' => 8],
    'description' => ['label' => 'Description', 'type' => 'textarea', 'rows' => 3, 'nullable' => true],
    'bullets'     => ['label' => 'Bullet points', 'type' => 'textarea', 'rows' => 4, 'nullable' => true, 'help' => 'One bullet per line.'],
    'sort_order'  => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'   => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'order'   => "FIELD(section,'summary','education','experience'), sort_order, id",
    'columns' => ['section' => 'Block', 'title' => 'Title', 'period' => 'Period', 'sort_order' => 'Order', 'is_active' => 'Status'],
]);
$items->handle();

$pageTitle = 'Resume';
$activeMenu = 'resume';
require __DIR__ . '/partials/header.php';
$form->render();
$items->render();
require __DIR__ . '/partials/footer.php';
