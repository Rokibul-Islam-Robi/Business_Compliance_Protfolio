<?php
/**
 * Secure image uploads.
 *  - checks PHP upload errors + is_uploaded_file()
 *  - size limit
 *  - REAL MIME type via finfo (client-supplied type / extension are ignored)
 *  - must decode as an image (getimagesize) and be of sane dimensions
 *  - stored under uploads/images/ with a random name and a server-chosen extension
 */

declare(strict_types=1);

final class Uploader
{
    public const MAX_BYTES       = 3 * 1024 * 1024;   // 3 MB
    public const MAX_LOGO_BYTES  = 2 * 1024 * 1024;   // 2 MB for website logo
    public const MAX_PIXELS      = 8000;              // max width/height

    /** Allowed MIME => extension (SVG deliberately excluded: it can carry scripts). */
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /** True when the user actually chose a file in this input. */
    public static function hasFile(?array $file): bool
    {
        return is_array($file) && isset($file['error']) && !is_array($file['error'])
            && (int) $file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Validate + store an uploaded image.
     * @return string path relative to the project root, e.g. "uploads/images/ab12....jpg"
     * @throws RuntimeException with a user-friendly message
     */
    public static function saveImage(array $file): string
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is larger than the server upload limit.',
                UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted. Please try again.',
                default                                   => 'The file could not be uploaded.',
            });
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid upload.');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES || filesize($tmp) > self::MAX_BYTES) {
            throw new RuntimeException('Image is too large (max ' . (self::MAX_BYTES / 1048576) . ' MB).');
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

        $dir = UPLOAD_DIR . '/images';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Upload folder is not writable.');
        }

        $name = bin2hex(random_bytes(16)) . '.' . self::ALLOWED[$mime];
        if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
            throw new RuntimeException('Could not save the uploaded file (check folder permissions).');
        }
        @chmod($dir . '/' . $name, 0644);

        return 'uploads/images/' . $name;
    }

    /**
     * Validate + store an uploaded website logo (.png, .jpg, .jpeg, .svg, max 2MB).
     * @return string path relative to project root, e.g. "uploads/images/logo_abc123.png"
     * @throws RuntimeException
     */
    public static function saveLogo(array $file): string
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Logo file is larger than the server upload limit.',
                UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted. Please try again.',
                default                                   => 'The logo file could not be uploaded.',
            });
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid logo upload.');
        }

        // 1. File size limit (max 2MB)
        if (($file['size'] ?? 0) > self::MAX_LOGO_BYTES || filesize($tmp) > self::MAX_LOGO_BYTES) {
            throw new RuntimeException('Logo is too large (maximum allowed size is 2 MB).');
        }

        // 2. Extension validation
        $origName = (string) ($file['name'] ?? '');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        $allowedExts = ['png', 'jpg', 'svg'];
        if (!in_array($ext, $allowedExts, true)) {
            throw new RuntimeException('Invalid file format. Allowed formats: PNG, JPG, JPEG, and SVG.');
        }

        // 3. MIME validation & content inspection
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

        if ($ext === 'svg') {
            $allowedSvgMimes = ['image/svg+xml', 'image/svg', 'text/xml', 'text/plain'];
            if (!is_string($mime) || !in_array($mime, $allowedSvgMimes, true)) {
                throw new RuntimeException('The uploaded file is not a valid SVG image.');
            }
            if (!self::isValidSvg($tmp)) {
                throw new RuntimeException('The SVG file is invalid or contains forbidden scripts or elements.');
            }
        } else {
            $allowedRasterMimes = [
                'image/png'  => 'png',
                'image/jpeg' => 'jpg',
            ];
            if (!is_string($mime) || !isset($allowedRasterMimes[$mime])) {
                throw new RuntimeException('Only PNG, JPG, JPEG, or SVG files are allowed.');
            }
            $info = @getimagesize($tmp);
            if ($info === false) {
                throw new RuntimeException('The uploaded file is not a valid image.');
            }
        }

        $dir = UPLOAD_DIR . '/images';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Upload folder is not writable.');
        }

        $name = 'logo_' . bin2hex(random_bytes(10)) . '.' . $ext;
        $target = $dir . '/' . $name;
        if (!move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Could not save the uploaded logo.');
        }
        @chmod($target, 0644);

        return 'uploads/images/' . $name;
    }

    /**
     * Inspect an SVG file to ensure it is valid XML/SVG and contains no scripts or event handlers.
     */
    public static function isValidSvg(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            return false;
        }

        // Must have opening and closing svg tags
        if (!preg_match('/<svg[\s>]/i', $content) || !preg_match('/<\/svg>/i', $content)) {
            return false;
        }

        // Reject script tags, XML entity declarations, and inline execution vectors
        $blacklist = [
            '/<script[\s>]/i',
            '/<\/script>/i',
            '/<iframe[\s>]/i',
            '/<embed[\s>]/i',
            '/<object[\s>]/i',
            '/<foreignObject[\s>]/i',
            '/<!ENTITY/i',
            '/<!DOCTYPE[^>]*\[/i',
            '/href\s*=\s*["\']\s*javascript:/i',
            '/xlink:href\s*=\s*["\']\s*javascript:/i',
            '/\bon[a-z]+\s*=/i', // onload=, onerror=, onclick=, etc.
        ];

        foreach ($blacklist as $pattern) {
            if (preg_match($pattern, $content)) {
                return false;
            }
        }

        return true;
    }

    /** Delete a previously uploaded file. Template images (img/...) are never touched. */
    public static function delete(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'uploads/')) {
            return;
        }
        $full = realpath(ROOT_PATH . '/' . $path);
        $base = realpath(UPLOAD_DIR);
        if ($full && $base && str_starts_with($full, $base . DIRECTORY_SEPARATOR) && is_file($full)) {
            @unlink($full);
        }
    }

    /** Turn the $_FILES shape of <input type=file multiple> into a list of single-file arrays. */
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
