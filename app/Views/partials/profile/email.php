<div class="profile-compact-form">
  <div>
    <label class="form-label">Current email</label>
    <div class="profile-readonly"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></div>
  </div>

  <label class="form-label" for="new_email">New email</label>
  <input class="form-control" id="new_email" type="email" name="new_email" autocomplete="email">

  <label class="form-label" for="email_current_password">Current password</label>
  <input class="form-control" id="email_current_password" type="password" name="current_password" autocomplete="current-password">

  <button class="btn btn-primary w-100" type="submit"><i data-lucide="mail-check"></i> Request email change</button>
</div>
