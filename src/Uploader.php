<?php
/**
 * Secure image uploads with Serverless / Vercel fallback (stores in DB / /tmp).
 */

declare(strict_types=1);

final class Uploader
{
    public const MAX_BYTES       = 5 * 1024 * 1024;   // 5 MB
    public const MAX_LOGO_BYTES  = 3 * 1024 * 1024;   // 3 MB for website logo
    public const MAX_PIXELS      = 8000;              // max width/height

    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    public static function hasFile(?array $file): bool
    {
        return is_array($file) && isset($file['error']) && !is_array($file['error'])
            && (int) $file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    private static function ensureDbTable(): void
    {
        try {
            Database::execute("
                CREATE TABLE IF NOT EXISTS uploaded_files (
                    path VARCHAR(255) NOT NULL PRIMARY KEY,
                    mime_type VARCHAR(64) NOT NULL,
                    data LONGBLOB NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Throwable $e) {
            // ignore if already exists
        }
    }

    private static function saveFileToStorage(string $relPath, string $tmpFile, string $mime): void
    {
        $content = file_get_contents($tmpFile);
        if ($content === false) {
            throw new RuntimeException('Could not read uploaded file content.');
        }

        // 1. Try writing to disk (works on local XAMPP and in writable environments)
        $diskSuccess = false;
        $dir = ROOT_PATH . '/' . dirname($relPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $fullPath = ROOT_PATH . '/' . $relPath;
        if (@file_put_contents($fullPath, $content) !== false) {
            @chmod($fullPath, 0644);
            $diskSuccess = true;
        }

        // 2. Also write to /tmp on serverless environments
        if (is_dir('/tmp')) {
            $tmpDest = '/tmp/' . basename($relPath);
            @file_put_contents($tmpDest, $content);
        }

        // 3. Always save to Database (TiDB / MySQL) for persistent serverless access on Vercel
        self::ensureDbTable();
        try {
            Database::execute("
                INSERT INTO uploaded_files (path, mime_type, data)
                VALUES (:p, :m, :d)
                ON DUPLICATE KEY UPDATE mime_type = VALUES(mime_type), data = VALUES(data)
            ", [
                ':p' => $relPath,
                ':m' => $mime,
                ':d' => $content
            ]);
        } catch (Throwable $e) {
            if (!$diskSuccess) {
                throw new RuntimeException('Could not store file: ' . $e->getMessage());
            }
        }
    }

    public static function saveImage(array $file): string
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is larger than upload limit.',
                UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted.',
                default                                   => 'The file could not be uploaded.',
            });
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid upload.');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES || filesize($tmp) > self::MAX_BYTES) {
            throw new RuntimeException('Image is too large (max 5 MB).');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!is_string($mime) || !isset(self::ALLOWED[$mime])) {
            throw new RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
        }

        $info = @getimagesize($tmp);
        if ($info === false) {
            throw new RuntimeException('The file is not a valid image.');
        }
        if ($info[0] > self::MAX_PIXELS || $info[1] > self::MAX_PIXELS) {
            throw new RuntimeException('Image dimensions are too large (max ' . self::MAX_PIXELS . ' px).');
        }

        $ext = self::ALLOWED[$mime];
        $relPath = 'uploads/images/' . bin2hex(random_bytes(16)) . '.' . $ext;

        self::saveFileToStorage($relPath, $tmp, $mime);

        return $relPath;
    }

    public static function saveLogo(array $file): string
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The logo file could not be uploaded.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid logo upload.');
        }

        if (($file['size'] ?? 0) > self::MAX_LOGO_BYTES || filesize($tmp) > self::MAX_LOGO_BYTES) {
            throw new RuntimeException('Logo is too large (max 3 MB).');
        }

        $origName = (string) ($file['name'] ?? '');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        $relPath = 'uploads/images/logo_' . bin2hex(random_bytes(10)) . '.' . $ext;

        self::saveFileToStorage($relPath, $tmp, (string)$mime);

        return $relPath;
    }

    public static function delete(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'uploads/')) {
            return;
        }
        // Delete from DB
        try {
            Database::execute("DELETE FROM uploaded_files WHERE path = :p", [':p' => $path]);
        } catch (Throwable $e) {}

        // Delete from disk if exists
        $full = ROOT_PATH . '/' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }

    public static function normalizeMultiple(?array $files): array
    {
        if (!$files || !isset($files['name']) || !is_array($files['name'])) {
            return [];
        }
        $out = [];
        foreach ($files['name'] as $i => $name) {
            $out[] = [
                'name'     => $name,
                'type'     => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $files['size'][$i] ?? 0,
            ];
        }
        return $out;
    }
}
