// Travel assistant widget. Talks to POST /wp-json/tkh/v1/chat; the conversation is kept per tab
// (sessionStorage) so it survives page changes, and is never sent anywhere else.
(() => {
  const cfg = window.tkhChat;
  const launcher = document.getElementById('tkh-chat-launcher');
  const panel = document.getElementById('tkh-chat');
  if (!cfg || !launcher || !panel) return;

  const log = panel.querySelector('[data-chat-log]');
  const form = panel.querySelector('[data-chat-form]');
  const input = panel.querySelector('#tkh-chat-input');
  const send = panel.querySelector('[data-chat-send]');
  const suggestions = panel.querySelector('[data-chat-suggestions]');
  const STORE = `tkh-chat-${cfg.lang}`;
  let busy = false;

  const load = () => {
    try {
      const value = JSON.parse(sessionStorage.getItem(STORE));
      return Array.isArray(value) ? value : [];
    } catch {
      return [];
    }
  };
  let turns = load();
  const save = () => {
    try {
      sessionStorage.setItem(STORE, JSON.stringify(turns.slice(-20)));
    } catch {
      /* storage unavailable: the conversation just won't persist across pages */
    }
  };

  const el = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  const sourceCard = (src) => {
    const a = el('a', 'flex items-center gap-3 p-2 rounded-xl bg-surface-container-lowest border border-slate-border/70 hover:border-primary-container transition-colors');
    a.href = src.url;
    const img = el('img', 'w-12 h-12 rounded-lg object-cover shrink-0');
    img.src = src.image;
    img.alt = '';
    img.loading = 'lazy';
    img.width = 48;
    img.height = 48;
    const body = el('span', 'min-w-0');
    body.append(el('span', 'block font-label-lg text-label-lg text-on-surface line-clamp-1', src.title));
    const meta = [src.rating ? `★ ${Number(src.rating).toFixed(1)}` : '', src.meta].filter(Boolean).join(' · ');
    body.append(el('span', 'block font-body-sm text-body-sm text-slate-muted line-clamp-1', meta));
    a.append(img, body);
    return a;
  };

  const render = (turn) => {
    if (turn.role === 'user') {
      const row = el('div', 'flex justify-end');
      row.append(el('p', 'max-w-[85%] px-4 py-2.5 rounded-2xl rounded-br-md bg-primary-container text-on-primary font-body-md text-body-md whitespace-pre-wrap break-words', turn.content));
      log.append(row);
      return;
    }
    const row = el('div', 'flex gap-2 items-start');
    const avatar = el('span', 'w-8 h-8 shrink-0 rounded-full bg-primary-fixed text-primary flex items-center justify-center');
    avatar.append(Object.assign(el('span', 'material-symbols-outlined icon-fill text-[18px]', 'auto_awesome'), { ariaHidden: 'true' }));
    const bubble = el('div', 'tkh-chat-bubble max-w-[85%] min-w-0 px-4 py-3 rounded-2xl rounded-tl-md bg-surface-container-lowest border border-slate-border/70 font-body-md text-body-md text-on-surface space-y-2');
    // Bot HTML is built server-side from escaped text with an allowlist (p, ul/li, strong, same-site links).
    bubble.innerHTML = turn.html;
    if (turn.sources && turn.sources.length) {
      const list = el('div', 'space-y-2 pt-1');
      list.append(el('p', 'font-label-sm text-label-sm uppercase tracking-wider text-slate-muted', cfg.i18n.sources));
      turn.sources.forEach((src) => list.append(sourceCard(src)));
      bubble.append(list);
    }
    row.append(avatar, bubble);
    log.append(row);
  };

  const scrollDown = () => {
    log.scrollTop = log.scrollHeight;
  };

  const renderAll = () => {
    log.replaceChildren();
    render({ role: 'assistant', html: `<p>${cfg.i18n.welcome}</p>` });
    turns.forEach(render);
    suggestions.hidden = turns.length > 0;
    scrollDown();
  };

  cfg.suggestions.forEach((q) => {
    const chip = el('button', 'chip !py-1.5 !px-3 !text-body-sm shrink-0 cursor-pointer', q);
    chip.type = 'button';
    chip.addEventListener('click', () => ask(q));
    suggestions.append(chip);
  });

  const typing = () => {
    const row = el('div', 'flex gap-2 items-center');
    row.dataset.typing = '';
    row.append(el('span', 'w-8 h-8 shrink-0 rounded-full bg-primary-fixed'));
    const dots = el('span', 'px-4 py-3 rounded-2xl bg-surface-container-lowest border border-slate-border/70 font-body-sm text-body-sm text-slate-muted', cfg.i18n.thinking);
    row.append(dots);
    log.append(row);
    scrollDown();
    return row;
  };

  const setBusy = (value) => {
    busy = value;
    send.disabled = value;
    input.setAttribute('aria-busy', String(value));
  };

  async function ask(text) {
    const message = text.trim().slice(0, 500);
    if (!message || busy) return;
    const history = turns.slice(-6).map((t) => ({ role: t.role, content: t.role === 'user' ? t.content : t.text || '' }));
    const userTurn = { role: 'user', content: message };
    turns.push(userTurn);
    render(userTurn);
    suggestions.hidden = true;
    input.value = '';
    autosize();
    setBusy(true);
    const indicator = typing();
    let reply;
    try {
      const res = await fetch(cfg.endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message, lang: cfg.lang, history }),
      });
      const data = await res.json();
      reply = data && data.html ? data : null;
    } catch {
      reply = null;
    }
    indicator.remove();
    const botTurn = reply
      ? { role: 'assistant', html: reply.html, text: reply.text || '', sources: reply.sources || [] }
      : { role: 'assistant', html: `<p>${cfg.i18n.error}</p>`, text: '', sources: [] };
    turns.push(botTurn);
    save();
    render(botTurn);
    setBusy(false);
    scrollDown();
    input.focus();
  }

  // Grow with the text up to 128px; an empty box returns to its one-line CSS height.
  const autosize = () => {
    input.style.height = '';
    if (!input.value) return;
    const border = input.offsetHeight - input.clientHeight;
    input.style.height = `${Math.min(input.scrollHeight + border, 128)}px`;
  };
  input.addEventListener('input', autosize);
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
      event.preventDefault();
      ask(input.value);
    }
  });
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    ask(input.value);
  });

  let rendered = false;
  const open = (value) => {
    panel.hidden = !value;
    launcher.setAttribute('aria-expanded', String(value));
    launcher.hidden = value && window.matchMedia('(max-width: 639px)').matches;
    if (value) {
      if (!rendered) {
        renderAll();
        rendered = true;
      }
      scrollDown();
      input.focus();
    } else {
      launcher.hidden = false;
      launcher.focus();
    }
  };
  launcher.addEventListener('click', () => open(panel.hidden));
  panel.querySelector('[data-chat-close]').addEventListener('click', () => open(false));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) open(false);
  });
})();
