<div class="row g-3 profile-form-grid">
  <div class="col-md-12">
    <label class="form-label" for="fullname">Full name</label>
    <input class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="150">
  </div>
  <div class="col-md-6">
    <label class="form-label" for="phone">Phone</label>
    <input class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="30">
  </div>
  <div class="col-md-6">
    <label class="form-label" for="country">Country</label>
    <input class="form-control" id="country" name="country" value="<?= htmlspecialchars($user['country'] ?? '', ENT_QUOTES, 'UTF-8') ?>" list="country-list" maxlength="100">
    <datalist id="country-list">
      <?php foreach (['Malawi', 'Zambia', 'Mozambique', 'Tanzania', 'South Africa', 'Kenya', 'Nigeria', 'Ghana', 'United Kingdom', 'United States'] as $country): ?>
        <option value="<?= htmlspecialchars($country, ENT_QUOTES, 'UTF-8') ?>">
      <?php endforeach; ?>
    </datalist>
  </div>
  <div class="col-12">
    <label class="form-label" for="bio">Bio</label>
    <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="500" placeholder="Short profile note for support context."><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>
  <div class="col-12 text-end">
    <button class="btn btn-primary profile-save-button" type="submit"><i data-lucide="save"></i> Save profile</button>
  </div>
</div>
