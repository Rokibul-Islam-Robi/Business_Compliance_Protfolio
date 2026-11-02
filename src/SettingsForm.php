<?php
/**
 * SettingsForm - edits a group of keys of the `site_settings` key/value table
 * (hero text, about details, section titles, contact info ...).
 * Supports text, textarea, url, email and image fields.
 */

declare(strict_types=1);

final class SettingsForm
{
    private array $values = [];

    /** @param array<string,array> $fields setting_key => definition */
    public function __construct(
        private string $id,
        private string $title,
        private string $icon,
        private array $fields
    ) {
        foreach (Database::fetchAll('SELECT setting_key, setting_value FROM site_settings') as $r) {
            $this->values[$r['setting_key']] = (string) $r['setting_value'];
        }
    }

    /** Process a submitted form (call BEFORE any output). */
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['_form'] ?? '') !== $this->id) {
            return;
        }
        Csrf::require();

        $new = [];
        $errors = [];
        foreach ($this->fields as $key => $f) {
            if (($f['type'] ?? 'text') === 'image') {
                continue;
            }
            $raw = $_POST[$key] ?? '';
            $res = Crud::clean($f + ['label' => $key], is_string($raw) ? $raw : '');
            if ($res['error'] !== null) {
                $errors[] = $res['error'];
            } else {
                $new[$key] = (string) $res['value'];
            }
        }

        $uploaded = [];
        $replaced = [];
        if (!$errors) {
            foreach ($this->fields as $key => $f) {
                if (($f['type'] ?? '') !== 'image') {
                    continue;
                }
                $file = $_FILES[$key] ?? null;
                if (Uploader::hasFile($file)) {
                    try {
                        $path = Uploader::saveImage($file);
                        $new[$key] = $path;
                        $uploaded[] = $path;
                        $replaced[] = $this->values[$key] ?? '';
                    } catch (RuntimeException $e) {
                        $errors[] = ($f['label'] ?? $key) . ': ' . $e->getMessage();
                    }
                }
            }
        }

        if ($errors) {
            array_map([Uploader::class, 'delete'], $uploaded);
            foreach ($errors as $err) {
                Flash::add('danger', $err);
            }
            return;   // re-render page with the alerts
        }

        foreach ($new as $key => $val) {
            Database::execute(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                [':k' => $key, ':v' => $val]
            );
        }
        array_map([Uploader::class, 'delete'], $replaced);
        Flash::add('success', $this->title . ' saved successfully.');
        redirect(basename($_SERVER['SCRIPT_NAME']));
    }

    /** Render the card with the form. */
    public function render(): void
    {
        ?>
        <div class="card admin-card mb-4">
          <div class="card-header d-flex align-items-center">
            <i class="bi <?= e($this->icon) ?> me-2"></i><h2 class="h6 mb-0"><?= e($this->title) ?></h2>
          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data" autocomplete="off">
              <?= Csrf::field() ?>
              <input type="hidden" name="_form" value="<?= e($this->id) ?>">
              <div class="row g-3">
                <?php foreach ($this->fields as $key => $f):
                    $type = $f['type'] ?? 'text';
                    $val  = $_POST[$key] ?? ($this->values[$key] ?? '');
                    $fid  = 'sf-' . $this->id . '-' . $key;
                    $col  = $f['col'] ?? 12; ?>
                <div class="col-md-<?= (int) $col ?>">
                  <label class="form-label" for="<?= e($fid) ?>"><?= e($f['label'] ?? $key) ?><?= !empty($f['required']) ? ' <span class="text-danger">*</span>' : '' ?></label>
                  <?php if ($type === 'textarea'): ?>
                    <textarea class="form-control" id="<?= e($fid) ?>" name="<?= e($key) ?>" rows="<?= (int) ($f['rows'] ?? 4) ?>" maxlength="<?= (int) ($f['max'] ?? 5000) ?>"><?= e($val) ?></textarea>
                  <?php elseif ($type === 'image'): ?>
                    <div class="d-flex align-items-start gap-3 flex-wrap">
                      <?php if (!empty($this->values[$key])): ?>
                        <img src="<?= e(admin_img($this->values[$key])) ?>" alt="" class="img-preview" data-preview-for="<?= e($fid) ?>">
                      <?php else: ?>
                        <img src="" alt="" class="img-preview d-none" data-preview-for="<?= e($fid) ?>">
                      <?php endif; ?>
                      <input class="form-control" style="max-width:380px" type="file" id="<?= e($fid) ?>" name="<?= e($key) ?>" accept="image/jpeg,image/png,image/webp,image/gif" data-image-input>
                    </div>
                  <?php else: ?>
                    <input class="form-control" type="<?= $type === 'email' ? 'email' : 'text' ?>" id="<?= e($fid) ?>" name="<?= e($key) ?>" value="<?= e($val) ?>" maxlength="<?= (int) ($f['max'] ?? 255) ?>"<?= !empty($f['placeholder']) ? ' placeholder="' . e($f['placeholder']) . '"' : '' ?>>
                  <?php endif; ?>
                  <?php if (!empty($f['help'])): ?><div class="form-text"><?= e($f['help']) ?></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save changes</button>
              </div>
            </form>
          </div>
        </div>
        <?php
    }
}
