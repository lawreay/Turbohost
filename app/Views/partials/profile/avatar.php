<?php
$avatar = (string) ($user['avatar'] ?? '');
$hasAvatar = $avatar !== '' && $avatar !== 'default.png';
?>
<div class="profile-avatar-panel">
  <div class="profile-avatar-preview">
    <?php if ($hasAvatar): ?>
      <img src="<?= htmlspecialchars(($app['base_url'] ?? '') . '/' . $avatar, ENT_QUOTES, 'UTF-8') ?>" alt="Profile avatar">
    <?php else: ?>
      <span><?= htmlspecialchars(strtoupper(substr((string) $user['fullname'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
  </div>
  <label class="form-label" for="avatar">Profile photo</label>
  <input id="avatar" type="file" name="avatar" accept="image/png,image/jpeg,image/gif,image/bmp,image/webp" class="form-control">
  <small>PNG, JPG, GIF, BMP, or WebP. Max 2MB.</small>
</div>
