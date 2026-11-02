<?php
/**
 * Crud - generic, definition-driven data layer for one table.
 *
 * $fields example:
 *   'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 150],
 * Supported types: text, textarea, number, decimal, select, date, url, email, icon, image, checkbox.
 * Column names ONLY come from this trusted definition; all values are bound parameters.
 *
 * $options: order (SQL ORDER BY literal), prepare (callable $data,$existing => $data),
 *           before_delete (callable int $id => void)
 */

declare(strict_types=1);

final class Crud
{
    public function __construct(
        private string $table,
        private array $fields,
        private array $options = []
    ) {}

    public function all(): array
    {
        $order = $this->options['order'] ?? 'id';
        return Database::fetchAll("SELECT * FROM `{$this->table}` ORDER BY {$order}");
    }

    public function find(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM `{$this->table}` WHERE id = :id", [':id' => $id]);
    }

    /**
     * Validate + insert/update.
     * @return array{ok:bool, errors:string[], id:?int}
     */
    public function save(array $post, array $files, ?int $id): array
    {
        $existing = null;
        if ($id !== null) {
            $existing = $this->find($id);
            if ($existing === null) {
                return ['ok' => false, 'errors' => ['That record no longer exists.'], 'id' => null];
            }
        }

        // ---- pass 1: ordinary fields --------------------------------------
        $data = [];
        $errors = [];
        foreach ($this->fields as $name => $f) {
            $type = $f['type'] ?? 'text';
            if ($type === 'image') {
                continue;
            }
            if ($type === 'checkbox') {
                $data[$name] = isset($post[$name]) ? 1 : 0;
                continue;
            }
            $raw = $post[$name] ?? '';
            $res = self::clean($f, is_string($raw) ? $raw : '');
            if ($res['error'] !== null) {
                $errors[] = $res['error'];
            } else {
                $data[$name] = $res['value'];
            }
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'id' => null];
        }

        if (isset($this->options['prepare'])) {
            $data = ($this->options['prepare'])($data, $existing);
        }

        // ---- pass 2: image uploads ----------------------------------------
        $uploaded = [];   // new files (removed again if anything fails)
        $replaced = [];   // old files (removed after a successful save)
        foreach ($this->fields as $name => $f) {
            if (($f['type'] ?? '') !== 'image') {
                continue;
            }
            $file = $files[$name] ?? null;
            if (Uploader::hasFile($file)) {
                try {
                    $path = Uploader::saveImage($file);
                    $data[$name] = $path;
                    $uploaded[] = $path;
                    if ($existing && !empty($existing[$name])) {
                        $replaced[] = $existing[$name];
                    }
                } catch (RuntimeException $e) {
                    $errors[] = ($f['label'] ?? $name) . ': ' . $e->getMessage();
                }
            } elseif ($existing === null && !empty($f['required'])) {
                $errors[] = ($f['label'] ?? $name) . ' is required.';
            }
        }
        if ($errors) {
            array_map([Uploader::class, 'delete'], $uploaded);
            return ['ok' => false, 'errors' => $errors, 'id' => null];
        }

        // ---- write ---------------------------------------------------------
        try {
            $params = [];
            foreach ($data as $col => $val) {
                $params[':' . $col] = $val;
            }
            if ($existing === null) {
                $cols = '`' . implode('`, `', array_keys($data)) . '`';
                $vals = implode(', ', array_keys($params));
                Database::execute("INSERT INTO `{$this->table}` ({$cols}) VALUES ({$vals})", $params);
                $newId = Database::lastInsertId();
            } else {
                $sets = [];
                foreach (array_keys($data) as $col) {
                    $sets[] = "`{$col}` = :{$col}";
                }
                $params[':__id'] = $existing['id'];
                Database::execute("UPDATE `{$this->table}` SET " . implode(', ', $sets) . ' WHERE id = :__id', $params);
                $newId = (int) $existing['id'];
            }
        } catch (PDOException $e) {
            array_map([Uploader::class, 'delete'], $uploaded);
            error_log('Crud save error (' . $this->table . '): ' . $e->getMessage());
            $msg = $e->getCode() === '23000'
                ? 'A record with the same unique value already exists (e.g. duplicate slug).'
                : 'The record could not be saved because of a database error.';
            return ['ok' => false, 'errors' => [$msg], 'id' => null];
        }

