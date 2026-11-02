<?php
/**
 * Admin authentication (PHP sessions + password_hash / password_verify).
 *  - hardened session cookie (HttpOnly, SameSite=Lax, Secure on HTTPS)
 *  - session id regenerated on login (fixation protection)
 *  - idle timeout, brute-force lockout (per IP, stored in MySQL)
 *  - a default admin is created automatically on first use (see DEFAULT_*)
 */

declare(strict_types=1);

final class Auth
{
    public const DEFAULT_USER = 'admin';
    public const DEFAULT_PASS = 'Admin@12345';
    public const IDLE_TIMEOUT = 1800;   // 30 minutes
    public const MAX_ATTEMPTS = 5;      // failed logins ...
    public const LOCK_MINUTES = 15;     // ... per IP within this window

    private static ?array $user = null;

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        if (is_dir('/tmp') && is_writable('/tmp')) {
            ini_set('session.save_path', '/tmp');
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_name('devfolio_admin');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    /** Guard for every protected admin page. */
    public static function requireLogin(): void
    {
        if (self::check()) {
            $last = (int) ($_SESSION['last_activity'] ?? 0);
            if ($last && (time() - $last) > self::IDLE_TIMEOUT) {
                self::logout();
                Flash::add('warning', 'You were signed out after 30 minutes of inactivity.');
                redirect('login.php');
            }
            $_SESSION['last_activity'] = time();
            return;
        }
        redirect('login.php');
    }

    /** Current admin row (without hash exposure to views) or null. */
    public static function user(): ?array
    {
        if (self::$user === null && self::check()) {
            self::$user = Database::fetchOne(
                'SELECT id, username, email, password_hash, last_login_at FROM admin_users WHERE id = :id',
                [':id' => $_SESSION['admin_id']]
            );
        }
        return self::$user;
    }

    /** Create the default admin when the table is empty (first run). */
    public static function ensureDefaultAdmin(): void
    {
        if ((int) Database::fetchValue('SELECT COUNT(*) FROM admin_users') === 0) {
            Database::execute(
                'INSERT INTO admin_users (username, email, password_hash) VALUES (:u, :e, :p)',
                [':u' => self::DEFAULT_USER, ':e' => 'admin@example.com',
                 ':p' => password_hash(self::DEFAULT_PASS, PASSWORD_DEFAULT)]
            );
        }
    }

    public static function usingDefaultPassword(): bool
    {
        $u = self::user();
        return $u !== null && password_verify(self::DEFAULT_PASS, $u['password_hash']);
    }

    /** @return string|null error message, or null on success */
    public static function attempt(string $login, string $password): ?string
    {
        self::ensureDefaultAdmin();
        $ip = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $ip = substr($ip, 0, 45);

        // Housekeeping + brute-force lockout
        Database::execute('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
        $fails = (int) Database::fetchValue(
            'SELECT COUNT(*) FROM login_attempts WHERE ip_address = :ip
              AND attempted_at > (NOW() - INTERVAL ' . self::LOCK_MINUTES . ' MINUTE)',
            [':ip' => $ip]
        );
        if ($fails >= self::MAX_ATTEMPTS) {
            return 'Too many failed attempts. Please try again in ' . self::LOCK_MINUTES . ' minutes.';
        }

        $row = Database::fetchOne(
            'SELECT * FROM admin_users WHERE username = :u1 OR email = :u2 LIMIT 1',
            [':u1' => $login, ':u2' => $login]
        );

        // Always run a verify (even for unknown users) so timing does not reveal valid usernames
        $hash = $row['password_hash'] ?? password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $ok   = password_verify($password, $hash) && $row !== null;

        if (!$ok) {
            Database::execute('INSERT INTO login_attempts (ip_address, username) VALUES (:ip, :u)',
                [':ip' => $ip, ':u' => mb_substr($login, 0, 100)]);
            return 'Invalid username or password.';
        }

        // Success
        Database::execute('DELETE FROM login_attempts WHERE ip_address = :ip', [':ip' => $ip]);
        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            Database::execute('UPDATE admin_users SET password_hash = :h WHERE id = :id',
                [':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $row['id']]);
        }
        Database::execute('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id', [':id' => $row['id']]);

        @session_regenerate_id(false);
        $_SESSION['admin_id']      = (int) $row['id'];
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token']    = bin2hex(random_bytes(32));
        self::$user = null;
        return null;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_id'], $_SESSION['last_activity']);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        @session_regenerate_id(false);
        self::$user = null;
    }

    /** @return string|null error message, or null on success */
    public static function changePassword(int $id, string $current, string $new, string $confirm): ?string
    {
        $row = Database::fetchOne('SELECT password_hash FROM admin_users WHERE id = :id', [':id' => $id]);
        if (!$row || !password_verify($current, $row['password_hash'])) {
            return 'Your current password is incorrect.';
        }
        if (strlen($new) < 8) {
            return 'The new password must be at least 8 characters long.';
        }
        if ($new !== $confirm) {
            return 'The new password and its confirmation do not match.';
        }
        if ($new === self::DEFAULT_PASS) {
            return 'Please choose a password different from the default one.';
        }
        Database::execute('UPDATE admin_users SET password_hash = :h WHERE id = :id',
            [':h' => password_hash($new, PASSWORD_DEFAULT), ':id' => $id]);
        @session_regenerate_id(false);
        self::$user = null;
        return null;
    }
}
