<?php /** @var string $name */ /** @var string $message */ ?>
<h2><?= htmlspecialchars($this->appConfig['name'] ?? 'Instaweb', ENT_QUOTES, 'UTF-8') ?> — Announcement</h2>
<p>Hello <?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>,</p>
<p><?= nl2br(htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
<p>
  <a href="<?= htmlspecialchars((string) ($this->appConfig['base_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/dashboard">Open your dashboard</a>
</p>
<p>If you have questions, reply to this email or contact the site administrators.</p>
