// ===== Gamers Arena — Shared behavior =====
document.addEventListener('DOMContentLoaded', () => {

  // Mobile menu toggle
  const burger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  if (burger && navLinks) {
    burger.addEventListener('click', () => navLinks.classList.toggle('open'));
  }

  // Mobile dropdown toggle (My Sales / My Orders / History)
  document.querySelectorAll('.dropdown > a').forEach(a => {
    a.addEventListener('click', (e) => {
      if (window.innerWidth <= 820) {
        e.preventDefault();
        a.parentElement.classList.toggle('open');
      }
    });
  });

  // Back to top
  const backTop = document.querySelector('.back-top');
  if (backTop) {
    backTop.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Recharge / package pill selection
  document.querySelectorAll('.pill-grid').forEach(grp => {
    grp.addEventListener('click', (e) => {
      const pill = e.target.closest('.pill');
      if (!pill) return;
      grp.querySelectorAll('.pill').forEach(p => {
        p.classList.remove('selected');
        p.querySelector('.check')?.remove();
      });
      pill.classList.add('selected');
      const radio = pill.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
      const chk = document.createElement('span');
      chk.className = 'check';
      chk.innerHTML = '<i class="fa-solid fa-check"></i>';
      pill.prepend(chk);
      recalcPurchase();
    });
  });

  // Payment logo selection
  document.querySelectorAll('.pay-grid').forEach(grp => {
    grp.addEventListener('click', (e) => {
      const logo = e.target.closest('.pay-logo');
      if (!logo) return;
      grp.querySelectorAll('.pay-logo').forEach(p => {
        p.classList.remove('selected');
        p.querySelector('.check')?.remove();
      });
      logo.classList.add('selected');
      const chk = document.createElement('span');
      chk.className = 'check';
      chk.innerHTML = '<i class="fa-solid fa-check"></i>';
      logo.prepend(chk);
      const noteMethod = document.querySelector('[data-method-name]');
      if (noteMethod) noteMethod.textContent = logo.dataset.name || logo.textContent.trim();
    });
  });

  function recalcPurchase() {
    const selected = document.querySelector('.pill.selected');
    if (!selected) return;
    const price = parseFloat(selected.dataset.price || '0');
    const subtotalEl = document.querySelector('[data-subtotal]');
    const discountEl = document.querySelector('[data-discount]');
    const payableEl = document.querySelector('[data-payable]');
    const noteEl = document.querySelector('[data-payable-note]');
    if (!subtotalEl) return;
    const discountRate = 0.15;
    const subtotal = price;
    const discount = +(subtotal * discountRate).toFixed(2);
    const payable = +(subtotal - discount).toFixed(2);
    subtotalEl.textContent = '$' + subtotal.toFixed(2);
    discountEl.textContent = '$' + discount.toFixed(2);
    payableEl.textContent = '$' + payable.toFixed(2);
    if (noteEl) noteEl.textContent = payable.toFixed(2);
  }

  // Chat: the .chat-input form posts for real to actions/chat_send.php,
  // this just keeps the scroll pinned to the latest message on load.
  const chatBody = document.querySelector('.chat-body');
  if (chatBody) {
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  // Simple price range slider (visual only)
  document.querySelectorAll('.range-track').forEach(track => {
    const thumbs = track.querySelectorAll('.range-thumb');
    let active = null;
    thumbs.forEach(t => t.addEventListener('mousedown', () => active = t));
    document.addEventListener('mouseup', () => active = null);
    document.addEventListener('mousemove', (e) => {
      if (!active) return;
      const rect = track.getBoundingClientRect();
      let pct = ((e.clientX - rect.left) / rect.width) * 100;
      pct = Math.max(0, Math.min(100, pct));
      active.style.left = pct + '%';
    });
  });

  // Offer item menu toggle
  document.querySelectorAll('.item-menu-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const menu = btn.nextElementSibling;
      document.querySelectorAll('.item-menu').forEach(m => { if (m !== menu) m.style.display = 'none'; });
      menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
  });
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.item-menu-wrap')) {
      document.querySelectorAll('.item-menu').forEach(m => m.style.display = 'none');
    }
  });

});
