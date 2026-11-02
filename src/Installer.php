<?php
/**
 * src/Installer.php - automatic database setup.
 *
 * When a query fails with "table doesn't exist" (or the database itself is missing)
 * Database calls Installer::run(), which creates whatever is missing from database.sql
 * (tables + sample data). Tables that already exist are NEVER dropped or touched.
 */

declare(strict_types=1);

final class Installer
{
    private static bool $done = false;

    /** Create missing tables (and their seed rows) from database.sql. */
    public static function run(PDO $pdo): void
    {
        if (self::$done) {
            return;               // only try once per request - avoids endless retry loops
        }
        self::$done = true;

        $embedded = __DIR__ . '/schema.php';
        $sql = is_file($embedded) ? require $embedded : false;
        if (!is_string($sql) || $sql === '') {
            $file = dirname(__DIR__) . '/database.sql';
            $sql  = is_file($file) ? file_get_contents($file) : false;
        }
        if ($sql === false) {
            throw new RuntimeException('Schema not found (src/schema.php and database.sql are both missing) - cannot create tables.');
        }

        $existing = array_map('strtolower', $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
        $created  = [];           // tables created by this run => only these get sample data

        foreach (self::split($sql) as $stmt) {
            // Never drop anything, never switch/create databases (db.php already did that).
            if (preg_match('/^(DROP\s|USE\s|CREATE\s+DATABASE\s)/i', $stmt)) {
                continue;
            }
            if (preg_match('/^CREATE\s+TABLE\s+`?(\w+)`?/i', $stmt, $m)) {
                $table = strtolower($m[1]);
                if (in_array($table, $existing, true)) {
                    continue;
                }
                $created[$table] = true;
            } elseif (preg_match('/^INSERT\s+INTO\s+`?(\w+)`?/i', $stmt, $m)) {
                if (!isset($created[strtolower($m[1])])) {
                    continue;     // table already existed => keep the user's data
                }
            }
            $pdo->exec($stmt);
        }
    }

    /**
     * Split an SQL script into statements. Understands '...' / "..." / `...` strings
     * (with \ escapes and doubled quotes), -- and # line comments and block comments.
     * @return string[]
     */
    public static function split(string $sql): array
    {
        $out = [];
        $buf = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $c = $sql[$i];
            $n = $sql[$i + 1] ?? '';

            // comments
            if ($c === '-' && $n === '-' && (($sql[$i + 2] ?? ' ') === ' ' || ($sql[$i + 2] ?? '') === "\n" || ($sql[$i + 2] ?? '') === "\r")
                || $c === '#') {
                while ($i < $len && $sql[$i] !== "\n") { $i++; }
                $buf .= "\n";
                continue;
            }
            if ($c === '/' && $n === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i   = $end === false ? $len : $end + 1;
                $buf .= ' ';
                continue;
            }

            // quoted strings / identifiers
            if ($c === "'" || $c === '"' || $c === '`') {
                $buf .= $c;
                for ($i++; $i < $len; $i++) {
                    $d    = $sql[$i];
                    $buf .= $d;
                    if ($d === '\\' && $c !== '`' && $i + 1 < $len) {
                        $buf .= $sql[++$i];
                    } elseif ($d === $c) {
                        if (($sql[$i + 1] ?? '') === $c) {   // doubled quote = escaped quote
                            $buf .= $sql[++$i];
                        } else {
                            break;
                        }
                    }
                }
                continue;
            }

            if ($c === ';') {
                $s = trim($buf);
                if ($s !== '') { $out[] = $s; }
                $buf = '';
                continue;
            }
            $buf .= $c;
        }
        $s = trim($buf);
        if ($s !== '') { $out[] = $s; }
        return $out;
    }
}
