<?php

namespace App\Services;

/**
 * Imports and exports project draft files as ZIP archives.
 */
class ProjectArchiveService
{
    private array $allowedExtensions = [
        'html', 'htm', 'css', 'js', 'json', 'txt', 'md',
        'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg',
        'pdf', 'mp4',
    ];

    /**
     * Create a ZIP archive from a project's draft folder.
     */
    public function exportDraft(int $userId, string $slug): array
    {
        $storage = new ProjectStorageService();
        $projectPath = $storage->ensureProjectDirectory($userId, $slug);
        $exportsPath = STORAGE_PATH . DIRECTORY_SEPARATOR . 'exports';

        if (!is_dir($exportsPath) && !mkdir($exportsPath, 0755, true) && !is_dir($exportsPath)) {
            throw new \RuntimeException('Unable to prepare export folder.');
        }

        $zipPath = $exportsPath . DIRECTORY_SEPARATOR . $slug . '-' . date('Ymd-His') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create ZIP export.');
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($projectPath) + 1));

            if ($item->isDir()) {
                $zip->addEmptyDir($relative);
                continue;
            }

            if ($this->isAllowedFilePath($relative)) {
                $zip->addFile($item->getPathname(), $relative);
            }
        }

        $zip->close();

        return [
            'path' => $zipPath,
            'filename' => $slug . '.zip',
            'size' => filesize($zipPath) ?: 0,
        ];
    }

    /**
     * Import safe ZIP contents into a project's draft folder.
     */
    public function importDraft(int $userId, string $slug, array $file, ?int $maxProjectBytes = null): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('ZIP upload failed. Please choose the file again.');
        }

        if (strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION)) !== 'zip') {
            throw new \InvalidArgumentException('Import requires a ZIP file.');
        }

        if (($file['size'] ?? 0) > DEFAULT_UPLOAD_FILE_LIMIT) {
            throw new \InvalidArgumentException('The ZIP file is too large.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            throw new \RuntimeException('Unable to open ZIP file.');
        }

        $storage = new ProjectStorageService();
        $projectPath = $storage->ensureProjectDirectory($userId, $slug);
        $currentBytes = $storage->directorySize($projectPath);
        $incomingBytes = $this->incomingZipBytes($zip, $storage);

        if ($maxProjectBytes !== null && ($currentBytes + $incomingBytes) > $maxProjectBytes) {
            $zip->close();
            throw new \RuntimeException(sprintf('Imported files exceed the Free plan %dMB storage limit.', (int) ($maxProjectBytes / BYTES_PER_MB)));
        }

        $imported = 0;
        $skipped = 0;
        $totalBytes = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);
            $isDirectory = str_ends_with($name, '/');

            try {
                $safePath = $storage->normalizeRelativePath($name);
            } catch (\Throwable) {
                $skipped++;
                continue;
            }

            if ($safePath === '') {
                $skipped++;
                continue;
            }

            $targetPath = $storage->resolveProjectPath($userId, $slug, $safePath);

            if ($isDirectory) {
                if (!is_dir($targetPath) && !mkdir($targetPath, 0755, true) && !is_dir($targetPath)) {
                    throw new \RuntimeException('Unable to create imported folder.');
                }
                continue;
            }

            if (!$this->isAllowedFilePath($safePath)) {
                $skipped++;
                continue;
            }

            $stat = $zip->statIndex($index);
            $totalBytes += (int) ($stat['size'] ?? 0);

            if ($maxProjectBytes !== null && $totalBytes > $maxProjectBytes) {
                $zip->close();
                throw new \RuntimeException('Imported files exceed the safe import size limit.');
            }

            $parent = dirname($targetPath);
            if (!is_dir($parent) && !mkdir($parent, 0755, true) && !is_dir($parent)) {
                throw new \RuntimeException('Unable to create imported folder.');
            }

            $stream = $zip->getStream($name);
            if (!$stream) {
                $skipped++;
                continue;
            }

            $target = fopen($targetPath, 'wb');
            if (!$target) {
                fclose($stream);
                throw new \RuntimeException('Unable to write imported file.');
            }

            stream_copy_to_stream($stream, $target);
            fclose($target);
            fclose($stream);
            $imported++;
        }

        $zip->close();

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'size' => $storage->directorySize($projectPath),
        ];
    }

    /**
     * Return true when a ZIP entry path is safe to import/export.
     */
    private function isAllowedFilePath(string $relativePath): bool
    {
        $filename = basename($relativePath);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($extension === '' || !in_array($extension, $this->allowedExtensions, true)) {
            return false;
        }

        $blocked = ['php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'sh', 'py', 'pl', 'cgi', 'htaccess'];
        foreach (array_map('strtolower', explode('.', $filename)) as $part) {
            if (in_array($part, $blocked, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Estimate allowed uncompressed bytes before extraction.
     */
    private function incomingZipBytes(\ZipArchive $zip, ProjectStorageService $storage): int
    {
        $bytes = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);

            if (str_ends_with($name, '/')) {
                continue;
            }

            try {
                $safePath = $storage->normalizeRelativePath($name);
            } catch (\Throwable) {
                continue;
            }

            if (!$this->isAllowedFilePath($safePath)) {
                continue;
            }

            $stat = $zip->statIndex($index);
            $bytes += (int) ($stat['size'] ?? 0);
        }

        return $bytes;
    }
}
