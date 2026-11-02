<?php
declare(strict_types=1);

/**
 * db.php - Database connection class (PDO + MySQL / TiDB / Environment Variables).
 * Supports local XAMPP and Cloud databases (TiDB / PlanetScale / Aiven / RDS).
 */

final class Database
{
    private static ?PDO $pdo = null;

    private static function getEnvOr(string $key, string $default): string
    {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return (string)$val;
        }
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    /** Shared PDO connection (created lazily). */
    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host    = self::getEnvOr('DB_HOST', '127.0.0.1');
        $port    = self::getEnvOr('DB_PORT', '3306');
        $name    = self::getEnvOr('DB_NAME', 'devfolio');
        $user    = self::getEnvOr('DB_USER', 'root');
        $pass    = self::getEnvOr('DB_PASS', '');
        $sslCa   = self::getEnvOr('DB_SSL_CA', '');
        $charset = 'utf8mb4';

        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // TiDB Cloud SSL Support
        if (!empty($sslCa)) {
            $opts[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        } elseif ($host !== '127.0.0.1' && $host !== 'localhost') {
            $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $base = sprintf('mysql:host=%s;port=%s;charset=%s', $host, $port, $charset);

        try {
            try {
                self::$pdo = new PDO($base . ';dbname=' . $name, $user, $pass, $opts);
            } catch (PDOException $e) {
                if ((int) ($e->errorInfo[1] ?? 0) !== 1049) {
                    throw $e;
                }
                $tmp = new PDO($base, $user, $pass, $opts);
                $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                self::$pdo = new PDO($base . ';dbname=' . $name, $user, $pass, $opts);
            }
        } catch (PDOException $e) {
            error_log('DevFolio DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Database connection failed. Check credentials or server status.');
        }

        foreach (['site_settings', 'admin_users'] as $t) {
            if (self::$pdo->query("SHOW TABLES LIKE '$t'")->fetchColumn() === false) {
                require_once __DIR__ . '/src/Installer.php';
                try {
                    Installer::run(self::$pdo);
                } catch (Throwable $e) {
                    error_log('DevFolio auto-install failed: ' . $e->getMessage());
                    http_response_code(500);
                    exit('Automatic database setup failed: ' . htmlspecialchars($e->getMessage()));
                }
                break;
            }
        }
        return self::$pdo;
    }

    private static function run(string $sql, array $params): PDOStatement
    {
        for ($try = 0; ; $try++) {
            try {
                $st = self::pdo()->prepare($sql);
                $st->execute($params);
                return $st;
            } catch (PDOException $e) {
                if ($try > 0 || (int) ($e->errorInfo[1] ?? 0) !== 1146) {
                    throw $e;
                }
                require_once __DIR__ . '/src/Installer.php';
                Installer::run(self::pdo());
            }
        }
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        $st = self::run($sql, $params);
        return $st->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $st = self::run($sql, $params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchValue(string $sql, array $params = []): mixed
    {
        $st = self::run($sql, $params);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function execute(string $sql, array $params = []): int
    {
        $st = self::run($sql, $params);
        return $st->rowCount();
    }

    public static function lastInsertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }
}

function db(): PDO
{
    return Database::pdo();
}
