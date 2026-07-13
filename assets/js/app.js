document.addEventListener('DOMContentLoaded', () => {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const root = document.documentElement;

  // ——— CSRF: inject em todos os forms POST ———
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta?.getAttribute('content') || '';
  const ensureCsrf = (form) => {
    if (!csrfToken || !form || form.method.toLowerCase() !== 'post') return;
    if (form.querySelector('input[name="_csrf"]')) return;
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_csrf';
    input.value = csrfToken;
    form.appendChild(input);
  };
  document.querySelectorAll('form').forEach(ensureCsrf);
  const formObserver = new MutationObserver((mutations) => {
    mutations.forEach((m) => {
      m.addedNodes.forEach((node) => {
        if (node.nodeType !== 1) return;
        if (node.matches?.('form')) ensureCsrf(node);
        node.querySelectorAll?.('form').forEach(ensureCsrf);
      });
    });
  });
  formObserver.observe(document.body, { childList: true, subtree: true });

  // ——— Tema claro / escuro ———
  const themeToggle = document.getElementById('themeToggle');
  const metaTheme = document.getElementById('metaThemeColor');

  const applyTheme = (theme) => {
    root.setAttribute('data-theme', theme);
    try { localStorage.setItem('nu_chao_theme', theme); } catch (e) {}
    if (metaTheme) {
      metaTheme.setAttribute('content', theme === 'light' ? '#f3f7f1' : '#0f2e1f');
    }
  };

  try {
    const saved = localStorage.getItem('nu_chao_theme');
    if (saved === 'light' || saved === 'dark') applyTheme(saved);
  } catch (e) {}

  themeToggle?.addEventListener('click', () => {
    const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    applyTheme(next);
    themeToggle.classList.add('is-spin');
    setTimeout(() => themeToggle.classList.remove('is-spin'), 400);
  });

  // ——— Alertas de promoção (dismiss) ———
  document.querySelectorAll('.promo-alert').forEach((el) => {
    const id = el.getAttribute('data-promo-id') || '';
    const key = 'nu_chao_promo_hide_' + id;
    try {
      if (localStorage.getItem(key) === '1') {
        el.remove();
        return;
      }
    } catch (e) {}
    el.querySelector('.promo-alert-close')?.addEventListener('click', () => {
      try { localStorage.setItem(key, '1'); } catch (e) {}
      el.style.maxHeight = el.offsetHeight + 'px';
      requestAnimationFrame(() => {
        el.classList.add('is-hiding');
        setTimeout(() => el.remove(), 280);
      });
    });
  });
  const promoWrap = document.getElementById('promoAlerts');
  if (promoWrap && !promoWrap.querySelector('.promo-alert')) {
    promoWrap.remove();
  }
  // ——— Menu do utilizador logado ———
  const userMenu = document.getElementById('userMenu');
  const userMenuBtn = document.getElementById('userMenuBtn');
  const userMenuPanel = document.getElementById('userMenuPanel');

  const closeUserMenu = () => {
    if (!userMenu || !userMenuBtn || !userMenuPanel) return;
    userMenu.classList.remove('is-open');
    userMenuBtn.setAttribute('aria-expanded', 'false');
    userMenuPanel.hidden = true;
  };

  const openUserMenu = () => {
    if (!userMenu || !userMenuBtn || !userMenuPanel) return;
    userMenu.classList.add('is-open');
    userMenuBtn.setAttribute('aria-expanded', 'true');
    userMenuPanel.hidden = false;
  };

  userMenuBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (userMenuPanel?.hidden) openUserMenu();
    else closeUserMenu();
  });

  document.addEventListener('click', (e) => {
    if (userMenu && !userMenu.contains(e.target)) closeUserMenu();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeUserMenu();
  });

  // ——— Menu mobile ———
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('mainNav');
  const overlay = document.getElementById('navOverlay');

  const closeNav = () => {
    if (!toggle || !nav) return;
    toggle.setAttribute('aria-expanded', 'false');
    nav.classList.remove('is-open');
    if (overlay) {
      overlay.classList.remove('is-open');
      overlay.hidden = true;
    }
    document.body.style.overflow = '';
    closeUserMenu();
  };

  const openNav = () => {
    if (!toggle || !nav) return;
    toggle.setAttribute('aria-expanded', 'true');
    nav.classList.add('is-open');
    if (overlay) {
      overlay.hidden = false;
      requestAnimationFrame(() => overlay.classList.add('is-open'));
    }
    document.body.style.overflow = 'hidden';
  };

  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') === 'true';
      open ? closeNav() : openNav();
    });
    overlay?.addEventListener('click', closeNav);
    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeNav));
    window.addEventListener('resize', () => {
      if (window.innerWidth > 960) closeNav();
    });
  }

  // ——— Filtros produtos + sync categorias ———
  const chips = document.querySelectorAll('.chip[data-filter], .cat-card[data-filter], .cat-pill[data-filter]');
  const cards = document.querySelectorAll('[data-categoria]');

  const applyFilter = (filter) => {
    document.querySelectorAll('[data-filter]').forEach((el) => {
      el.classList.toggle('is-active', el.getAttribute('data-filter') === filter);
      el.classList.toggle('active', el.getAttribute('data-filter') === filter);
    });
    cards.forEach((card) => {
      const cat = card.getAttribute('data-categoria');
      const isPromo = card.getAttribute('data-promo') === '1';
      const show = filter === 'TODOS' || filter === cat || (filter === 'PROMO' && isPromo);
      card.style.display = show ? '' : 'none';
      if (show && !prefersReduced) {
        card.style.animation = 'none';
        void card.offsetWidth;
        card.style.animation = 'fade-up 0.45s cubic-bezier(0.22, 1, 0.36, 1) both';
      }
    });
  };

  chips.forEach((chip) => {
    chip.addEventListener('click', () => {
      const filter = chip.getAttribute('data-filter');
      if (!filter) return;
      applyFilter(filter);
      document.getElementById('produtos')?.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });
    });
  });

  const search = document.getElementById('productSearch');
  if (search) {
    search.addEventListener('input', () => {
      const q = search.value.trim().toLowerCase();
      cards.forEach((card) => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  // ——— Hero carousel ———
  const hero = document.getElementById('heroCarousel');
  if (hero) {
    const slides = [...hero.querySelectorAll('.hero-slide')];
    const dots = [...hero.querySelectorAll('.hero-dot')];
    let index = 0;
    let timer;

    const goTo = (i) => {
      index = (i + slides.length) % slides.length;
      slides.forEach((s, n) => s.classList.toggle('is-active', n === index));
      dots.forEach((d, n) => d.classList.toggle('is-active', n === index));
    };

    const next = () => goTo(index + 1);
    const prev = () => goTo(index - 1);

    const start = () => {
      if (prefersReduced || slides.length < 2) return;
      stop();
      timer = setInterval(next, 3500);
    };
    const stop = () => { if (timer) clearInterval(timer); };

    hero.querySelector('.hero-nav.next')?.addEventListener('click', () => { next(); start(); });
    hero.querySelector('.hero-nav.prev')?.addEventListener('click', () => { prev(); start(); });
    dots.forEach((dot) => {
      dot.addEventListener('click', () => {
        goTo(Number(dot.dataset.goto || 0));
        start();
      });
    });

    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    hero.addEventListener('touchstart', stop, { passive: true });
    hero.addEventListener('touchend', start, { passive: true });

    // swipe
    let touchX = 0;
    hero.addEventListener('touchstart', (e) => { touchX = e.changedTouches[0].screenX; }, { passive: true });
    hero.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].screenX - touchX;
      if (Math.abs(dx) > 40) {
        dx < 0 ? next() : prev();
        start();
      }
    }, { passive: true });

    start();
  }

  // ——— Category carousel ———
  const cat = document.getElementById('catCarousel');
  if (cat) {
    const track = cat.querySelector('.cat-track');
    const step = () => Math.min(track.clientWidth * 0.8, 260);
    cat.querySelector('.cat-nav.next')?.addEventListener('click', () => {
      track.scrollBy({ left: step(), behavior: 'smooth' });
    });
    cat.querySelector('.cat-nav.prev')?.addEventListener('click', () => {
      track.scrollBy({ left: -step(), behavior: 'smooth' });
    });
  }

  // ——— Scroll reveal ———
  const revealEls = document.querySelectorAll(
    '.section-head, .product-card, .about-panel, .panel, .summary-box, .auth-card, .reveal, .admin-stat, .admin-form-card, .admin-list-card, .cat-card, .cat-pill, .contact-card, .deliver-card, .painel-stat, .painel-head, .order-card'
  );
  revealEls.forEach((el) => el.classList.add('reveal'));

  if (prefersReduced) {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  } else if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );
    revealEls.forEach((el, i) => {
      el.style.transitionDelay = `${Math.min(i % 6, 5) * 0.06}s`;
      io.observe(el);
    });
  } else {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  }

  // ——— Modal de cadastro (sobre a página actual) ———
  const registerModal = document.getElementById('registerModal');
  const registerForm = document.getElementById('registerForm');
  const registerError = document.getElementById('registerError');
  const registerSubmit = document.getElementById('registerSubmit');

  const openRegister = (e) => {
    if (e) e.preventDefault();
    if (!registerModal) {
      window.location.href = '/No_chao/index.php?cadastro=1';
      return;
    }
    closeNav();
    registerModal.hidden = false;
    registerModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    setTimeout(() => document.getElementById('reg_nome')?.focus(), 50);
  };

  const closeRegister = () => {
    if (!registerModal) return;
    registerModal.hidden = true;
    registerModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    if (registerError) {
      registerError.hidden = true;
      registerError.textContent = '';
    }
    // Limpa ?cadastro=1 da URL sem recarregar
    if (window.history.replaceState && /[?&]cadastro=1/.test(location.search)) {
      const url = new URL(location.href);
      url.searchParams.delete('cadastro');
      history.replaceState({}, '', url.pathname + url.search + url.hash);
    }
  };

  document.querySelectorAll('.js-open-register').forEach((el) => {
    el.addEventListener('click', openRegister);
  });

  registerModal?.querySelectorAll('[data-close-register]').forEach((el) => {
    el.addEventListener('click', closeRegister);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && registerModal && !registerModal.hidden) {
      closeRegister();
    }
  });

  // Abrir automaticamente via ?cadastro=1
  if (registerModal && /(?:\?|&)cadastro=1(?:&|$)/.test(location.search)) {
    openRegister();
  }

  // Tipo de conta cards
  registerModal?.querySelectorAll('.tipo-card input').forEach((input) => {
    input.addEventListener('change', () => {
      registerModal.querySelectorAll('.tipo-card').forEach((c) => c.classList.remove('active'));
      input.closest('.tipo-card')?.classList.add('active');
    });
  });

  document.getElementById('googleRegistoBtn')?.addEventListener('click', (ev) => {
    ev.preventDefault();
    const tipo = registerModal?.querySelector('input[name="tipo"]:checked')?.value || 'CLIENTE';
    window.location.href = '/No_chao/auth/google_auth.php?modo=registo&tipo=' + encodeURIComponent(tipo);
  });

  registerForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!registerError) return;
    registerError.hidden = true;
    registerError.textContent = '';
    if (registerSubmit) {
      registerSubmit.disabled = true;
      registerSubmit.textContent = 'A criar conta…';
    }

    try {
      const res = await fetch('/No_chao/api/registar.php', {
        method: 'POST',
        body: new FormData(registerForm),
        headers: { Accept: 'application/json' },
      });
      const data = await res.json();
      if (!data.ok) {
        registerError.textContent = data.error || 'Não foi possível criar a conta.';
        registerError.hidden = false;
        return;
      }
      window.location.href = data.redirect || '/No_chao/index.php';
    } catch (err) {
      registerError.textContent = 'Erro de ligação. Tente novamente.';
      registerError.hidden = false;
    } finally {
      if (registerSubmit) {
        registerSubmit.disabled = false;
        registerSubmit.innerHTML = 'Registar';
      }
    }
  });

  // ——— Galeria de detalhes do produto ———
  document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const main = gallery.querySelector('#galleryMain') || gallery.querySelector('.product-gallery-main img');
    if (!main) return;
    gallery.querySelectorAll('.gallery-thumb').forEach((thumb) => {
      thumb.addEventListener('click', () => {
        const src = thumb.getAttribute('data-src');
        if (!src) return;
        main.src = src;
        gallery.querySelectorAll('.gallery-thumb').forEach((t) => t.classList.remove('is-active'));
        thumb.classList.add('is-active');
      });
    });
  });
});
