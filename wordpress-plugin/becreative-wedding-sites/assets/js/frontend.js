document.documentElement.classList.add('bcw-js');

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) entry.target.classList.add('is-visible');
  });
}, { threshold: 0.08 });

document.querySelectorAll('[data-reveal]').forEach((el) => observer.observe(el));

const menuButton = document.querySelector('[data-menu-button]');
const menu = document.querySelector('[data-menu]');
if (menuButton && menu) {
  menuButton.addEventListener('click', () => {
    const open = menu.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', String(open));
  });

  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      menu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
    });
  });
}

const entryCover = document.querySelector('[data-entry-cover]');
const enterButton = document.querySelector('[data-enter-wedding]');

if (entryCover) {
  const weddingId = entryCover.dataset.weddingId || 'wedding';
  const storageKey = 'bcw-entered-' + weddingId;
  const previewMode = new URLSearchParams(window.location.search).get('bcw_invitation_preview') === '1';

  if (!previewMode) {
    try {
      if (window.sessionStorage.getItem(storageKey) === '1') {
        entryCover.remove();
      }
    } catch (e) {
      // If storage is unavailable, the cover simply appears again on refresh.
    }
  }

  if (enterButton && document.body.contains(entryCover)) {
    enterButton.addEventListener('click', () => {
      if (!previewMode) {
        try {
          window.sessionStorage.setItem(storageKey, '1');
        } catch (e) {}
      }

      entryCover.classList.add('is-leaving');
      window.setTimeout(() => entryCover.remove(), 550);
    });
  }
}


const removeCorporateChatWidgets = () => {
  const selectors = [
    '.joinchat',
    '#joinchat',
    '.ht-ctc-chat',
    '#ht-ctc-chat',
    '#qlwapp',
    '.qlwapp',
    '.floating-wpp',
    '.wa__btn_popup',
    '.whatsapp_float',
    '.whatsapp-button',
    '.chaty-widget',
    '.chaty-widget-i',
    '#chaty-widget',
    '[class*="whatsapp-widget"]',
    '[id*="whatsapp-widget"]',
    'a[href*="wa.me"]',
    'a[href*="api.whatsapp.com"]',
    'a[href*="whatsapp.com/send"]',
    'iframe[src*="whatsapp"]'
  ];

  document.querySelectorAll(selectors.join(',')).forEach((node) => {
    // Remove the widget wrapper when one of its inner WhatsApp links is matched.
    const wrapper = node.closest(
      '.joinchat, .ht-ctc-chat, #qlwapp, .qlwapp, .floating-wpp, .wa__btn_popup, .whatsapp_float, .whatsapp-button, .chaty-widget, #chaty-widget'
    );
    (wrapper || node).remove();
  });
};

removeCorporateChatWidgets();

const chatWidgetObserver = new MutationObserver(() => {
  removeCorporateChatWidgets();
});

chatWidgetObserver.observe(document.body, { childList: true, subtree: true });
