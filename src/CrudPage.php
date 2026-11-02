<?php
/**
 * CrudPage - renders a complete management card (table + add/edit modal + delete modal)
 * for one table and processes its POST actions. Built on top of Crud.
 *
 * $opts:
 *   columns       array name => header label shown in the table
 *   order         SQL ORDER BY literal
 *   prepare       callable($data, $existing): array     (modify values before saving)
 *   before_delete callable(int $id): void
 *   after_save    callable(int $id, array $post, array $files): void
 *   decorate      callable(array $rows): array           (add extra keys to rows for the UI)
 *   modal_extra   callable(): void                       (extra HTML inside the form)
 *   modal_size    '' | 'modal-lg' | 'modal-xl'
 *   empty_text    text shown when the table is empty
 */

declare(strict_types=1);

final class CrudPage
{
    private Crud $crud;
    private ?array $old = null;     // submitted values re-shown after a validation error

    public function __construct(
        private string $table,
        private string $title,
        private string $singular,
        private string $icon,
        private array $fields,
        private array $opts = []
    ) {
        $this->crud = new Crud($table, $fields, [
            'order'         => $opts['order'] ?? 'sort_order, id',
            'prepare'       => $opts['prepare'] ?? null,
            'before_delete' => $opts['before_delete'] ?? null,
        ]);
        // drop null hooks so isset() checks in Crud work
        // (Crud uses isset(), and isset(null) === false)
    }

