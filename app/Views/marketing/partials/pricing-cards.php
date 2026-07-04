<div class="row g-4 align-items-stretch">
  <?php $plans = $planDetails ?? (new \App\Services\PlanService($settings ?? []))->plans(); ?>
  <?php $freePlan = $plans['free']; ?>
  <?php $premiumPlan = $plans['premium']; ?>
  <?php $freeEnabled = ($settings['free_plan_enabled'] ?? '1') === '1'; ?>
  <?php $premiumEnabled = ($settings['premium_plan_enabled'] ?? '1') === '1'; ?>
  <div class="col-lg-6">
    <div class="pricing-card h-100<?= $freeEnabled ? '' : ' pricing-card-disabled' ?>">
      <h3><?= htmlspecialchars($freePlan['name'], ENT_QUOTES, 'UTF-8') ?></h3>
      <p class="price"><?= htmlspecialchars($freePlan['price'], ENT_QUOTES, 'UTF-8') ?></p>
      <ul>
        <?php foreach ($freePlan['benefits'] as $benefit): ?>
          <li><i data-lucide="check"></i><?= htmlspecialchars($benefit, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($freeEnabled): ?>
        <a class="btn btn-outline-primary w-100" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/register', ENT_QUOTES, 'UTF-8') ?>">Get started</a>
      <?php else: ?>
        <button class="btn btn-outline-secondary w-100" disabled>Free plan unavailable</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="pricing-card pricing-card-featured h-100<?= $premiumEnabled ? '' : ' pricing-card-disabled' ?>">
      <span class="plan-badge">Most popular</span>
      <h3><?= htmlspecialchars($premiumPlan['name'], ENT_QUOTES, 'UTF-8') ?></h3>
      <p class="price"><?= htmlspecialchars($premiumPlan['price'], ENT_QUOTES, 'UTF-8') ?></p>
      <ul >
        <?php foreach ($premiumPlan['benefits'] as $benefit): ?>
          <li ><i data-lucide="check"></i><?= htmlspecialchars($benefit, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($premiumEnabled): ?>
        <?php if (\App\Services\AuthService::check()): ?>
          <form method="POST" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/payments/paychangu/premium', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <button class="btn btn-primary w-100" type="submit">Upgrade with PayChangu</button>
          </form>
        <?php else: ?>
          <a class="btn btn-primary w-100" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/register', ENT_QUOTES, 'UTF-8') ?>">Create account to upgrade</a>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn btn-secondary w-100" disabled>Premium plan unavailable</button>
      <?php endif; ?>
    </div>
  </div>
</div>
