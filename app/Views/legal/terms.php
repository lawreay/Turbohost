<?php
$value = static fn (string $key, string $default = ''): string => htmlspecialchars($settings[$key] ?? $default, ENT_QUOTES, 'UTF-8');
?>
<section class="page-hero page-hero-legal">
  <div class="container">
    <h1><?= htmlspecialchars($policy['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead">Last updated: <?= htmlspecialchars($policy['published_at'], ENT_QUOTES, 'UTF-8') ?></p>
  </div>
</section>
<section class="page-content container">
  <div class="legal-content">
    <?= $policy['content'] ?>
  </div>
</section>
