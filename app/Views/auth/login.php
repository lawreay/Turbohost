<?php $errors = $errors ?? []; ?>
<?php $old = $old ?? []; ?>
<section class="auth-shell">
  <div class="auth-split">
    <div class="auth-side">
      <div class="auth-side-content">
        <p class="auth-eyebrow">Secure access</p>
        <h1>Welcome back</h1>
        <p>Manage your websites, files, subscriptions, and analytics from one secure dashboard.</p>
        <a class="btn btn-light btn-lg mt-3" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/register', ENT_QUOTES, 'UTF-8') ?>">Create account</a>
        <a class="d-inline-block mt-3 text-white-50" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/', ENT_QUOTES, 'UTF-8') ?>">Back to home</a>
      </div>
    </div>

    <div class="auth-form-panel">
      <div class="logo mb-4 text-center">
        <h1>TurboHostMw</h1>
        <p>Sign in to continue</p>
      </div>

      <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>" novalidate>
        <?= \App\Core\Csrf::field() ?>
        <div class="mb-3">
          <label class="form-label" for="login">Email or username</label>
          <input class="form-control" id="login" name="login" value="<?= htmlspecialchars($old['login'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          <?php foreach ($errors['login'] ?? [] as $error): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endforeach; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input class="form-control" id="password" name="password" type="password" required>
          <?php foreach ($errors['password'] ?? [] as $error): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endforeach; ?>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
          <div class="form-check">
            <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/forgot-password', ENT_QUOTES, 'UTF-8') ?>">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <p class="text-center text-secondary mt-4 mb-0">
        Need an account?
        <a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/register', ENT_QUOTES, 'UTF-8') ?>">Create one</a>
      </p>
    </div>
  </div>
</section>
