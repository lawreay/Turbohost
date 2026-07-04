<section class="maintenance-page">
  <div class="maintenance-panel">
    <div class="maintenance-icon">
      <i data-lucide="tools"></i>
    </div>
    <h1><?= htmlspecialchars($maintenance_page_title ?? 'We’ll be back soon', ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if (!empty($maintenance_status_label)): ?>
      <p class="maintenance-status"><?= htmlspecialchars($maintenance_status_label, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if (!empty($maintenance_message)): ?>
      <p class="maintenance-message"><?= nl2br(htmlspecialchars($maintenance_message, ENT_QUOTES, 'UTF-8')) ?></p>
    <?php endif; ?>

    <?php if (!empty($maintenance_return_at)): ?>
      <?php $returnAt = date('c', strtotime($maintenance_return_at)); ?>
      <div class="maintenance-countdown-card">
        <p>Expected return</p>
        <strong><?= htmlspecialchars(date('F j, Y \a\t g:i A', strtotime($maintenance_return_at)), ENT_QUOTES, 'UTF-8') ?></strong>
        <span data-maintenance-countdown data-return-at="<?= htmlspecialchars($returnAt, ENT_QUOTES, 'UTF-8') ?>">Calculating...</span>
      </div>
    <?php endif; ?>

    <?php if (!empty($maintenance_preview)): ?>
      <div class="maintenance-bypass">
        <p>Preview mode — bypass token validation is available to admins only.</p>
        <?php if (!empty($maintenance_bypass_token)): ?>
          <code><?= htmlspecialchars($maintenance_bypass_token, ENT_QUOTES, 'UTF-8') ?></code>
        <?php endif; ?>
      </div>
      <p class="maintenance-preview-banner">Preview mode — this page is visible only to admins.</p>
    <?php endif; ?>
  </div>
</section>
