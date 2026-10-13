import Fuse from 'fuse.js';

let searchEngine;
let tocObserver;

async function loadSearch() {
  if (searchEngine) return searchEngine;
  const response = await fetch(import.meta.env.BASE_URL + 'search-index.json');
  if (!response.ok) throw new Error('Search index request failed');
  const items = await response.json();
  searchEngine = new Fuse(items, {
    keys: [
      { name: 'title', weight: 0.45 },
      { name: 'summary', weight: 0.2 },
      { name: 'content', weight: 0.25 },
      { name: 'tags', weight: 0.1 }
    ],
    threshold: 0.38,
    ignoreLocation: true,
    minMatchCharLength: 1
  });
  return searchEngine;
}

function renderSearch(query, target, hint) {
  target.replaceChildren();
  if (!query.trim()) {
    hint.textContent = '输入关键词，搜索文章和学习笔记';
    return;
  }
  loadSearch().then(engine => {
    const matches = engine.search(query).slice(0, 12);
    hint.textContent = matches.length ? '找到 ' + matches.length + ' 条相关内容' : '没有匹配结果，换个关键词试试';
    for (const match of matches) {
      const item = match.item;
      const link = document.createElement('a');
      link.className = 'search-result';
      link.href = item.url;
      const title = document.createElement('strong');
      title.textContent = item.title;
      const summary = document.createElement('span');
      summary.textContent = item.summary || item.category;
      const meta = document.createElement('small');
      meta.textContent = item.category;
      link.append(title, summary, meta);
      target.append(link);
    }
  }).catch(() => { hint.textContent = '搜索索引暂时无法读取。'; });
}

function bindOnce(element, type, handler) {
  if (!element || element.hasAttribute('data-bound-' + type)) return;
  element.setAttribute('data-bound-' + type, 'true');
  element.addEventListener(type, handler);
}

function closeMenu() {
  document.body.classList.remove('sidebar-open');
  document.querySelector('#menu-toggle')?.setAttribute('aria-expanded', 'false');
}

function initPage() {
  const root = document.documentElement;
  const dialog = document.querySelector('#search-dialog');
  const globalInput = document.querySelector('#global-search');
  const globalResults = document.querySelector('#global-results');
  const searchHint = document.querySelector('#search-hint');
  const homeSearch = document.querySelector('#home-search');
  const homeResults = document.querySelector('#home-search-results');
  const homeHint = document.querySelector('#home-search-hint');

  bindOnce(document.querySelector('#search-open'), 'click', () => {
    dialog?.showModal();
    globalInput?.focus();
  });
  bindOnce(document.querySelector('#search-close'), 'click', () => dialog?.close());
  bindOnce(globalInput, 'input', () => renderSearch(globalInput.value, globalResults, searchHint));
  bindOnce(homeSearch, 'input', () => renderSearch(homeSearch.value, homeResults, homeHint));

  bindOnce(document.querySelector('#theme-toggle'), 'click', () => {
    const dark = root.dataset.theme !== 'dark';
    if (dark) root.dataset.theme = 'dark';
    else delete root.dataset.theme;
    try { localStorage.setItem('argon-mode', dark ? 'dark' : 'light'); } catch {}
  });
  bindOnce(document.querySelector('#menu-toggle'), 'click', event => {
    const button = event.currentTarget;
    const open = document.body.classList.toggle('sidebar-open');
    button.setAttribute('aria-expanded', String(open));
  });
  bindOnce(document.querySelector('#sidebar-scrim'), 'click', closeMenu);

  document.querySelectorAll('.widget-tab').forEach(button => bindOnce(button, 'click', () => {
    const panelName = button.dataset.tab;
    document.querySelectorAll('.widget-tab').forEach(tab => {
      const selected = tab === button;
      tab.classList.toggle('is-current', selected);
      tab.setAttribute('aria-selected', String(selected));
    });
    document.querySelectorAll('.widget-panel').forEach(panel => {
      const visible = panel.dataset.panel === panelName;
      panel.hidden = !visible;
      panel.classList.toggle('is-visible', visible);
    });
  }));

  bindOnce(document.querySelector('#back-to-top'), 'click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  document.querySelectorAll('pre:not([data-copy-ready])').forEach(pre => {
    pre.dataset.copyReady = 'true';
    const button = document.createElement('button');
    button.className = 'copy-code';
    button.type = 'button';
    button.textContent = '复制';
    button.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(pre.querySelector('code')?.innerText || pre.innerText);
        button.textContent = '已复制';
      } catch {
        button.textContent = '复制失败';
      }
      setTimeout(() => button.textContent = '复制', 1400);
    });
    pre.append(button);
  });

  tocObserver?.disconnect();
  const tocLinks = [...document.querySelectorAll('.toc-link')];
  if (tocLinks.length && 'IntersectionObserver' in window) {
    const targets = tocLinks.map(link => document.getElementById(decodeURIComponent(link.hash.slice(1)))).filter(Boolean);
    tocObserver = new IntersectionObserver(entries => {
      const active = entries.filter(entry => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
      if (!active) return;
      tocLinks.forEach(link => link.classList.toggle('is-active', link.hash === '#' + active.target.id));
    }, { rootMargin: '-15% 0px -70% 0px' });
    targets.forEach(target => tocObserver.observe(target));
  }
}

if (!window.__argonInteractionsBound) {
  document.addEventListener('astro:page-load', initPage);
  document.addEventListener('keydown', event => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault();
      const dialog = document.querySelector('#search-dialog');
      if (dialog && !dialog.open) dialog.showModal();
      document.querySelector('#global-search')?.focus();
    }
    if (event.key === 'Escape') closeMenu();
  });
  window.addEventListener('scroll', () => document.querySelector('#back-to-top')?.classList.toggle('is-visible', window.scrollY > window.innerHeight), { passive: true });
  window.__argonInteractionsBound = true;
}

initPage();
