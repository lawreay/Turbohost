<?php /** @var array $policies */ /** @var string $name */ /** @var string $message */ ?>
<h2><?= htmlspecialchars($this->appConfig['name'] ?? 'Instaweb', ENT_QUOTES, 'UTF-8') ?> — Policy update</h2>
<p>Hello <?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>,</p>
<p><?= htmlspecialchars($message ?? 'A platform policy was updated.', ENT_QUOTES, 'UTF-8') ?></p>
<?php if (!empty($policies) && is_array($policies)): ?>
  <ul>
    <?php foreach ($policies as $p): ?>
      <li><strong><?= htmlspecialchars($p['title'] ?? ($p['policy_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> — Version <?= htmlspecialchars($p['version_label'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
<p>
  Please review the updated policies and accept them on your dashboard to continue using the platform.
</p>
<p>
  <a href="<?= htmlspecialchars((string) ($this->appConfig['base_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/legal/accept">Review and accept policies</a>
</p>
<p>If you have questions, contact the site administrators.</p>
