<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

class VideoService
{
    private const MAX_UPLOAD_BYTES = 104_857_600; // 100 MB

    /** @var array<string, string> */
    private const ALLOWED_MIME_TYPES = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
    ];

    public function normalizeYoutubeUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }

        $videoId = $this->extractYoutubeId($url);

        return $videoId !== null
            ? 'https://www.youtube.com/watch?v=' . $videoId
            : null;
    }

    public function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '~(?:youtube\.com/watch\?(?:[^&]*&)*v=|youtube\.com/embed/|youtu\.be/)([A-Za-z0-9_-]{11})~',
            '~youtube\.com/shorts/([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function resolveUploadedVideoPath(string $contentUrl): ?string
    {
        $contentUrl = trim($contentUrl);

        if ($contentUrl === '' || str_contains($contentUrl, 'youtube.com') || str_contains($contentUrl, 'youtu.be')) {
            return null;
        }

        $relative = ltrim(parse_url($contentUrl, PHP_URL_PATH) ?: $contentUrl, '/');
        $fullPath = base_path('public/' . $relative);

        if (!is_file($fullPath)) {
            return null;
        }

        $realPublic = realpath(base_path('public'));
        $realFile = realpath($fullPath);

        if ($realPublic === false || $realFile === false || !str_starts_with($realFile, $realPublic)) {
            return null;
        }

        return $realFile;
    }

    public function streamFile(string $filePath): void
    {
        if (!is_file($filePath)) {
            http_response_code(404);
            exit;
        }

        $size = filesize($filePath);
        $length = $size;
        $start = 0;
        $end = $size - 1;

        if (ob_get_level()) {
            ob_end_clean();
        }

        $mimeType = match (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            'webm' => 'video/webm',
            default => 'video/mp4',
        };

        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $mimeType);

        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = explode('=', (string) $_SERVER['HTTP_RANGE'], 2)[1] ?? '';

            if (str_contains($range, ',')) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes */{$size}");
                exit;
            }

            $parts = explode('-', $range, 2);
            $start = $parts[0] !== '' ? (int) $parts[0] : 0;
            $end = isset($parts[1]) && $parts[1] !== '' ? (int) $parts[1] : $size - 1;

            if ($start > $end || $start > $size - 1 || $end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes */{$size}");
                exit;
            }

            $length = $end - $start + 1;
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }

        header('Content-Length: ' . $length);

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            http_response_code(500);
            exit;
        }

        fseek($handle, $start);

        $remaining = $length;
        while ($remaining > 0 && !feof($handle)) {
            $chunk = fread($handle, min(8192, $remaining));

            if ($chunk === false) {
                break;
            }

            echo $chunk;
            flush();
            $remaining -= strlen($chunk);
        }

        fclose($handle);
        exit;
    }

    /**
     * @param array<string, mixed> $file
     * @return array{content_url: string, duration_seconds: int}
     */
    public function storeUploadedVideo(array $file): array
    {
        $this->assertValidUpload($file);

        $extension = $this->detectExtension($file);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = base_path('public/uploads/videos');

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new Exception(__('courses.validation.video_upload_failed'));
        }

        $destination = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
            throw new Exception(__('courses.validation.video_upload_failed'));
        }

        return [
            'content_url' => '/uploads/videos/' . $filename,
            'duration_seconds' => 0,
        ];
    }

    /** @param array<string, mixed> $file */
    public function assertValidUpload(array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new Exception(__('courses.validation.video_file_required'));
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new Exception(__('courses.validation.video_upload_failed'));
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > self::MAX_UPLOAD_BYTES) {
            throw new Exception(__('courses.validation.video_file_too_large'));
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new Exception(__('courses.validation.video_upload_failed'));
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpName) ?: '';

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new Exception(__('courses.validation.video_type_invalid'));
        }
    }

    /** @param array<string, mixed> $file */
    private function detectExtension(array $file): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file((string) $file['tmp_name']) ?: '';

        return self::ALLOWED_MIME_TYPES[$mimeType] ?? 'mp4';
    }
}
