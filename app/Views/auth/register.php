<?php $errors = $errors ?? []; ?>
<?php $old = $old ?? []; ?>
<section class="auth-shell">
  <div class="auth-split auth-split-wide">
    <div class="auth-side">
      <div class="auth-side-content">
        <p class="auth-eyebrow">Start hosting</p>
        <h1>Create your account</h1>
        <p>Register in minutes, verify your email, and launch your first website with Instaweb.</p>
        <a class="btn btn-light btn-lg mt-3" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>">Back to login</a>
        <a class="d-inline-block mt-3 text-white-50" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/', ENT_QUOTES, 'UTF-8') ?>">Back to home</a>
      </div>
    </div>

    <div class="auth-form-panel">
      <div class="logo mb-4 text-center">
        <h1>Instaweb</h1>
        <p>Begin with a free account</p>
      </div>

      <?php $registrationEnabled = $registrationEnabled ?? true; ?>
      <?php if (!$registrationEnabled): ?>
        <div class="alert alert-warning">Public registration is currently disabled. Please check back later or contact support.</div>
      <?php endif; ?>

      <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/register', ENT_QUOTES, 'UTF-8') ?>" novalidate>
        <?= \App\Core\Csrf::field() ?>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="fullname">Full name</label>
            <input class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($old['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php foreach ($errors['fullname'] ?? [] as $error): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php foreach ($errors['username'] ?? [] as $error): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" name="email" type="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php foreach ($errors['email'] ?? [] as $error): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="phone">Phone number</label>
            <input class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php foreach ($errors['phone'] ?? [] as $error): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>

          <div class="col-12">
            <label class="form-label" for="country">Country</label>
            <input class="form-control" id="country" name="country" list="country-options" value="<?= htmlspecialchars($old['country'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <datalist id="country-options">
              <option value="Malawi">
              <option value="South Africa">
              <option value="Zambia">
              <option value="Mozambique">
              <option value="Tanzania">
              <option value="Kenya">
              <option value="Nigeria">
              <option value="United States">
              <option value="United Kingdom">
            </datalist>
            <?php foreach ($errors['country'] ?? [] as $error): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" required>
            <?php foreach ($errors['password'] ?? [] as $error): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="confirm_password">Confirm password</label>
            <input class="form-control" id="confirm_password" name="confirm_password" type="password" required>
            <?php foreach ($errors['confirm_password'] ?? [] as $error): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-4" <?= !$registrationEnabled ? 'disabled' : '' ?>>Create account</button>
      </form>

      <p class="text-center text-secondary mt-4 mb-0">
        Already registered?
        <a href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/login', ENT_QUOTES, 'UTF-8') ?>">Login</a>
      </p>
    </div>
  </div>
</section>
