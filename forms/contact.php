<?php
/**
 * forms/contact.php - Contact form handler.
 *
 * Called via AJAX (fetch) from js/main.js. The JS expects:
 *   - the exact text "OK"   -> shows the green "sent" message
 *   - any other text        -> shows that text in the red error box
 *
 * Security: POST only, CSRF token, honeypot, server-side validation,
 * sanitisation, per-IP rate limit and a PDO prepared INSERT.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

header('Content-Type: text/plain; charset=UTF-8');

/** Send a plain-text response and stop. (Messages are static strings - safe for innerHTML in main.js.) */
function respond(string $text): never
{
    echo $text;
    exit;
}

/** Remove control chars + HTML tags, collapse spaces, trim. Output is still escaped with e() on display. */
function clean_line(string $v): string
{
    $v = strip_tags($v);
    $v = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $v) ?? '';
    $v = preg_replace('/\s{2,}/u', ' ', $v) ?? '';
    return trim($v);
}

/** Same as clean_line but keeps line breaks (for the message body). */
function clean_text(string $v): string
{
    $v = strip_tags($v);
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    $v = preg_replace('/[^\P{C}\n\t]+/u', '', $v) ?? '';       // drop control chars except \n \t
    $v = preg_replace("/\n{3,}/", "\n\n", $v) ?? '';
    return trim($v);
}

// 1) Method ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond('Method not allowed.');
}

// 2) CSRF -----------------------------------------------------------------
require_once __DIR__ . '/../src/PdoSessionHandler.php';
PdoSessionHandler::register();
session_start();
$token = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    respond('Your session has expired. Please reload the page and try again.');
}

// 3) Honeypot (silently pretend success to bots) ---------------------------
if (!empty($_POST['website'])) {
    respond('OK');
}

// 4) Sanitise -------------------------------------------------------------
$name    = clean_line((string) ($_POST['name'] ?? ''));
$email   = clean_line((string) ($_POST['email'] ?? ''));
$subject = clean_line((string) ($_POST['subject'] ?? ''));
$message = clean_text((string) ($_POST['message'] ?? ''));

// 5) Validate -------------------------------------------------------------
if ($name === '' || $email === '' || $subject === '' || $message === '') {
    respond('Please fill in all required fields.');
}
if (mb_strlen($name) > 100 || mb_strlen($subject) > 200 || mb_strlen($email) > 150) {
    respond('One of the fields is too long.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('Please enter a valid email address.');
}
if (mb_strlen($message) < 10) {
    respond('Your message is too short (minimum 10 characters).');
}
if (mb_strlen($message) > 5000) {
    respond('Your message is too long (maximum 5000 characters).');
}

// 6) Store ----------------------------------------------------------------
try {
    $pdo = db();
    $ip  = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    $ua  = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    // Rate limit: max 5 messages per IP per 10 minutes
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM contact_messages
          WHERE ip_address = :ip AND created_at > (NOW() - INTERVAL 10 MINUTE)'
    );
    $stmt->execute([':ip' => $ip]);
    if ((int) $stmt->fetchColumn() >= 5) {
        respond('Too many messages sent. Please try again in a few minutes.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent)
         VALUES (:name, :email, :subject, :message, :ip, :ua)'
    );
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject,
        ':message' => $message,
        ':ip'      => $ip,
        ':ua'      => $ua,
    ]);
} catch (Throwable $e) {
    error_log('DevFolio contact form error: ' . $e->getMessage());
    respond('Sorry, your message could not be saved. Please try again later.');
}

respond('OK');
