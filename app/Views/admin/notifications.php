<div class="admin-pro-shell">
  <?php $active = 'notifications'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
     
      <div class="admin-pro-topbar-right admin-pro-actions">
        <a class="btn btn-outline-primary" href="#" onclick="document.querySelector('form[action*=broadcast]').scrollIntoView({ behavior: 'smooth' }); return false;"><i data-lucide="send"></i> Broadcast</a>
      </div>
    </header>

    <div class="admin-pro-card">
      <div class="admin-pro-card-head">
        <h2>Broadcast Notification</h2>
      </div>
      <form action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/notifications/broadcast', ENT_QUOTES, 'UTF-8') ?>" method="post" class="admin-pro-form mb-4">
        <?= \App\Core\Csrf::field() ?>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="broadcast_title">Title</label>
            <input class="form-control" id="broadcast_title" name="broadcast_title" type="text" placeholder="Announcement title" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="broadcast_message">Message</label>
            <input class="form-control" id="broadcast_message" name="broadcast_message" type="text" placeholder="Notification message" required>
          </div>
        </div>
        <div class="mt-3">
          <label class="settings-check me-3"><input type="checkbox" name="broadcast_send_email" value="1"> Send as email</label>
          <button type="submit" class="btn btn-primary">Send Broadcast</button>
        </div>
      </form>

      <div class="admin-pro-card-head">
        <h2>Recent Notifications</h2>
        <span><?= count($notifications) ?> records</span>
      </div>
      <div class="table-responsive">
        <table class="admin-pro-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>User</th>
              <th>Message</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($notifications as $notification): ?>
              <tr>
                <td><strong><?= htmlspecialchars($notification['title'] ?? 'Notification', ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= htmlspecialchars($notification['fullname'] ?? 'System', ENT_QUOTES, 'UTF-8') ?><small><?= htmlspecialchars($notification['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                <td><?= htmlspecialchars($notification['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="status-chip <?= ((int) $notification['is_read']) === 1 ? 'active' : 'pending' ?>"><?= ((int) $notification['is_read']) === 1 ? 'Read' : 'Unread' ?></span></td>
                <td><?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if ($notifications === []): ?>
              <tr><td colspan="5">No notifications found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <footer class="admin-pro-footer">
      <span>Last synced <?= htmlspecialchars(date('d M Y'), ENT_QUOTES, 'UTF-8') ?></span>
      <span><a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/settings', ENT_QUOTES, 'UTF-8') ?>">Notification settings</a></span>
    </footer>
  </section>
</div>
