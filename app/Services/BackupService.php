<?php

namespace App\Services;

/**
 * Creates filesystem backups for website draft projects.
 */
class BackupService
{
    /**
     * Create a timestamped ZIP backup for a project draft folder.
     */
    public function backupDraft(int $userId, string $slug): string
    {
        $storage = new ProjectStorageService();
        $projectPath = $storage->ensureProjectDirectory($userId, $slug);
        $backupPath = STORAGE_PATH . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $slug;

        if (!is_dir($backupPath) && !mkdir($backupPath, 0755, true) && !is_dir($backupPath)) {
            throw new \RuntimeException('Unable to prepare backup folder.');
        }

        $zipPath = $backupPath . DIRECTORY_SEPARATOR . 'backup-' . date('Ymd-His') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create project backup.');
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

            $zip->addFile($item->getPathname(), $relative);
        }

        $zip->close();

        return $zipPath;
    }
}
