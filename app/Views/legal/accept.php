<?php $baseUrl = rtrim(($app['base_url'] ?? ''), '/'); ?>
<section class="page-hero page-hero-legal">
  <div class="container">
    <h1>Accept updated policies</h1>
    <p class="lead">To continue using Instaweb, please review and accept the latest published policies below.</p>
  </div>
</section>
<section class="page-content container">
  <?php foreach ($policies as $policy): ?>
    <article class="legal-policy-card">
      <h2><?= htmlspecialchars($policy['title'], ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="muted">Version: <?= htmlspecialchars($policy['version_label'], ENT_QUOTES, 'UTF-8') ?> · Published <?= htmlspecialchars($policy['published_at'], ENT_QUOTES, 'UTF-8') ?></p>
      <div class="legal-content"><?= $policy['content'] ?></div>
    </article>
  <?php endforeach; ?>

  <form method="POST" action="<?= htmlspecialchars($baseUrl . '/legal/accept', ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Core\Csrf::field() ?>
    <?php foreach ($policies as $policy): ?>
      <input type="hidden" name="accepted_policy_types[]" value="<?= htmlspecialchars($policy['policy_type'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <button class="btn btn-primary mt-4" type="submit">Accept All Policies</button>
  </form>
</section>
