<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Csrf;
use App\Core\Session;
use App\Services\AuthService;

$config = require __DIR__ . '/../app/Config/app.php';
Session::start();

if (!AuthService::check()) {
    header('Location: ' . rtrim((string) ($config['base_url'] ?? ''), '/') . '/login');
    exit;
}

if (!AuthService::isAdmin()) {
    http_response_code(403);
    exit('Forbidden');
}

$rootPath = realpath(__DIR__ . '/..');
$updatePath = $rootPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'updates';
$incomingPath = $updatePath . DIRECTORY_SEPARATOR . 'incoming';
$backupPath = $updatePath . DIRECTORY_SEPARATOR . 'backups';
$maxUploadBytes = 200 * 1024 * 1024;
$message = null;
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $message = 'Your session expired. Please try again.';
        $messageType = 'danger';
    } else {
        try {
            $message = applyUpdate($rootPath, $incomingPath, $backupPath, $maxUploadBytes);
        } catch (Throwable $exception) {
            $message = $exception->getMessage();
            $messageType = 'danger';
        }
    }
}

function applyUpdate(string $rootPath, string $incomingPath, string $backupPath, int $maxUploadBytes): string
{
    $upload = $_FILES['update_package'] ?? null;
    if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Choose a ZIP package to upload.');
    }

    if ((int) ($upload['size'] ?? 0) > $maxUploadBytes) {
        throw new RuntimeException('The update package must not exceed 200 MB.');
    }

    if (strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION)) !== 'zip') {
        throw new RuntimeException('The update package must be a ZIP file.');
    }

    foreach ([$incomingPath, $backupPath] as $directory) {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to prepare update storage.');
        }
    }

    $incomingFile = $incomingPath . DIRECTORY_SEPARATOR . 'update-' . bin2hex(random_bytes(8)) . '.zip';
    if (!move_uploaded_file((string) $upload['tmp_name'], $incomingFile)) {
        throw new RuntimeException('Unable to store the uploaded package.');
    }

    $zip = new ZipArchive();
    if ($zip->open($incomingFile) !== true) {
        @unlink($incomingFile);
        throw new RuntimeException('The uploaded file is not a readable ZIP archive.');
    }

    try {
        validateZip($zip);
        $extractPath = $incomingPath . DIRECTORY_SEPARATOR . 'extract-' . bin2hex(random_bytes(8));
        if (!mkdir($extractPath, 0755, true)) {
            throw new RuntimeException('Unable to prepare the extraction directory.');
        }

        if (!$zip->extractTo($extractPath)) {
            throw new RuntimeException('Unable to extract the update package.');
        }
        $zip->close();

        $backupDirectory = $backupPath . DIRECTORY_SEPARATOR . 'backup_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        copyTree($rootPath, $backupDirectory, $rootPath, true);
        copyTree($extractPath, $rootPath, $extractPath, false);
        removeTree($extractPath);
        @unlink($incomingFile);

        return 'Update applied successfully. Backup created at ' . basename($backupDirectory) . '.';
    } catch (Throwable $exception) {
        $zip->close();
        @unlink($incomingFile);
        throw $exception;
    }
}

function validateZip(ZipArchive $zip): void
{
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        $name = str_replace('\\', '/', (string) ($stat['name'] ?? ''));
        $trimmed = trim($name, '/');

        if ($trimmed === '' || str_starts_with($name, '/') || preg_match('/^[A-Za-z]:\//', $name)) {
            throw new RuntimeException('The ZIP contains an unsafe path.');
        }

        foreach (explode('/', $trimmed) as $part) {
            if ($part === '..' || $part === '') {
                throw new RuntimeException('The ZIP contains an unsafe path.');
            }
        }

        $mode = (int) ($stat['external_attributes'] ?? 0) >> 16;
        if (($mode & 0170000) === 0120000 || (($mode & 0170000) === 0100000 && ($mode & 0111) !== 0)) {
            throw new RuntimeException('The ZIP contains an unsafe link or executable file.');
        }
    }
}

function copyTree(string $source, string $destination, string $rootPath, bool $backup): void
{
    if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
        throw new RuntimeException('Unable to create update destination.');
    }

    $items = scandir($source);
    if ($items === false) {
        throw new RuntimeException('Unable to read update files.');
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $sourcePath = $source . DIRECTORY_SEPARATOR . $item;
        $relativePath = ltrim(str_replace('\\', '/', substr($sourcePath, strlen($rootPath))), '/');
        if (shouldExclude($relativePath, $backup)) {
            continue;
        }

        $destinationPath = $destination . DIRECTORY_SEPARATOR . $item;
        if (is_link($sourcePath)) {
            throw new RuntimeException('Links are not allowed in application files.');
        }
        if (is_dir($sourcePath)) {
            copyTree($sourcePath, $destinationPath, $rootPath, $backup);
        } elseif (is_file($sourcePath) && !copy($sourcePath, $destinationPath)) {
            throw new RuntimeException('Unable to copy ' . $relativePath . '.');
        }
    }
}

function shouldExclude(string $relativePath, bool $backup): bool
{
    $normalized = trim(str_replace('\\', '/', $relativePath), '/');
    $parts = explode('/', $normalized);
    $first = strtolower($parts[0] ?? '');

    if ($normalized === '.env' || $first === '.git' || in_array($first, ['uploads', 'templates', 'logs'], true)) {
        return true;
    }
    if (str_starts_with($normalized, 'storage/updates') || $normalized === 'storage/updates') {
        return true;
    }
    if (!$backup && $normalized === '') {
        return true;
    }
    if (str_starts_with($normalized, 'vendor/composer/') && strtolower(pathinfo($normalized, PATHINFO_EXTENSION)) === 'zip') {
        return true;
    }

    return false;
}

function removeTree(string $path): void
{
    if (is_dir($path)) {
        foreach (scandir($path) ?: [] as $item) {
            if ($item !== '.' && $item !== '..') {
                removeTree($path . DIRECTORY_SEPARATOR . $item);
            }
        }
        @rmdir($path);
    } elseif (is_file($path)) {
        @unlink($path);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Application Update</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f3f6fa; color: #172033; margin: 0; padding: 48px 20px; }
    main { max-width: 680px; margin: auto; background: #fff; border: 1px solid #dbe2ea; border-radius: 10px; padding: 32px; box-shadow: 0 10px 30px rgba(25, 45, 75, .08); }
    h1 { margin-top: 0; } .notice { padding: 12px 14px; border-radius: 6px; background: #e8f7ee; color: #17633a; margin-bottom: 20px; }
    .notice.danger { background: #fdecec; color: #9b2020; } label { display: block; font-weight: 700; margin-bottom: 8px; }
    input[type=file] { display: block; width: 100%; margin-bottom: 18px; } button { border: 0; border-radius: 6px; padding: 11px 18px; background: #0d6efd; color: #fff; font-weight: 700; cursor: pointer; }
    small { color: #5b6678; }
  </style>
</head>
<body>
<main>
  <h1>Application Update</h1>
  <?php if ($message !== null): ?><div class="notice <?= $messageType === 'danger' ? 'danger' : '' ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <p>Upload a ZIP package to update the application. A backup is created before files are applied.</p>
  <form method="post" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <label for="update_package">ZIP package</label>
    <input id="update_package" name="update_package" type="file" accept=".zip,application/zip" required>
    <small>Maximum size: 200 MB. Configuration, uploads, logs, templates, update storage, Git metadata, and Composer ZIP files are preserved.</small>
    <p><button type="submit">Upload and apply update</button></p>
  </form>
</main>
</body>
</html>