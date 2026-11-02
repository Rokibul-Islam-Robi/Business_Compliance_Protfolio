<?php
/** CSRF protection: one random token per session, verified on every POST. */

declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Hidden <input> to put in every form. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    public static function verify(?string $token): bool
    {
        return is_string($token) && $token !== ''
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /** Call at the top of any POST handler - aborts the request on failure. */
    public static function require(): void
    {
        if (!self::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Security token missing or invalid (or the upload was larger than the server allows). Go back, reload the page and try again.');
        }
    }
}
