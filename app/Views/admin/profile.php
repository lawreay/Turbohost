<?php
$joinedAt = isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A';
$isVerified = ((int) ($user['email_verified'] ?? 0)) === 1;
$status = (string) ($user['account_status'] ?? 'pending');
?>
<div class="admin-pro-shell">
  <?php $active = $active ?? 'profile'; include APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

  <section class="admin-pro-main">
    <header class="admin-pro-topbar">
      <div class="admin-pro-topbar-left">
        <p class="admin-pro-kicker">User profile</p>
        <h1>My Profile</h1>
      </div>
      <div class="admin-pro-topbar-right admin-pro-actions">
        <div class="admin-pro-user">
          <span><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></span>
          <strong><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
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
            <h2>Account information</h2>
            <span>Keep your administrator identity and support contact details current.</span>
          </div>
          <div class="profile-status-stack">
            <span class="status-chip <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="status-chip <?= $isVerified ? 'active' : 'pending' ?>"><?= $isVerified ? 'Verified' : 'Unverified' ?></span>
          </div>
        </div>

        <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/profile/update', ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
          <?= \App\Core\Csrf::field() ?>
          <div class="row g-4">
            <div class="col-lg-4">
              <?php include APP_PATH . '/Views/partials/profile/avatar.php'; ?>
            </div>
            <div class="col-lg-8">
              <?php include APP_PATH . '/Views/partials/profile/details.php'; ?>
            </div>
          </div>
        </form>
      </section>

      <aside class="profile-side">
        <section class="admin-pro-card">
          <div class="admin-pro-card-head"><h2>Account summary</h2></div>
          <div class="profile-summary-list">
            <div><span>Joined</span><strong><?= htmlspecialchars($joinedAt, ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Username</span><strong>@<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Email</span><strong><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></strong></div>
          </div>
        </section>

        <section class="admin-pro-card">
          <div class="admin-pro-card-head"><h2>Security</h2></div>
          <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/profile/change-password', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <?php include APP_PATH . '/Views/partials/profile/security.php'; ?>
          </form>
        </section>

        <section class="admin-pro-card">
          <div class="admin-pro-card-head"><h2>Email & account</h2></div>
          <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/profile/change-email', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <?php include APP_PATH . '/Views/partials/profile/email.php'; ?>
          </form>
          <?php include APP_PATH . '/Views/partials/profile/deactivate.php'; ?>
        </section>
      </aside>
    </div>
    <footer class="admin-pro-footer">
      <span>Profile details updated <?= htmlspecialchars(date('d M Y'), ENT_QUOTES, 'UTF-8') ?></span>
      <span><a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/admin/settings', ENT_QUOTES, 'UTF-8') ?>">Edit platform settings</a></span>
    </footer>
  </section>
</div>
