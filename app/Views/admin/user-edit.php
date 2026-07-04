<?php
$joinedAt = isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A';
$updatedAt = isset($user['updated_at']) ? date('F j, Y g:i A', strtotime($user['updated_at'])) : 'N/A';
?>
<div class="admin-pro-shell">
  <?php $active = 'users'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
      <div class="admin-pro-topbar-left">
        <p class="admin-pro-kicker">Account management</p>
        <h1>Edit User</h1>
      </div>
      <div class="admin-pro-topbar-right admin-pro-actions">
        <a class="btn btn-outline-primary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users', ENT_QUOTES, 'UTF-8') ?>"><i data-lucide="arrow-left"></i> Back to users</a>
      </div>
    </header>

    <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $flashKey => $type): ?>
      <?php if ($message = \App\Core\Session::pullFlash($flashKey)): ?>
        <div class="alert alert-<?= $type ?> shadow-sm" role="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
    <?php endforeach; ?>

    <div class="profile-layout">
      <section class="admin-pro-card profile-panel">
        <div class="admin-pro-card-head">
          <div>
            <h2><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></h2>
            <span>Edit profile, login identity, role, and account access.</span>
          </div>
          <span class="status-chip <?= htmlspecialchars($user['account_status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($user['account_status'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users/update', ENT_QUOTES, 'UTF-8') ?>">
          <?= \App\Core\Csrf::field() ?>
          <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">

          <div class="row g-3 profile-form-grid">
            <div class="col-md-6">
              <label class="form-label" for="fullname">Full name</label>
              <input class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="150">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="username">Username</label>
              <input class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="50">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" id="email" type="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="150">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="phone">Phone</label>
              <input class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="30">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="country">Country</label>
              <input class="form-control" id="country" name="country" value="<?= htmlspecialchars($user['country'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="email_verified">Email verification</label>
              <select class="form-select" id="email_verified" name="email_verified">
                <option value="1" <?= ((int) $user['email_verified']) === 1 ? 'selected' : '' ?>>Verified</option>
                <option value="0" <?= ((int) $user['email_verified']) === 0 ? 'selected' : '' ?>>Unverified</option>
              </select>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2 mb-2">
                <label class="form-label mb-0" for="role">Role</label>
                <?php if (!$isEditingSelf): ?>
                  <button type="submit" name="reset_role" value="1" class="btn btn-sm btn-outline-secondary">Reset to User</button>
                <?php endif; ?>
              </div>
              <select class="form-select" id="role" name="role" <?= $isEditingSelf ? 'disabled' : '' ?>>
                <?php foreach ($roles as $value => $label): ?>
                  <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $user['role'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($isEditingSelf): ?><small class="profile-help">Your own role is locked while editing yourself.</small><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="account_status">Account status</label>
              <select class="form-select" id="account_status" name="account_status" <?= $isEditingSelf ? 'disabled' : '' ?>>
                <?php foreach ($statuses as $value => $label): ?>
                  <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $user['account_status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($isEditingSelf): ?><small class="profile-help">Your own account status is protected.</small><?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="bio">Bio</label>
              <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="500"><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-12 text-end">
              <button class="btn btn-primary profile-save-button" type="submit"><i data-lucide="save"></i> Save user</button>
            </div>
          </div>
        </form>

        <?php if (!$isEditingSelf): ?>
          <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/users/delete', ENT_QUOTES, 'UTF-8') ?>" class="mt-3">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
            <button class="btn btn-outline-danger w-100" type="submit" onclick="return confirm('Delete this user account? This cannot be undone.')"><i data-lucide="trash-2"></i> Delete user</button>
          </form>
        <?php endif; ?>
      </section>

      <aside class="profile-side">
        <section class="admin-pro-card">
          <div class="admin-user-profile-card">
            <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.png'): ?>
              <img src="<?= htmlspecialchars(($app['base_url'] ?? '') . '/' . $user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="User avatar">
            <?php else: ?>
              <span><?= htmlspecialchars(strtoupper(substr((string) $user['fullname'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <strong><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></strong>
            <small>@<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></small>
          </div>
        </section>

        <section class="admin-pro-card">
          <div class="admin-pro-card-head"><h2>Account summary</h2></div>
          <div class="profile-summary-list">
            <div><span>Joined</span><strong><?= htmlspecialchars($joinedAt, ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Updated</span><strong><?= htmlspecialchars($updatedAt, ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Email</span><strong><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Current plan</span><strong><?= htmlspecialchars(ucfirst($currentPlan), ENT_QUOTES, 'UTF-8') ?></strong></div>
          </div>
        </section>
      </aside>
    </div>
  </section>
</div>
