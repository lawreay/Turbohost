<?php $active = 'legal'; ?>
<div class="admin-pro-shell">
  <?php include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
      <div class="admin-pro-topbar-left">
        <p class="admin-pro-kicker">Legal management</p>
        <h1>Policies</h1>
      </div>
    </header>

  <div class="admin-pro-card">
    <p>Manage your platform policies, create versioned drafts, and publish the current policy content.</p>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Type</th>
            <th>Title</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($policies as $policy): ?>
            <tr>
              <td><?= htmlspecialchars($policy['policy_type'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($policy['title'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/legal/edit?id=' . $policy['id'], ENT_QUOTES, 'UTF-8') ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <a class="btn btn-primary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/legal/edit', ENT_QUOTES, 'UTF-8') ?>">Create new policy</a>
  </div>
</section>
</div>
