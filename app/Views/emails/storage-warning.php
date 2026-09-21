<?php /** @var string $name */ ?>
<h2>Storage usage alert</h2>
<p>Hello <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>,</p>
<p><?= htmlspecialchars($message ?? 'Your storage usage is high. Please remove files or upgrade your plan to continue using Instaweb without interruption.', ENT_QUOTES, 'UTF-8') ?></p>
<p>Visit your dashboard to manage storage and keep your website running smoothly.</p>
<p>Best regards,<br>Instaweb Team</p>
