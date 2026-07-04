<div class="profile-danger-zone">
  <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/profile/deactivate', ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Core\Csrf::field() ?>
    <div>
      <strong>Deactivate account</strong>
      <p>Suspends sign-in after email confirmation.</p>
    </div>
    <button class="btn btn-outline-danger" type="submit"><i data-lucide="user-x"></i> Request</button>
  </form>
</div>
