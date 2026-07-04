<?php

namespace App\Services;

/**
 * Validates user website file uploads before they enter draft storage.
 */
class UploadValidationService
{
    private array $allowedExtensions = [
        'html', 'htm', 'css', 'js', 'json', 'txt', 'md',
        'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg',
        'pdf', 'mp4', 'zip',
    ];

    private array $blockedExtensions = [
        'php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'sh', 'py', 'pl', 'cgi', 'htaccess',
    ];

    /**
     * Validate an uploaded file and return its safe filename.
     */
    public function validate(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Upload failed. Please choose the file again.');
        }

        $originalName = basename((string) ($file['name'] ?? ''));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($originalName === '' || $extension === '') {
            throw new \InvalidArgumentException('File name is invalid.');
        }

        $parts = array_map('strtolower', explode('.', $originalName));
        foreach ($parts as $part) {
            if (in_array($part, $this->blockedExtensions, true)) {
                throw new \InvalidArgumentException('This file type is not allowed.');
            }
        }

        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new \InvalidArgumentException('This file type is not allowed.');
        }

        if (($file['size'] ?? 0) > DEFAULT_UPLOAD_FILE_LIMIT) {
            throw new \InvalidArgumentException('The file is too large.');
        }

        $safeBase = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^A-Za-z0-9_\-\. ]/', '_', $safeBase);
        $safeBase = trim((string) $safeBase);

        if ($safeBase === '' || str_starts_with($safeBase, '.')) {
            throw new \InvalidArgumentException('File name is invalid.');
        }

        return $safeBase . '.' . $extension;
    }

    /**
     * Validate a manually created filename.
     */
    public function validateCreatedFileName(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($extension === '' || !in_array($extension, $this->allowedExtensions, true)) {
            throw new \InvalidArgumentException('Use an allowed static file extension.');
        }

        foreach (array_map('strtolower', explode('.', $filename)) as $part) {
            if (in_array($part, $this->blockedExtensions, true)) {
                throw new \InvalidArgumentException('This file type is not allowed.');
            }
        }

        return $filename;
    }
}
