<?php
/**
 * src/helpers.php - small, dependency-free helper functions.
 */

declare(strict_types=1);

/** HTML-escape any scalar for safe output (XSS protection). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Split a multi-line text into non-empty trimmed lines. */
function lines(?string $text): array
{
    $parts = preg_split('/\R/u', (string) $text) ?: [];
    return array_values(array_filter(array_map('trim', $parts), static fn($l) => $l !== ''));
}

/** Split text into paragraphs (separated by blank lines). */
function paragraphs(?string $text): array
{
    $parts = preg_split('/\R{2,}/u', trim((string) $text)) ?: [];
    return array_values(array_filter(array_map('trim', $parts), static fn($p) => $p !== ''));
}

/** Only allow http(s) URLs or in-page anchors (#contact) in links coming from the DB. */
function safe_url(?string $url): string
{
    $url = trim((string) $url);
    if (preg_match('#^https?://#i', $url) || preg_match('/^#[\w-]*$/', $url)) {
        return $url;
    }
    return '#';
}

/**
 * Sanitize a CMS navigation URL: an #anchor, a relative page link (about.php,
 * portfolio.php?id=1) or a full http(s) address. Anything else becomes "#".
 */
function nav_url(?string $url): string
{
    $url = trim((string) $url);
    if (preg_match('/^#[\w-]*$/', $url)) {
        return $url;
    }
    if (preg_match('#^https?://#i', $url) && filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    if (preg_match('/^[A-Za-z0-9_-]+\.php(?:#[\w-]*)?$/', $url)) {
        return $url;
    }
    if (preg_match('/^[A-Za-z0-9_-]+\.php\?[A-Za-z0-9_.=&%-]*$/', $url)) {
        return $url;
    }
    return '#';
}

/** "Web Design!" => "web-design" (lowercase letters, digits, hyphens). */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/** Shorten text for table cells. */
function truncate(?string $text, int $len = 80): string
{
    $text = trim(preg_replace('/\s+/u', ' ', (string) $text) ?? '');
    return mb_strlen($text) > $len ? mb_substr($text, 0, $len - 1) . '…' : $text;
}

/** Redirect (relative URLs only) and stop. */
function redirect(string $to): never
{
    header('Location: ' . str_replace(["\r", "\n"], '', $to));
    exit;
}

/** Relative URL of an uploaded/template image as seen from /admin/. */
function admin_img(?string $path): string
{
    return '../' . ltrim((string) $path, '/');
}

/** All site settings as [key => value] (loaded once per request, cached in memory). */
if (!function_exists('settings')) {
    function settings(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            try {
                foreach (Database::fetchAll('SELECT setting_key, setting_value FROM site_settings') as $row) {
                    $cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Throwable) {
                return [];
            }
        }
        return $cache;
    }
}

/** One setting with an optional fallback. */
if (!function_exists('setting')) {
    function setting(string $key, mixed $default = ''): string
    {
        $all = settings();
        return isset($all[$key]) && $all[$key] !== null ? (string) $all[$key] : (string) ($default ?? '');
    }
}
