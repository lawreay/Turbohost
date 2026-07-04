<div class="admin-pro-shell">
  <?php $active = 'reports'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
      <div class="admin-pro-topbar-left">
        <p class="admin-pro-kicker">Safety queue</p>
        <h1>Reports</h1>
      </div>
      <div class="admin-pro-topbar-right admin-pro-actions">
        <span class="text-secondary">Pending: <?= number_format($pendingReports ?? 0) ?></span>
      </div>
    </header>

    <div class="admin-pro-card">
      <div class="admin-pro-card-head">
        <h2>Abuse Reports</h2>
        <span><?= count($reports) ?> records</span>
      </div>
      <div class="table-responsive">
        <table class="admin-pro-table">
          <thead>
            <tr>
              <th>Website</th>
              <th>Reporter</th>
              <th>Reason</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reports as $report): ?>
              <tr>
                <td><strong><?= htmlspecialchars($report['website_name'] ?: 'Unknown website', ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($report['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                <td><?= htmlspecialchars($report['reporter_name'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?><small><?= htmlspecialchars($report['reporter_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                <td><?= htmlspecialchars($report['reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="status-chip <?= htmlspecialchars($report['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($report['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars($report['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if ($reports === []): ?>
              <tr><td colspan="5">No reports found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <footer class="admin-pro-footer">
      <span>Report queue updated <?= htmlspecialchars(date('d M Y'), ENT_QUOTES, 'UTF-8') ?></span>
      <span><a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/notifications', ENT_QUOTES, 'UTF-8') ?>">Send alert</a></span>
    </footer>
  </section>
</div>
