<?php $errors = $errors ?? []; ?>
<?php $old = $old ?? []; ?>
<section class="auth-shell">
  <div class="auth-split">
    <div class="auth-side">
      <div class="auth-side-content">
        <p class="auth-eyebrow">Account recovery</p>
        <h1>Reset your password</h1>
        <p>Enter your email and we will send a secure reset link to help you regain access.</p>
        <a class="btn btn-light btn-lg mt-3" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>">Back to login</a>
        <a class="d-inline-block mt-3 text-white-50" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/', ENT_QUOTES, 'UTF-8') ?>">Back to home</a>
      </div>
    </div>

    <div class="auth-form-panel">
      <div class="logo mb-4 text-center">
        <h1>Instaweb</h1>
        <p>Forgot your password?</p>
      </div>

      <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/forgot-password', ENT_QUOTES, 'UTF-8') ?>" novalidate>
        <?= \App\Core\Csrf::field() ?>
        <div class="mb-4">
          <label class="form-label" for="email">Email address</label>
          <input class="form-control" id="email" name="email" type="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          <?php foreach ($errors['email'] ?? [] as $error): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
      </form>

      <p class="text-center mt-4 mb-0">
        <a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>">Back to login</a>
      </p>
    </div>
  </div>
</section>
