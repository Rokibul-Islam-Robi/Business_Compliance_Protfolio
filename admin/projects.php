<?php
/**
 * admin/projects.php - Portfolio section: heading + full CRUD for projects
 * (cover image, gallery images, category, client, URL, date, description, linked testimonial).
 */
require __DIR__ . '/bootstrap.php';

$form = new SettingsForm('portfolio', 'Portfolio section heading', 'bi-images', [
    'portfolio_title'    => ['label' => 'Section title', 'required' => true, 'max' => 100, 'col' => 6],
    'portfolio_subtitle' => ['label' => 'Section subtitle', 'max' => 255, 'col' => 6],
]);
$form->handle();

// Select options loaded from the DB
$catOptions = [];
foreach (Database::fetchAll('SELECT id, name FROM portfolio_categories ORDER BY sort_order, id') as $c) {
    $catOptions[(string) $c['id']] = $c['name'];
}
$testiOptions = ['' => '— none —'];
foreach (Database::fetchAll('SELECT id, name, position FROM testimonials ORDER BY sort_order, id') as $t) {
    $testiOptions[(string) $t['id']] = $t['name'] . ' (' . $t['position'] . ')';
}

const MAX_GALLERY_UPLOADS = 10;

$projects = new CrudPage('portfolio_items', 'Projects', 'Project', 'bi-images', [
    'title'             => ['label' => 'Project title', 'required' => true, 'max' => 150, 'col' => 8],
    'category_id'       => ['label' => 'Category', 'type' => 'select', 'required' => true, 'options' => $catOptions, 'col' => 4],
    'short_description' => ['label' => 'Short description (grid hover text)', 'required' => true, 'max' => 255],
    'image_path'        => ['label' => 'Cover image', 'type' => 'image', 'required' => true, 'help' => 'Used in the portfolio grid. JPG/PNG/WEBP/GIF, max 3 MB.'],
    'details_title'     => ['label' => 'Details page heading', 'nullable' => true, 'max' => 255, 'help' => 'Falls back to the project title if empty.'],
    'details_text'      => ['label' => 'Description', 'type' => 'textarea', 'rows' => 7, 'nullable' => true, 'max' => 10000, 'help' => 'Separate paragraphs with a blank line.'],
    'client'            => ['label' => 'Client name', 'nullable' => true, 'max' => 150, 'col' => 4],
    'project_date'      => ['label' => 'Project date', 'type' => 'date', 'nullable' => true, 'col' => 4],
    'project_url'       => ['label' => 'Project URL', 'type' => 'url', 'nullable' => true, 'col' => 4, 'placeholder' => 'https://…'],
    'testimonial_id'    => ['label' => 'Linked testimonial', 'type' => 'select', 'nullable' => true, 'options' => $testiOptions, 'col' => 6],
    'sort_order'        => ['label' => 'Sort order', 'type' => 'number', 'default' => 0, 'col' => 3],
    'is_active'         => ['label' => 'Visible', 'type' => 'checkbox', 'default' => 1, 'col' => 3],
], [
    'order'      => 'sort_order, id',
    'modal_size' => 'modal-xl',
    'columns'    => ['image_path' => 'Cover', 'title' => 'Title', 'category_id' => 'Category', 'client' => 'Client', 'is_active' => 'Status'],

    // add the gallery list to every row so the edit modal can show it
    'decorate' => static function (array $rows): array {
        $byItem = [];
        foreach (Database::fetchAll('SELECT id, portfolio_item_id, image_path FROM portfolio_images ORDER BY sort_order, id') as $g) {
            $byItem[$g['portfolio_item_id']][] = ['id' => (int) $g['id'], 'path' => $g['image_path']];
        }
        foreach ($rows as &$r) {
            $r['gallery'] = $byItem[$r['id']] ?? [];
        }
        return $rows;
    },

    // extra form area: gallery uploads + removal of existing images
    'modal_extra' => static function (): void { ?>
        <hr>
        <h6 class="mb-2"><i class="bi bi-collection me-1"></i>Gallery images (details page slider)</h6>
        <div id="gallery-existing" class="d-flex flex-wrap gap-2 mb-3"></div>
        <input class="form-control" type="file" name="gallery[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
        <div class="form-text">Optional – up to <?= MAX_GALLERY_UPLOADS ?> images per save. Tick “Remove” on an existing image to delete it. If no gallery image exists, the cover image is shown.</div>
    <?php },

    // delete gallery files from disk before the DB rows cascade-delete
    'before_delete' => static function (int $id): void {
        foreach (Database::fetchAll('SELECT image_path FROM portfolio_images WHERE portfolio_item_id = :id', [':id' => $id]) as $g) {
            Uploader::delete($g['image_path']);
        }
    },

    // process gallery after the project row has been saved
    'after_save' => static function (int $id, array $post, array $files): void {
        // 1) removals (ids validated as ints, bound as parameters, restricted to this project)
        $remove = array_filter(array_map('intval', (array) ($post['remove_gallery'] ?? [])), static fn($v) => $v > 0);
        foreach ($remove as $imgId) {
            $row = Database::fetchOne(
                'SELECT image_path FROM portfolio_images WHERE id = :i AND portfolio_item_id = :p',
                [':i' => $imgId, ':p' => $id]
            );
            if ($row) {
                Database::execute('DELETE FROM portfolio_images WHERE id = :i AND portfolio_item_id = :p', [':i' => $imgId, ':p' => $id]);
                Uploader::delete($row['image_path']);
            }
        }
        // 2) new uploads
        $order = (int) Database::fetchValue('SELECT COALESCE(MAX(sort_order), 0) FROM portfolio_images WHERE portfolio_item_id = :p', [':p' => $id]);
        $count = 0;
        foreach (Uploader::normalizeMultiple($files['gallery'] ?? null) as $file) {
            if (!Uploader::hasFile($file)) {
                continue;
            }
            if (++$count > MAX_GALLERY_UPLOADS) {
                Flash::add('warning', 'Only the first ' . MAX_GALLERY_UPLOADS . ' gallery images were uploaded.');
                break;
            }
            try {
                $path = Uploader::saveImage($file);
                Database::execute(
                    'INSERT INTO portfolio_images (portfolio_item_id, image_path, sort_order) VALUES (:p, :i, :o)',
                    [':p' => $id, ':i' => $path, ':o' => ++$order]
                );
            } catch (RuntimeException $e) {
                Flash::add('warning', 'Gallery image “' . mb_substr((string) $file['name'], 0, 60) . '”: ' . $e->getMessage());
            }
        }
    },
]);
$projects->handle();

