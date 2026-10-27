(() => {
  const FAV_KEY = 'tkh-favorites';
  const t = Object.assign({ favAdd: 'Lưu vào yêu thích', favRemove: 'Bỏ khỏi yêu thích', copied: 'Đã sao chép liên kết' }, window.tkhI18n || {});

  const readFavs = () => {
    try {
      const value = JSON.parse(localStorage.getItem(FAV_KEY));
      return Array.isArray(value) ? value.map(String) : [];
    } catch {
      return [];
    }
  };
  const writeFavs = (favs) => {
    try {
      localStorage.setItem(FAV_KEY, JSON.stringify(favs));
    } catch {
      /* storage unavailable (private mode): favorites stay in-memory for this page */
    }
  };

  // Favorites: heart buttons on cards and the single-post sidebar.
  let favs = readFavs();
  const syncFavs = () => {
    const saved = new Set(favs);
    document.querySelectorAll('[data-fav]').forEach((btn) => {
      const on = saved.has(btn.dataset.fav);
      btn.classList.toggle('is-fav', on);
      btn.setAttribute('aria-pressed', String(on));
      if (!btn.querySelector('.fav-off')) {
        btn.setAttribute('aria-label', on ? t.favRemove : t.favAdd);
      }
    });

    const list = document.querySelector('[data-fav-list]');
    if (list) {
      let shown = 0;
      list.querySelectorAll('[data-card]').forEach((card) => {
        const on = saved.has(card.dataset.card);
        card.hidden = !on;
        if (on) shown += 1;
      });
      document.querySelector('[data-fav-empty]').hidden = shown > 0;
      document.querySelector('[data-fav-count]').textContent = shown;
    }
  };

  document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-fav]');
    if (!btn) return;
    event.preventDefault();
    const id = btn.dataset.fav;
    favs = favs.includes(id) ? favs.filter((f) => f !== id) : [...favs, id];
    writeFavs(favs);
    syncFavs();
  });

  window.addEventListener('storage', (event) => {
    if (event.key === FAV_KEY) {
      favs = readFavs();
      syncFavs();
    }
  });

  syncFavs();

  // Copy-link button on articles.
  document.querySelectorAll('[data-copy-link]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(btn.dataset.copyLink);
        const icon = btn.querySelector('.material-symbols-outlined');
        icon.textContent = 'check';
        btn.setAttribute('title', t.copied);
        setTimeout(() => { icon.textContent = 'link'; }, 2000);
      } catch {
        window.prompt('', btn.dataset.copyLink);
      }
    });
  });

  // Article: reading progress bar, back-to-top button, active table-of-contents entry.
  const article = document.querySelector('[data-article]');
  if (article) {
    const bar = document.querySelector('[data-reading-progress]');
    const topBtn = document.querySelector('[data-back-to-top]');
    let ticking = false;
    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        const rect = article.getBoundingClientRect();
        const total = rect.height - window.innerHeight;
        const progress = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 1;
        if (bar) bar.style.transform = `scaleX(${progress})`;
        if (topBtn) topBtn.dataset.show = String(window.scrollY > 600);
        ticking = false;
      });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    topBtn?.addEventListener('click', () => {
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });

    const toc = document.querySelector('[data-toc]');
    if (toc && 'IntersectionObserver' in window) {
      const links = new Map([...toc.querySelectorAll('a[href^="#"]')].map((a) => [decodeURIComponent(a.hash.slice(1)), a]));
      const setActive = (id) => links.forEach((a, key) => a.classList.toggle('is-active', key === id));
      const observer = new IntersectionObserver((entries) => {
        const visible = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
        if (visible[0]) setActive(visible[0].target.id);
      }, { rootMargin: '-96px 0px -65% 0px' });
      links.forEach((_, id) => {
        const heading = document.getElementById(id);
        if (heading) observer.observe(heading);
      });
    }
  }

  // Mobile slide-in menu.
  const drawer = document.getElementById('tkh-drawer');
  const opener = document.querySelector('[data-menu-open]');
  const setDrawer = (open) => {
    if (!drawer) return;
    drawer.hidden = !open;
    document.body.classList.toggle('overflow-hidden', open);
    opener?.setAttribute('aria-expanded', String(open));
    if (open) drawer.querySelector('input')?.focus();
    else opener?.focus();
  };
  opener?.addEventListener('click', () => setDrawer(true));
  drawer?.querySelectorAll('[data-menu-close]').forEach((el) => el.addEventListener('click', () => setDrawer(false)));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && drawer && !drawer.hidden) setDrawer(false);
  });

  // Listing filters: apply select changes immediately, dropping empty params for clean URLs.
  document.querySelectorAll('form[data-autosubmit]').forEach((form) => {
    form.addEventListener('change', (event) => {
      if (event.target.matches('select')) form.requestSubmit();
    });
    form.addEventListener('submit', () => {
      form.querySelectorAll('select, input[type="hidden"]').forEach((el) => {
        if (el.value === '') el.disabled = true;
      });
    });
  });
})();
