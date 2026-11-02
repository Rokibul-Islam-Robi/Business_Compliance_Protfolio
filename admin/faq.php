<?php
/** admin/faq.php - FAQ intro text + CRUD for questions. */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('faq', 'FAQ intro text', 'bi-question-circle', [
    'faq_intro' => ['label' => 'Intro paragraph (left column)', 'type' => 'textarea', 'rows' => 4, 'max' => 1000],
]);
$form->handle();

$faq = new CrudPage('faqs', 'Questions & answers', 'Question', 'bi-question-circle', [
    'question'   => ['label' => 'Question', 'required' => true, 'max' => 255],
    'answer'     => ['label' => 'Answer', 'type' => 'textarea', 'rows' => 5, 'required' => true, 'max' => 3000],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 6],
    'is_active'  => ['label' => 'Visible on website', 'type' => 'checkbox', 'default' => 1, 'col' => 6],
], [
    'columns'    => ['question' => 'Question', 'answer' => 'Answer', 'sort_order' => 'Order', 'is_active' => 'Status'],
    'modal_size' => '',
]);
$faq->handle();

$pageTitle = 'FAQ';
$activeMenu = 'faq';
require __DIR__ . '/partials/header.php';
$form->render();
$faq->render();
require __DIR__ . '/partials/footer.php';
