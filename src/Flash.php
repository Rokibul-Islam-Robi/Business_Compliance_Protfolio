<?php
/** One-time notification messages shown as Bootstrap alerts after a redirect. */

declare(strict_types=1);

final class Flash
{
    /** @param string $type success|danger|warning|info */
    public static function add(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /** Return and clear all pending messages. */
    public static function pull(): array
    {
        $all = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $all;
    }
}
