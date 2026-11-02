<?php
/**
 * includes/functions.php - Small helpers + data access layer.
 * Every query uses PDO prepared statements (or has no user input at all).
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';   // DB class + shared helpers (e(), lines(), paragraphs(), safe_url())

/** All site settings as [key => value] (loaded once per request). */
if (!function_exists('settings')) {
    function settings(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            foreach (db()->query('SELECT setting_key, setting_value FROM site_settings') as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
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

/** Fetch rows from a table that has is_active + sort_order columns. */
function fetch_active(string $table, string $order = 'sort_order, id'): array
{
    // $table / $order are hard-coded by callers below (never user input).
    $allowed = ['skills', 'resume_items', 'services', 'stats', 'portfolio_categories',
                'pricing_plans', 'faqs', 'testimonials', 'social_links'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unknown table');
    }
    return db()->query("SELECT * FROM `$table` WHERE is_active = 1 ORDER BY $order")->fetchAll();
}

function get_skills(): array       { return fetch_active('skills'); }
function get_services(): array     { return fetch_active('services'); }
function get_stats(): array        { return fetch_active('stats'); }
function get_categories(): array   { return fetch_active('portfolio_categories'); }
function get_pricing(): array      { return fetch_active('pricing_plans'); }
function get_faqs(): array         { return fetch_active('faqs'); }
function get_testimonials(): array { return fetch_active('testimonials'); }
function get_social_links(): array { return fetch_active('social_links'); }

/**
 * True when a section heading only repeats the page title already shown in the
 * breadcrumb banner ("About me" vs "About", "Services" vs "Services").
 */
function inner_title_redundant(string $heading, string $bannerTitle): bool
{
    $norm = static fn(string $t): string => preg_replace('/[^a-z0-9]+/', '', strtolower($t)) ?? '';
    $a = $norm($heading);
    $b = $norm($bannerTitle);
    return $a === '' || ($b !== '' && str_starts_with($a, $b));
}

/**
 * Intro block at the top of the white content card on inner pages: the heading is
 * skipped when it would repeat the banner title; the sub-title stays (if any).
 */
function inner_intro(string $heading, string $subtitle, string $bannerTitle): void
{
    $showHeading = !inner_title_redundant($heading, $bannerTitle);
    $subtitle    = trim($subtitle);
    if (!$showHeading && $subtitle === '') {
        return;
    }
    echo '<div class="section-title inner-intro">';
    if ($showHeading) {
        echo '<h2>' . e($heading) . '</h2>';
    }
    if ($subtitle !== '') {
        echo '<p>' . e($subtitle) . '</p>';
    }
    echo "</div><!-- End Intro -->\n";
}

/** Visible main-navigation items (managed in admin > Menu). */
function get_nav_items(): array
{
    // Database::fetchAll (not db()->query) so a missing nav_menu table on an
    // existing installation triggers the auto-installer and its seed rows.
    return Database::fetchAll('SELECT * FROM nav_menu WHERE is_active = 1 ORDER BY sort_order, id');
}

/** Resume rows for one block: 'summary' | 'education' | 'experience'. */
function get_resume_items(string $section): array
{
    $stmt = db()->prepare(
        'SELECT * FROM resume_items WHERE section = :s AND is_active = 1 ORDER BY sort_order, id'
    );
    $stmt->execute([':s' => $section]);
    return $stmt->fetchAll();
}

/** Portfolio grid items joined with their category slug. */
function get_portfolio_items(): array
{
    return db()->query(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
           FROM portfolio_items p
           JOIN portfolio_categories c ON c.id = p.category_id
          WHERE p.is_active = 1
          ORDER BY p.sort_order, p.id'
    )->fetchAll();
}

/** A single portfolio item (or null). */
function get_portfolio_item(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
           FROM portfolio_items p
           JOIN portfolio_categories c ON c.id = p.category_id
          WHERE p.id = :id AND p.is_active = 1
          LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

/** Slider images for a portfolio item (falls back to its cover image). */
function get_portfolio_images(int $itemId, string $cover): array
{
    $stmt = db()->prepare(
        'SELECT image_path FROM portfolio_images WHERE portfolio_item_id = :id ORDER BY sort_order, id'
    );
    $stmt->execute([':id' => $itemId]);
    $imgs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $imgs ?: [$cover];
}

/** One testimonial by id (or null). */
function get_testimonial(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}
