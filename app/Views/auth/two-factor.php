<div class="auth-shell">
  <div class="auth-card">
    <div class="auth-card-header">
      <h1>Two-factor verification</h1>
      <p>Enter the code we sent to your email.</p>
    </div>

    <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/two-factor', ENT_QUOTES, 'UTF-8') ?>">
      <?= \App\Core\Csrf::field() ?>
      <div class="form-group">
        <label class="form-label" for="two_factor_code">Verification code</label>
        <input class="form-control" id="two_factor_code" name="two_factor_code" type="text" maxlength="6" required>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>" class="text-muted">Back to login</a>
        <button class="btn btn-primary" type="submit">Verify</button>
      </div>
    </form>
  </div>
</div>
