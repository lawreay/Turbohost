<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Csrf;
use App\Core\Session;
use App\Services\AuthService;

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

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
        :root { color-scheme: light; --ink: #132238; --muted: #66758a; --line: #dbe5ef; --blue: #1769e0; --blue-dark: #0f4fae; --wash: #eef6ff; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; padding: 32px 18px; font-family: "DM Sans", "Segoe UI", sans-serif; color: var(--ink); background: radial-gradient(circle at 15% 0%, #dff0ff 0, transparent 36%), linear-gradient(145deg, #f7fbff, #edf3f8); }
        .shell { width: min(760px, 100%); margin: auto; }
        .brand { display: flex; align-items: center; gap: 12px; margin: 0 0 24px 4px; }
        .brand-mark { display: grid; width: 42px; height: 42px; place-items: center; border-radius: 12px; background: linear-gradient(145deg, var(--blue), #34a0ff); color: white; font-size: 21px; font-weight: 800; box-shadow: 0 8px 18px rgba(23, 105, 224, .22); }
        .brand strong { display: block; font-size: 15px; letter-spacing: .02em; }
        .brand span { display: block; margin-top: 2px; color: var(--muted); font-size: 12px; }
        main { overflow: hidden; background: rgba(255, 255, 255, .94); border: 1px solid rgba(219, 229, 239, .9); border-radius: 18px; box-shadow: 0 22px 55px rgba(28, 54, 84, .12); }
        .hero { padding: 34px 36px 28px; background: linear-gradient(135deg, #fafdff, var(--wash)); border-bottom: 1px solid var(--line); }
        .eyebrow { margin: 0 0 9px; color: var(--blue); font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(27px, 5vw, 40px); letter-spacing: -.02em; }
        .hero p { max-width: 570px; margin: 12px 0 0; color: var(--muted); line-height: 1.6; }
        .content { padding: 28px 36px 34px; }
        .notice { padding: 13px 15px; margin-bottom: 22px; border: 1px solid #bde6cc; border-radius: 10px; background: #effaf3; color: #17633a; line-height: 1.45; }
        .notice.danger { border-color: #f1c2c2; background: #fff1f1; color: #9b2020; }
        .upload-box { padding: 22px; border: 1px dashed #a8c4e2; border-radius: 13px; background: #fbfdff; }
        label { display: block; margin-bottom: 9px; font-weight: 800; }
        input[type=file] { display: block; width: 100%; padding: 11px; border: 1px solid var(--line); border-radius: 8px; background: white; color: var(--muted); }
        input[type=file]:focus { outline: 3px solid rgba(23, 105, 224, .16); border-color: var(--blue); }
        .help { display: block; margin-top: 10px; color: var(--muted); font-size: 12px; line-height: 1.5; }
        button { display: inline-flex; align-items: center; gap: 9px; margin-top: 20px; border: 0; border-radius: 8px; padding: 12px 18px; background: var(--blue); color: #fff; font: inherit; font-weight: 800; cursor: pointer; box-shadow: 0 8px 16px rgba(23, 105, 224, .2); }
        button:hover { background: var(--blue-dark); }
        button:focus-visible { outline: 3px solid rgba(23, 105, 224, .28); outline-offset: 3px; }
        .footnote { display: flex; gap: 9px; align-items: flex-start; margin: 20px 2px 0; color: var(--muted); font-size: 12px; line-height: 1.5; }
        .footnote strong { color: var(--ink); }
        @media (max-width: 560px) { body { padding: 20px 12px; } .hero, .content { padding-left: 22px; padding-right: 22px; } .brand { margin-bottom: 18px; } .upload-box { padding: 17px; } }
  </style>
</head>
<body>
<div class="shell">
    <div class="brand"><div class="brand-mark">T</div><div><strong>TurboHostMw</strong><span>Administrator workspace</span></div></div>
    <main>
        <div class="hero"><p class="eyebrow">System maintenance</p><h1>Application Update</h1><p>Install a trusted release package with a backup created automatically before any files are changed.</p></div>
        <div class="content">
            <?php if ($message !== null): ?><div class="notice <?= $messageType === 'danger' ? 'danger' : '' ?>" role="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <div class="upload-box">
                    <label for="update_package">Choose release package</label>
                    <input id="update_package" name="update_package" type="file" accept=".zip,application/zip" required>
                    <small class="help">ZIP files up to 200 MB. Configuration, uploads, logs, templates, update storage, Git metadata, and Composer ZIP files remain untouched.</small>
                      <button type="submit">Upload and apply update</button>
                </div>
            </form>
            <p class="footnote"><span><strong>Protected operation.</strong> Only Administrator accounts can access this screen. A backup is stored before the update begins.</span></p>
        </div>
    </main>
</div>
</body>
</html>