<section class="subpage-hero">
  <div class="container">
    <p class="section-kicker">Features</p>
    <h1>Everything you need to publish a website with confidence.</h1>
    <p class="lead text-white-50">TurboHostMw brings file management, publishing, analytics, and account control into one simple platform that works well for beginners and growing teams.</p>
  </div>
</section>

<section class="page-section section-muted">
  <div class="container">
    <div class="row g-4 reveal-stagger">
      <?php foreach ([
        ['code-2', 'Built-in code workflow', 'Create and edit HTML, CSS, and JavaScript project files.'],
        ['folder-open', 'File manager', 'Upload, organize, replace, and delete website assets.'],
        ['server', 'Static hosting', 'Publish clean static websites through a simple dashboard.'],
        ['bar-chart-3', 'Analytics', 'Track visits, devices, traffic, and growth after publishing.'],
        ['link', 'Custom domains', 'Connect domains on Premium when your project grows.'],
        ['shield-check', 'Secure foundation', 'Sessions, CSRF protection, prepared queries, and validation.'],
      ] as $item): ?>
        <div class="col-md-6 col-lg-4">
          <div class="feature-card h-100 reveal">
            <i data-lucide="<?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') ?>"></i>
            <h3><?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($item[2], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="section-heading section-heading-left reveal">
      <p class="section-kicker">Why TurboHostMw</p>
      <h2>Publish faster with a platform designed for local websites.</h2>
    </div>
    <div class="row g-4 reveal-stagger">
      <div class="col-md-6">
        <div class="feature-card h-100">
          <i data-lucide="rocket"></i>
          <h3>Fast on-ramp</h3>
          <p>Launch your first site in minutes with built-in workflows for hosting, publishing, and content updates.</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="feature-card h-100">
          <i data-lucide="shield-check"></i>
          <h3>Secure baseline</h3>
          <p>Protected forms, validated uploads, and a clean publishing model so you can focus on your message.</p>
        </div>
      </div>
    </div>
  </div>
</section>
