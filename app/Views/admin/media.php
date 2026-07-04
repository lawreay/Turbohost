<?php
$uploadBase = $uploads_url;
?>
<div class="admin-pro-shell">
  <?php $active = $active ?? 'media'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
     
      <div class="admin-pro-topbar-right admin-pro-actions">
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin', ENT_QUOTES, 'UTF-8') ?>"><i data-lucide="arrow-left-circle"></i> Dashboard</a>
      </div>
    </header>

    <?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type): ?>
      <?php $message = \App\Core\Session::pullFlash($key); ?>
      <?php if ($message): ?>
        <div class="admin-settings-status alert alert-<?= $type ?> shadow-sm" role="alert">
          <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>

    <div class="admin-pro-card mb-4">
      <div class="admin-pro-card-head">
        <h2>Upload new asset</h2>
      </div>
      <form id="media-upload-form" method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/media/upload', ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
        <?= \App\Core\Csrf::field() ?>
        <div class="row g-3 align-items-end">
          <div class="col-md-5">
            <label class="form-label">Choose file</label>
            <input id="media-file-input" type="file" name="media_file" class="form-control" accept="<?= htmlspecialchars(implode(',', array_map(static fn($ext) => '.' . $ext, $allowed_extensions)), ENT_QUOTES, 'UTF-8') ?>">
            <div id="media-preview" class="mt-2 d-none">
              <label class="form-label small">Preview</label>
              <div class="p-2 bg-white rounded">
                <img id="media-preview-img" src="" alt="Preview" class="media-thumb">
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Allowed types</label>
            <div class="form-control p-2"><?= htmlspecialchars(implode(', ', $allowed_extensions), ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Max size</label>
            <div class="form-control p-2"><?= htmlspecialchars((string) $max_upload_size_mb, ENT_QUOTES, 'UTF-8') ?> MB</div>
          </div>
          <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-primary">Upload</button>
          </div>
        </div>
      </form>
    </div>

    <div class="admin-pro-card">
      <div class="admin-pro-card-head">
        <h2>Files</h2>
        <span><?= htmlspecialchars(count($files) . ' assets', ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <?php if ($files === []): ?>
        <p class="text-secondary">No uploaded media files found.</p>
      <?php else: ?>
        <div class="media-grid">
          <?php foreach ($files as $file): ?>
            <div class="media-card">
              <div class="media-card-img">
                <?php if (in_array($file['extension'], ['png','jpg','jpeg','gif','webp','bmp','svg'], true)): ?>
                  <img src="<?= htmlspecialchars($file['url'], ENT_QUOTES, 'UTF-8') ?>" class="media-thumb" alt="<?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                  <div class="media-thumb-placeholder">File</div>
                <?php endif; ?>
                <label class="media-select">
                  <input type="checkbox" class="form-check-input"> Select
                </label>
              </div>
              <div class="media-card-body">
                <div class="media-card-title" title="<?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="media-card-meta text-muted"><?= htmlspecialchars($file['extension'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(number_format($file['size'] / 1024, 2), ENT_QUOTES, 'UTF-8') ?> KB</div>

                <div class="d-flex gap-2 mt-2">
                  <button class="btn btn-sm btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($file['url'], ENT_QUOTES, 'UTF-8') ?>')">Copy</button>
                  <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($file['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open</a>
                  <button class="btn btn-sm btn-outline-warning" type="button" onclick="document.getElementById('rename-<?= htmlspecialchars(rawurlencode($file['name']), ENT_QUOTES, 'UTF-8') ?>').classList.toggle('d-none')">Rename</button>
                </div>

                <div id="rename-<?= htmlspecialchars(rawurlencode($file['name']), ENT_QUOTES, 'UTF-8') ?>" class="d-none mt-2">
                  <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/media/rename', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="current_name" value="<?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="input-group input-group-sm">
                      <input class="form-control" name="new_name" value="<?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?>">
                      <button class="btn btn-sm btn-primary" type="submit">Save</button>
                    </div>
                  </form>
                </div>

                <div class="mt-3">
                  <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/media/delete', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="filename" value="<?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-sm btn-danger w-100">Delete</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>
<script>
  (function () {
    const input = document.getElementById('media-file-input');
    const previewWrap = document.getElementById('media-preview');
    const previewImg = document.getElementById('media-preview-img');

    if (!input) return;

    input.addEventListener('change', function (e) {
      const file = e.target.files && e.target.files[0];
      if (!file) {
        previewWrap.classList.add('d-none');
        previewImg.src = '';
        return;
      }

      const isImage = /image\/(png|jpeg|jpg|gif|webp|bmp)/i.test(file.type) || /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(file.name);

      if (!isImage) {
        previewWrap.classList.add('d-none');
        previewImg.src = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = function (ev) {
        previewImg.src = ev.target.result;
        previewWrap.classList.remove('d-none');
      };
      reader.readAsDataURL(file);
    });
  })();
</script>