    /** Process POST (call BEFORE any output). */
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['_crud'] ?? '') !== $this->table) {
            return;
        }
        Csrf::require();

        $action = (string) ($_POST['_action'] ?? '');
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $id = $id === false ? null : (int) $id;

        if ($action === 'save') {
            $res = $this->crud->save($_POST, $_FILES, $id);
            if ($res['ok']) {
                if (isset($this->opts['after_save'])) {
                    ($this->opts['after_save'])((int) $res['id'], $_POST, $_FILES);
                }
                Flash::add('success', $this->singular . ($id ? ' updated' : ' created') . ' successfully.');
                redirect(basename($_SERVER['SCRIPT_NAME']));
            }
            foreach ($res['errors'] as $err) {
                Flash::add('danger', $err);
            }
            $this->old = $_POST;            // keep input and re-open the modal
        } elseif ($action === 'delete' && $id !== null) {
            $res = $this->crud->delete($id);
            Flash::add($res['ok'] ? 'success' : 'danger', $res['ok'] ? $this->singular . ' deleted.' : (string) $res['error']);
            redirect(basename($_SERVER['SCRIPT_NAME']));
        }
    }

    // ---------------------------------------------------------------- view
    public function render(): void
    {
        $rows = $this->crud->all();
        if (isset($this->opts['decorate'])) {
            $rows = ($this->opts['decorate'])($rows);
        }
        $cols    = $this->opts['columns'] ?? [];
        $modalId = 'modal-' . $this->table;
        $delId   = 'delete-' . $this->table;
        $size    = $this->opts['modal_size'] ?? 'modal-lg';
        ?>
        <div class="card admin-card mb-4">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center"><i class="bi <?= e($this->icon) ?> me-2"></i><h2 class="h6 mb-0"><?= e($this->title) ?> <span class="badge text-bg-light ms-1"><?= count($rows) ?></span></h2></div>
            <button type="button" class="btn btn-primary btn-sm" data-crud-add data-bs-toggle="modal" data-bs-target="#<?= e($modalId) ?>" data-title="Add <?= e($this->singular) ?>">
              <i class="bi bi-plus-lg me-1"></i>Add <?= e($this->singular) ?>
            </button>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
              <thead>
                <tr>
                  <?php foreach ($cols as $c => $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
                  <th class="text-end" style="width:120px">Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="<?= count($cols) + 1 ?>" class="text-center text-muted py-5"><?= e($this->opts['empty_text'] ?? 'Nothing here yet. Click “Add” to create the first item.') ?></td></tr>
              <?php endif; ?>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <?php foreach ($cols as $c => $label): ?><td><?= $this->cell($c, $row) ?></td><?php endforeach; ?>
                  <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Edit"
                            data-crud-edit data-bs-toggle="modal" data-bs-target="#<?= e($modalId) ?>"
                            data-title="Edit <?= e($this->singular) ?>"
                            data-row="<?= e(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
                      <i class="bi bi-pencil-square"></i></button>
                    <button type="button" class="btn btn-outline-danger btn-sm" title="Delete"
                            data-bs-toggle="modal" data-bs-target="#<?= e($delId) ?>"
                            data-delete-id="<?= (int) $row['id'] ?>"
                            data-delete-name="<?= e($row['title'] ?? $row['name'] ?? $row['label'] ?? $row['question'] ?? ('#' . $row['id'])) ?>">
                      <i class="bi bi-trash3"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Add / Edit modal -->
        <div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog <?= e($size) ?> modal-dialog-scrollable modal-fullscreen-sm-down">
            <form class="modal-content" method="post" enctype="multipart/form-data" autocomplete="off" data-crud-form
                  <?= $this->old !== null ? 'data-reopen="' . e(json_encode($this->reopenRow(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) . '"' : '' ?>>
              <?= Csrf::field() ?>
              <input type="hidden" name="_crud" value="<?= e($this->table) ?>">
              <input type="hidden" name="_action" value="save">
              <input type="hidden" name="id" value="">
              <div class="modal-header">
                <h5 class="modal-title" data-modal-title>Add <?= e($this->singular) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <?php foreach ($this->fields as $name => $f): ?>
                    <div class="col-md-<?= (int) ($f['col'] ?? 12) ?>"><?php $this->field($name, $f); ?></div>
                  <?php endforeach; ?>
                </div>
                <?php if (isset($this->opts['modal_extra'])) { ($this->opts['modal_extra'])(); } ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Delete confirmation modal -->
        <div class="modal fade" id="<?= e($delId) ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post">
              <?= Csrf::field() ?>
              <input type="hidden" name="_crud" value="<?= e($this->table) ?>">
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="id" value="">
              <div class="modal-body text-center p-4">
                <div class="display-5 text-danger mb-2"><i class="bi bi-exclamation-triangle"></i></div>
                <h5>Delete this <?= e(strtolower($this->singular)) ?>?</h5>
                <p class="text-muted mb-0"><strong data-delete-label></strong><br>This action cannot be undone.</p>
              </div>
              <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Yes, delete</button>
              </div>
            </form>
          </div>
        </div>
        <?php
    }

    /** Re-shown values after a validation error (checkboxes normalised to 0/1). */
    private function reopenRow(): array
    {
        $row = [];
        foreach ($this->fields as $name => $f) {
            if (($f['type'] ?? '') === 'checkbox') {
                $row[$name] = isset($this->old[$name]) ? 1 : 0;
            } elseif (($f['type'] ?? '') !== 'image') {
                $row[$name] = is_string($this->old[$name] ?? null) ? $this->old[$name] : '';
            }
        }
        $row['id'] = $this->old['id'] ?? '';
        return $row;
    }

    /** Table cell HTML (all output escaped). */
    private function cell(string $name, array $row): string
    {
        $f    = $this->fields[$name] ?? ['type' => 'text'];
        $type = $f['type'] ?? 'text';
        $v    = $row[$name] ?? null;

        if ($v === null || $v === '') {
            return '<span class="text-muted">—</span>';
        }
        return match ($type) {
            'image'    => '<img src="' . e(admin_img((string) $v)) . '" class="thumb" alt="">',
            'checkbox' => (int) $v === 1
                ? '<span class="badge text-bg-success">Active</span>'
                : '<span class="badge text-bg-secondary">Hidden</span>',
            'select'   => e($f['options'][$v] ?? $v),
            'icon'     => '<i class="bi ' . e($v) . ' fs-5 me-2 align-middle"></i><code class="small">' . e($v) . '</code>',
            'textarea' => e(truncate((string) $v, 70)),
            'decimal'  => '$' . e(number_format((float) $v, 2)),
            'url'      => '<span class="small">' . e(truncate((string) $v, 40)) . '</span>',
            default    => e(truncate((string) $v, 60)),
        };
    }

    /** One form control. */
    private function field(string $name, array $f): void
    {
        $type  = $f['type'] ?? 'text';
        $id    = 'f-' . $this->table . '-' . $name;
        $label = $f['label'] ?? $name;
        $req   = !empty($f['required']);
        $def   = $f['default'] ?? '';

        if ($type === 'checkbox') {
            ?>
            <div class="form-check form-switch mt-md-4 pt-md-2">
              <input class="form-check-input" type="checkbox" role="switch" id="<?= e($id) ?>" name="<?= e($name) ?>" value="1"<?= (int) $def === 1 ? ' checked' : '' ?>>
              <label class="form-check-label" for="<?= e($id) ?>"><?= e($label) ?></label>
            </div>
            <?php
            return;
        }
        ?>
        <label class="form-label" for="<?= e($id) ?>"><?= e($label) ?><?= $req ? ' <span class="text-danger">*</span>' : '' ?></label>
        <?php if ($type === 'textarea'): ?>
          <textarea class="form-control" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) ($f['rows'] ?? 3) ?>" maxlength="<?= (int) ($f['max'] ?? 5000) ?>"<?= $req ? ' required' : '' ?>></textarea>
        <?php elseif ($type === 'select'): ?>
          <select class="form-select" id="<?= e($id) ?>" name="<?= e($name) ?>"<?= $req ? ' required' : '' ?>>
            <?php foreach (($f['options'] ?? []) as $val => $text): ?>
              <option value="<?= e($val) ?>"<?= (string) $val === (string) $def ? ' selected' : '' ?>><?= e($text) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($type === 'image'): ?>
          <div class="d-flex align-items-start gap-3 flex-wrap">
            <img src="" alt="" class="img-preview d-none" data-preview-for="<?= e($id) ?>">
            <input class="form-control" style="max-width:380px" type="file" id="<?= e($id) ?>" name="<?= e($name) ?>" accept="image/jpeg,image/png,image/webp,image/gif" data-image-input>
          </div>
        <?php elseif ($type === 'icon'): ?>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-question-circle" data-icon-preview></i></span>
            <input class="form-control" type="text" id="<?= e($id) ?>" name="<?= e($name) ?>" maxlength="64" placeholder="bi-activity" data-icon-input<?= $req ? ' required' : '' ?>>
          </div>
        <?php else:
            $htmlType = match ($type) { 'number', 'decimal' => 'number', 'date' => 'date', 'email' => 'email', default => 'text' };
            $extra = '';
            if ($type === 'number')  { $extra = ' min="' . (int) ($f['min'] ?? 0) . '" max="' . (int) ($f['maxval'] ?? 2147483647) . '" step="1"'; }
            if ($type === 'decimal') { $extra = ' min="0" step="0.01"'; }
            if ($type !== 'number' && $type !== 'decimal' && $type !== 'date') { $extra = ' maxlength="' . (int) ($f['max'] ?? 255) . '"'; }
            ?>
          <input class="form-control" type="<?= $htmlType ?>" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($def) ?>"<?= $extra ?><?= !empty($f['placeholder']) ? ' placeholder="' . e($f['placeholder']) . '"' : '' ?><?= $req ? ' required' : '' ?>>
        <?php endif; ?>
        <?php if (!empty($f['help'])): ?><div class="form-text"><?= e($f['help']) ?></div><?php endif; ?>
        <?php
    }
}