        array_map([Uploader::class, 'delete'], $replaced);
        return ['ok' => true, 'errors' => [], 'id' => $newId];
    }

    /** @return array{ok:bool, error:?string} */
    public function delete(int $id): array
    {
        $row = $this->find($id);
        if ($row === null) {
            return ['ok' => false, 'error' => 'That record no longer exists.'];
        }
        try {
            if (isset($this->options['before_delete'])) {
                ($this->options['before_delete'])($id);
            }
            Database::execute("DELETE FROM `{$this->table}` WHERE id = :id", [':id' => $id]);
        } catch (PDOException $e) {
            error_log('Crud delete error (' . $this->table . '): ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getCode() === '23000'
                ? 'This item is still in use by other records and cannot be deleted.'
                : 'The record could not be deleted because of a database error.'];
        }
        foreach ($this->fields as $name => $f) {
            if (($f['type'] ?? '') === 'image') {
                Uploader::delete($row[$name] ?? null);
            }
        }
        return ['ok' => true, 'error' => null];
    }

    /**
     * Sanitize + validate one submitted value against its field definition.
     * Also used by SettingsForm.
     * @return array{value:mixed, error:?string}
     */
    public static function clean(array $f, string $raw): array
    {
        $type  = $f['type'] ?? 'text';
        $label = $f['label'] ?? 'Field';
        $ok    = static fn($v) => ['value' => $v, 'error' => null];
        $bad   = static fn($m) => ['value' => null, 'error' => $m];

        // strip control characters (keep \n and \t), tags, normalise whitespace
        $v = preg_replace('/[^\P{C}\n\t]/u', '', $raw) ?? '';
        $v = strip_tags($v);
        $v = $type === 'textarea'
            ? trim(preg_replace("/\n{3,}/", "\n\n", $v) ?? '')
            : trim(preg_replace('/\s+/u', ' ', $v) ?? '');

        if ($v === '') {
            if (!empty($f['required'])) {
                return $bad($label . ' is required.');
            }
            if (!empty($f['nullable'])) {
                return $ok(null);
            }
            return $ok(in_array($type, ['number', 'decimal'], true) ? ($f['default'] ?? 0) : '');
        }

        $max = $f['max'] ?? ($type === 'textarea' ? 5000 : 255);
        if (mb_strlen($v) > $max) {
            return $bad("$label must be at most $max characters.");
        }

        switch ($type) {
            case 'number':
                $min = $f['min'] ?? 0;
                $mx  = $f['maxval'] ?? 2147483647;
                $n = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $mx]]);
                return $n === false ? $bad("$label must be a whole number between $min and $mx.") : $ok($n);

            case 'decimal':
                $n = filter_var($v, FILTER_VALIDATE_FLOAT);
                if ($n === false || $n < 0 || $n > 99999999.99) {
                    return $bad("$label must be a valid amount.");
                }
                return $ok(number_format((float) $n, 2, '.', ''));

            case 'select':
                $keys = array_map('strval', array_keys($f['options'] ?? []));
                return in_array($v, $keys, true) ? $ok($v) : $bad("$label has an invalid choice.");

            case 'date':
                $d = DateTime::createFromFormat('Y-m-d', $v);
                return ($d && $d->format('Y-m-d') === $v) ? $ok($v) : $bad("$label must be a valid date.");

            case 'url':
                $valid = preg_match('/^#[\w-]*$/', $v)
                    || (preg_match('#^https?://#i', $v) && filter_var($v, FILTER_VALIDATE_URL));
                return $valid ? $ok($v) : $bad("$label must be a valid http(s) URL (or an #anchor).");

            case 'email':
                return filter_var($v, FILTER_VALIDATE_EMAIL) ? $ok($v) : $bad("$label must be a valid email address.");

            case 'icon':
                return preg_match('/^bi-[a-z0-9-]+$/', $v)
                    ? $ok($v) : $bad("$label must be a Bootstrap Icons class such as bi-activity.");
        }
        return $ok($v);
    }
}
