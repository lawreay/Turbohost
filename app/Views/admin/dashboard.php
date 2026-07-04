<div class="admin-pro-shell">
  <?php $active = 'dashboard'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
      
      <div class="admin-pro-topbar-right">
        <form class="admin-pro-search" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users', ENT_QUOTES, 'UTF-8') ?>" method="GET">
          <i data-lucide="search"></i>
          <input type="search" name="q" placeholder="Search users, websites, payments" aria-label="Search admin" />
          <button type="submit"><i data-lucide="arrow-right"></i></button>
        </form>
        <button type="button" class="admin-pro-icon-btn" aria-label="Notifications">
          <i data-lucide="bell"></i>
          <span class="icon-badge"></span>
        </button>
        <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users', ENT_QUOTES, 'UTF-8') ?>">
          <i data-lucide="plus"></i> Add user
        </a>
      </div>
    </header>

    <div class="admin-pro-summary-panel">
      <div class="admin-pro-card">
        <div>
          <span>Users</span>
          <strong><?= number_format($stats['users']) ?></strong>
        </div>
        <i data-lucide="users"></i>
      </div>
      <div class="admin-pro-card">
        <div>
          <span>Websites</span>
          <strong><?= number_format($stats['websites']) ?></strong>
        </div>
        <i data-lucide="globe-2"></i>
      </div>
      <div class="admin-pro-card">
        <div>
          <span>Revenue</span>
          <strong>MWK <?= number_format($stats['paid_revenue'], 2) ?></strong>
        </div>
        <i data-lucide="banknote"></i>
      </div>
      <div class="admin-pro-card">
        <div>
          <span>Reports</span>
          <strong><?= number_format($stats['reports']) ?></strong>
        </div>
        <i data-lucide="flag"></i>
      </div>
    </div>

    <?php
      $siteValue = max(1, (int) ($stats['websites'] ?? 0));
      $revenueValue = max(1.0, (float) ($stats['paid_revenue'] ?? 0));
      $chartMax = max($siteValue, $revenueValue);
      $siteHeight = 110 * min(1, $siteValue / $chartMax);
      $revenueHeight = 110 * min(1, $revenueValue / $chartMax);
      $revenuePerSite = $siteValue > 0 ? $revenueValue / $siteValue : 0;
    ?>

    <div class="row g-4">
      <div class="col-xl-8">
        <div class="admin-pro-card mb-4">
          <div class="admin-pro-card-head">
            <h2>Revenue vs Sites</h2>
            <span>Current snapshot</span>
          </div>
          <div class="revenue-sites-chart">
            <svg viewBox="0 0 320 180" role="img" aria-label="Revenue versus number of sites chart">
              <line x1="30" y1="145" x2="292" y2="145" stroke="var(--surface-border, #dbe3ee)" stroke-width="2" />
              <line x1="30" y1="35" x2="30" y2="145" stroke="var(--surface-border, #dbe3ee)" stroke-width="2" />
              <rect x="72" y="<?= 145 - $siteHeight ?>" width="72" height="<?= $siteHeight ?>" rx="10" fill="var(--app-primary, #0d6efd)" />
              <rect x="176" y="<?= 145 - $revenueHeight ?>" width="72" height="<?= $revenueHeight ?>" rx="10" fill="var(--app-secondary, #00b4d8)" />
              <text x="108" y="166" text-anchor="middle" class="chart-label">Sites</text>
              <text x="212" y="166" text-anchor="middle" class="chart-label">Revenue</text>
              <text x="108" y="<?= 138 - $siteHeight ?>" text-anchor="middle" class="chart-value"><?= number_format($siteValue) ?></text>
              <text x="212" y="<?= 138 - $revenueHeight ?>" text-anchor="middle" class="chart-value">MWK <?= number_format($revenueValue, 0) ?></text>
            </svg>
            <div class="chart-footnote">
              <span>Revenue per site</span>
              <strong>MWK <?= number_format($revenuePerSite, 2) ?></strong>
            </div>
          </div>
        </div>
        <div class="admin-pro-card">
          <div class="admin-pro-card-head">
            <h2>Recent Users</h2>
            <span><?= count($recentUsers) ?> shown</span>
          </div>
          <div class="table-responsive">
            <table class="admin-pro-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Verified</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentUsers as $user): ?>
                  <tr>
                    <td>
                      <strong><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></strong>
                      <small>@<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="status-chip"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><span class="status-chip <?= htmlspecialchars($user['account_status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($user['account_status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= ((int) $user['email_verified']) === 1 ? 'Yes' : 'No' ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if ($recentUsers === []): ?>
                  <tr><td colspan="5">No users found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-xl-4">
        <div class="admin-pro-card h-100">
          <div class="admin-pro-card-head">
            <h2>Payments</h2>
            <span>Summary</span>
          </div>
          <div class="admin-pro-summary-list">
            <?php foreach ($paymentSummary as $payment): ?>
              <div>
                <span><?= htmlspecialchars($payment['payment_status'], ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= number_format((float) $payment['amount'], 2) ?></strong>
              </div>
            <?php endforeach; ?>
            <?php if ($paymentSummary === []): ?>
              <p class="text-secondary mb-0">No payment records yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="admin-pro-card">
          <div class="admin-pro-card-head">
            <h2>Recent Websites</h2>
            <span>Hosting records</span>
          </div>
          <div class="table-responsive">
            <table class="admin-pro-table">
              <thead>
                <tr>
                  <th>Website</th>
                  <th>Owner</th>
                  <th>Status</th>
                  <th>Storage</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentWebsites as $website): ?>
                  <tr>
                    <td>
                      <strong><?= htmlspecialchars($website['website_name'] ?: 'Untitled website', ENT_QUOTES, 'UTF-8') ?></strong>
                      <small><?= htmlspecialchars($website['subdomain'] ?: $website['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td><?= htmlspecialchars($website['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="status-chip <?= htmlspecialchars($website['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($website['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= number_format(((int) $website['storage_used']) / 1048576, 2) ?> MB</td>
                    <td><?= htmlspecialchars($website['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if ($recentWebsites === []): ?>
                  <tr><td colspan="5">No websites found yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <footer class="admin-pro-footer">
      <span>Dashboard updated <?= htmlspecialchars(date('d M Y'), ENT_QUOTES, 'UTF-8') ?></span>
      <span>Need help? <a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/settings', ENT_QUOTES, 'UTF-8') ?>">System settings</a></span>
    </footer>
  </section>
</div>
