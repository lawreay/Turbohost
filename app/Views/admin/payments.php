<div class="admin-pro-shell">
  <?php $active = 'payments'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
      <div class="admin-pro-topbar-left">
        <p class="admin-pro-kicker">Billing records</p>
        <h1>Payments</h1>
      </div>
      <div class="admin-pro-topbar-right admin-pro-actions">
        <span class="text-secondary">Total payments: <?= count($payments) ?></span>
      </div>
    </header>

    <div class="admin-pro-card">
      <div class="admin-pro-card-head">
        <h2>Payment History</h2>
        <span><?= count($payments) ?> records</span>
      </div>
      <div class="table-responsive">
        <table class="admin-pro-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Transaction</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $payment): ?>
              <tr>
                <td><strong><?= htmlspecialchars($payment['fullname'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($payment['email'], ENT_QUOTES, 'UTF-8') ?></small></td>
                <td><?= htmlspecialchars($payment['currency'], ENT_QUOTES, 'UTF-8') ?> <?= number_format((float) $payment['amount'], 2) ?></td>
                <td><?= htmlspecialchars($payment['payment_method'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?= htmlspecialchars($payment['transaction_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                  <?php if (!empty($payment['tx_ref'])): ?><small><?= htmlspecialchars($payment['tx_ref'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                </td>
                <td><span class="status-chip <?= htmlspecialchars($payment['payment_status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($payment['payment_status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars($payment['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if ($payments === []): ?>
              <tr><td colspan="6">No payments found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <footer class="admin-pro-footer">
      <span>Payments audited <?= htmlspecialchars(date('d M Y'), ENT_QUOTES, 'UTF-8') ?></span>
      <span><a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/reports', ENT_QUOTES, 'UTF-8') ?>">Review issues</a></span>
    </footer>
  </section>
</div>
