<?php $active = 'legal'; ?>
<div class="admin-pro-shell">
  <?php include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/legal/save', ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Core\Csrf::field() ?>
    <header class="admin-pro-topbar">
      <div class="admin-pro-topbar-left">
        <p class="admin-pro-kicker">Legal policy editor</p>
        <h1><?= htmlspecialchars($policy['title'] ?? 'Create Policy', ENT_QUOTES, 'UTF-8') ?></h1>
      </div>
      <div class="admin-pro-topbar-right admin-pro-actions">
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/legal', ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
        <button class="btn btn-primary" type="submit">Save Version</button>
      </div>
    </header>

    <input type="hidden" name="policy_id" value="<?= htmlspecialchars($policy['id'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Policy type</label>
        <select class="form-control" name="policy_type" required>
          <option value="privacy" <?= (isset($policy['policy_type']) && $policy['policy_type'] === 'privacy') ? 'selected' : '' ?>>Privacy</option>
          <option value="terms" <?= (isset($policy['policy_type']) && $policy['policy_type'] === 'terms') ? 'selected' : '' ?>>Terms</option>
          <option value="cookie" <?= (isset($policy['policy_type']) && $policy['policy_type'] === 'cookie') ? 'selected' : '' ?>>Cookie Policy</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Policy title</label>
        <input class="form-control" name="title" value="<?= htmlspecialchars($policy['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Version label</label>
        <input class="form-control" name="version_label" value="" placeholder="e.g. v1.0 or 2026-07-02" required>
      </div>
    </div>

    <div class="mt-3">
      <label class="form-label">Policy content</label>
      <textarea class="form-control" name="content" rows="12" required><?= htmlspecialchars($policy['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="row g-3 mt-3">
      <div class="col-md-4">
        <label class="form-label">Publish this version</label>
        <select class="form-control" name="publish">
          <option value="0">Save as draft</option>
          <option value="1">Publish now</option>
        </select>
      </div>
    </div>

    <div class="mt-4">
      <button class="btn btn-primary" type="submit">Save Policy Version</button>
    </div>
  </form>

  <?php if (!empty($versions)): ?>
    <section class="admin-pro-card mt-4">
      <h2>Version history</h2>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Version</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($versions as $version): ?>
              <tr>
                <td><?= htmlspecialchars($version['version_label'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $version['published'] === '1' ? 'Published' : 'Draft' ?></td>
                <td><?= htmlspecialchars($version['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ($version['published'] !== '1'): ?>
                    <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/legal/publish', ENT_QUOTES, 'UTF-8') ?>" class="d-inline-block">
                      <?= \App\Core\Csrf::field() ?>
                      <input type="hidden" name="version_id" value="<?= htmlspecialchars($version['id'], ENT_QUOTES, 'UTF-8') ?>">
                      <button class="btn btn-sm btn-outline-success" type="submit">Publish</button>
                    </form>
                  <?php else: ?>
                    <span class="badge bg-success">Published</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>
</section>
</div>
