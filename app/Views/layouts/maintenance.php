<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? ($app['name'] ?? 'Instaweb'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars(($app['base_url'] ?? '') . '/assets/css/styles.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="maintenance-body">
  <?= $content ?? '' ?>
  <script src="https://cdn.jsdelivr.net/npm/lucide@0.468.0/dist/umd/lucide.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const countdownElement = document.querySelector('[data-maintenance-countdown]');
      const returnAt = countdownElement?.dataset.returnAt;

      if (!countdownElement || !returnAt) {
        return;
      }

      function updateCountdown() {
        const target = new Date(returnAt);
        const now = new Date();
        const diff = target.getTime() - now.getTime();

        if (diff <= 0) {
          countdownElement.textContent = 'Expected return time has passed. Please refresh the page soon.';
          return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        countdownElement.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
      }

      updateCountdown();
      setInterval(updateCountdown, 1000);
    });
  </script>
</body>
</html>
