<section class="auth-shell">
  <div class="auth-card text-center">
    <?php if ($verified): ?>
      <p class="auth-eyebrow">Email confirmed</p>
      <h1 class="h3 mb-3">Your account is verified</h1>
      <p class="text-secondary">You can now sign in and start creating hosted websites.</p>
      <a class="btn btn-primary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>">Login</a>
    <?php else: ?>
      <p class="auth-eyebrow">Verification failed</p>
      <h1 class="h3 mb-3">Invalid or expired link</h1>
      <p class="text-secondary">Please register again or request a fresh verification email when that feature is available.</p>
      <a class="btn btn-outline-primary" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>">Back to login</a>
    <?php endif; ?>
  </div>
</section>
