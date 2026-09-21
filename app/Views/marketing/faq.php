<section class="subpage-hero">
  <div class="container">
    <p class="section-kicker">FAQ</p>
    <h1>Common questions about hosting in Malawi.</h1>
    <p class="lead text-white-50">Quick answers for accounts, publishing, pricing, and getting your website online.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-5 reveal">
        <div class="section-heading">
          <p class="section-kicker">Need help?</p>
          <h2>Find quick answers to the questions we hear most often.</h2>
        </div>
        <p class="text-secondary">If your question is not covered here, please contact support and we will respond as soon as possible.</p>
      </div>
      <div class="col-lg-7 reveal">
        <div class="accordion faq-accordion" id="faqAccordion">
          <?php foreach ([
            ['How does hosting work?', 'You create or upload your website files, then publish them through the dashboard to an Instaweb URL.'],
            ['Can I use my own domain?', 'Yes. Custom domains are available on the Premium plan for projects that need a more professional identity.'],
            ['How long does free hosting last?', 'The free plan is a good starting point for one website and 100MB of storage, with a 30-day hosting period.'],
            ['Can I upgrade anytime?', 'Yes. You can move from Free to Premium when your project grows and you need more space, features, or control.'],
            ['Do you support analytics?', 'Premium users can track visits, devices, and traffic so they can understand how their website is performing.'],
          ] as $index => $item): ?>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $index ?>">
                  <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') ?>
                </button>
              </h2>
              <div id="faq<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                <div class="accordion-body"><?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
