if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('./sw.js').catch((error) => {
      console.warn('Service worker registration failed:', error);
    });
  });
}

const appShellNotice = () => {
  const status = document.createElement('div');
  status.style.position = 'fixed';
  status.style.right = '16px';
  status.style.bottom = '16px';
  status.style.background = '#132318';
  status.style.color = '#f7f2e2';
  status.style.padding = '10px 12px';
  status.style.borderRadius = '10px';
  status.style.fontSize = '12px';
  status.style.zIndex = '9999';
  status.textContent = 'Tsogolo Hub is ready for offline use.';
  document.body.appendChild(status);
  setTimeout(() => status.remove(), 3000);
};

window.addEventListener('online', appShellNotice);
window.addEventListener('load', appShellNotice);
