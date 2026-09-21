<?php /** @var string $name */ ?>
<h2>Password reset requested</h2>
<p>Hello <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>,</p>
<p>We received a request to reset your Instaweb password. If you made this request, use the link provided in the email to choose a new password.</p>
<p>If you did not request a password reset, please ignore this message or contact support.</p>
<p>Best regards,<br>Instaweb Team</p>
