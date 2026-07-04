<div class="admin-pro-shell">
  <?php $active = 'users'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
      <div class="admin-pro-topbar-left">
        <p class="admin-pro-kicker">Account management</p>
        <h1>Users</h1>
      </div>
      <div class="admin-pro-topbar-right admin-pro-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/register', ENT_QUOTES, 'UTF-8') ?>"><i data-lucide="user-plus"></i> Add user</a>
      </div>
    </header>

    <div class="admin-pro-card">
      <div class="admin-pro-card-head">
        <h2>All Users</h2>
        <span><?= count($users) ?> records</span>
      </div>
      <div class="table-responsive">
        <table class="admin-pro-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Contact</th>
              <th>Country</th>
              <th>Role</th>
              <th>Plan</th>
              <th>Status</th>
              <th>Verified</th>
              <th>Joined</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <?php $currentPlan = $user['current_plan'] ?? ((in_array($user['role'], ['admin', 'moderator', 'premium'], true)) ? 'premium' : 'free'); ?>
              <tr>
                <td><strong><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></strong><small>@<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></small></td>
                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?><small><?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                <td><?= htmlspecialchars($user['country'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="status-chip"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><span class="plan-chip <?= htmlspecialchars($currentPlan, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($currentPlan), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><span class="status-chip <?= htmlspecialchars($user['account_status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($user['account_status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= ((int) $user['email_verified']) === 1 ? 'Yes' : 'No' ?></td>
                <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-primary me-1" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users/edit?id=' . (int) $user['id'], ENT_QUOTES, 'UTF-8') ?>"><i data-lucide="square-pen"></i> Edit</a>
                  <?php if ((int) $user['id'] !== \App\Services\AuthService::id()): ?>
                    <?php if (!in_array($user['role'], ['admin', 'moderator'], true)): ?>
                      <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users/change-plan', ENT_QUOTES, 'UTF-8') ?>" class="d-inline-block me-1">
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                        <input type="hidden" name="plan" value="<?= $currentPlan === 'premium' ? 'free' : 'premium' ?>">
                        <button class="btn btn-sm <?= $currentPlan === 'premium' ? 'btn-outline-secondary' : 'btn-outline-success' ?>" type="submit" onclick="return confirm('Are you sure you want to <?= $currentPlan === 'premium' ? 'move this user to Free plan' : 'upgrade this user to Premium' ?>?')">
                          <i data-lucide="<?= $currentPlan === 'premium' ? 'chevrons-down' : 'award' ?>"></i>
                          <?= $currentPlan === 'premium' ? 'Free' : 'Premium' ?>
                        </button>
                      </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users/reset-role', ENT_QUOTES, 'UTF-8') ?>" class="d-inline-block me-1">
                      <?= \App\Core\Csrf::field() ?>
                      <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                      <button class="btn btn-sm btn-outline-secondary" type="submit" onclick="return confirm('Reset this user role to User?')"><i data-lucide="user-check"></i></button>
                    </form>
                    <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users/delete', ENT_QUOTES, 'UTF-8') ?>" class="d-inline-block">
                      <?= \App\Core\Csrf::field() ?>
                      <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this user account?')"><i data-lucide="trash-2"></i></button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if ($users === []): ?>
              <tr><td colspan="8">No users found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <footer class="admin-pro-footer">
      <span><?= htmlspecialchars('User directory refreshed ' . date('d M Y'), ENT_QUOTES, 'UTF-8') ?></span>
      <span><a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/settings', ENT_QUOTES, 'UTF-8') ?>">Manage user settings</a></span>
    </footer>
  </section>
</div>
