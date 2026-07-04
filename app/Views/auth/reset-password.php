<?php $errors = $errors ?? []; ?>
<section class="auth-shell">
  <div class="auth-card">
    <div class="mb-4">
      <p class="auth-eyebrow">Choose new password</p>
      <h1 class="h3 mb-2">Reset password</h1>
      <p class="text-secondary mb-0">Use at least 8 characters for your new password.</p>
    </div>

    <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/reset-password', ENT_QUOTES, 'UTF-8') ?>" novalidate>
      <?= \App\Core\Csrf::field() ?>
      <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

      <?php foreach ($errors['token'] ?? [] as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endforeach; ?>

      <div class="mb-3">
        <label class="form-label" for="password">New password</label>
        <input class="form-control" id="password" name="password" type="password" required>
        <?php foreach ($errors['password'] ?? [] as $error): ?>
          <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
      </div>

      <div class="mb-4">
        <label class="form-label" for="confirm_password">Confirm new password</label>
        <input class="form-control" id="confirm_password" name="confirm_password" type="password" required>
        <?php foreach ($errors['confirm_password'] ?? [] as $error): ?>
          <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn btn-primary w-100">Update password</button>
    </form>
  </div>
</section>