// Page-specific JS: render the existing gallery inside the edit modal
$pageScripts = <<<'HTML'
<script>
(function () {
  var form = document.querySelector('#modal-portfolio_items [data-crud-form]');
  if (!form) { return; }
  form.addEventListener('crud:fill', function (ev) {
    var box = document.getElementById('gallery-existing');
    box.innerHTML = '';
    var row = ev.detail;
    if (!row || !row.gallery) { return; }
    row.gallery.forEach(function (g) {
      var wrap = document.createElement('div'); wrap.className = 'gallery-item';
      var img = document.createElement('img'); img.src = '../' + g.path; img.alt = '';
      var chk = document.createElement('div'); chk.className = 'form-check';
      var inp = document.createElement('input'); inp.type = 'checkbox'; inp.className = 'form-check-input';
      inp.name = 'remove_gallery[]'; inp.value = g.id; inp.id = 'rg' + g.id;
      var lab = document.createElement('label'); lab.className = 'form-check-label'; lab.htmlFor = inp.id; lab.textContent = 'Remove';
      chk.appendChild(inp); chk.appendChild(lab); wrap.appendChild(img); wrap.appendChild(chk); box.appendChild(wrap);
    });
  });
})();
</script>
HTML;

$pageTitle = 'Projects';
$activeMenu = 'projects';
require __DIR__ . '/partials/header.php';
$projects->render();
$form->render();
require __DIR__ . '/partials/footer.php';
