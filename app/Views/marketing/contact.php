<section class="subpage-hero">
  <div class="container">
    <p class="section-kicker">Contact</p>
    <h1>Reach out to Instaweb.</h1>
    <p class="lead text-white-50">Whether you are in Lilongwe, Blantyre, Zomba, or anywhere else in Malawi, we are here to help you get online with confidence.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5 reveal">
        <div class="feature-card h-100">
          <i data-lucide="mail"></i>
          <h3>Support</h3>
          <p>phukal@mau.adventist.org</p>
          <hr>
          <p class="mb-0">Built by LawreayTech for creators, students, churches, and businesses that want a simpler path to publishing.</p>
        </div>
      </div>
      <div class="col-lg-7 reveal">
        <form class="contact-card" action="<?= htmlspecialchars(($app['base_url'] ?? '') . '/contact', ENT_QUOTES, 'UTF-8') ?>" method="post">
          <?= \App\Core\Csrf::field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="name">Full name</label>
              <input class="form-control" id="name" name="name" type="text" placeholder="Jane Doe">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" id="email" name="email" type="email" placeholder="you@example.com">
            </div>
            <div class="col-12">
              <label class="form-label" for="subject">Subject</label>
              <input class="form-control" id="subject" name="subject" type="text" placeholder="How can we help?">
            </div>
            <div class="col-12">
              <label class="form-label" for="message">Message</label>
              <textarea class="form-control" id="message" name="message" rows="5" placeholder="Tell us about your project or question."></textarea>
            </div>
            <div class="col-12 text-end">
              <button class="btn btn-primary" type="submit">Send message</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
