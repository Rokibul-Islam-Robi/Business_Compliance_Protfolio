<?php
// src/PdoSessionHandler.php - Database-backed session handler for Serverless / multi-instance hosting

declare(strict_types=1);

final class PdoSessionHandler implements SessionHandlerInterface
{
    private static bool $registered = false;
    private static bool $tableChecked = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;
        $handler = new self();
        session_set_save_handler($handler, true);
    }

    private function ensureTable(): void
    {
        if (self::$tableChecked) {
            return;
        }
        self::$tableChecked = true;
        try {
            Database::execute("
                CREATE TABLE IF NOT EXISTS php_sessions (
                    id VARCHAR(128) NOT NULL PRIMARY KEY,
                    data MEDIUMTEXT NOT NULL,
                    timestamp INT UNSIGNED NOT NULL,
                    INDEX (timestamp)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Throwable $e) {}
    }

    public function open(string $path, string $name): bool
    {
        $this->ensureTable();
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $this->ensureTable();
        try {
            $row = Database::fetchOne('SELECT data FROM php_sessions WHERE id = :id AND timestamp > :t', [
                ':id' => $id,
                ':t'  => time() - 86400 * 7 // 7 days session lifetime
            ]);
            return $row ? (string)$row['data'] : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        $this->ensureTable();
        try {
            Database::execute("
                INSERT INTO php_sessions (id, data, timestamp)
                VALUES (:id, :data, :ts)
                ON DUPLICATE KEY UPDATE data = VALUES(data), timestamp = VALUES(timestamp)
            ", [
                ':id'   => $id,
                ':data' => $data,
                ':ts'   => time()
            ]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            Database::execute('DELETE FROM php_sessions WHERE id = :id', [':id' => $id]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $deleted = Database::execute('DELETE FROM php_sessions WHERE timestamp < :t', [
                ':t' => time() - $max_lifetime
            ]);
            return $deleted;
        } catch (Throwable $e) {
            return 0;
        }
    }
}
