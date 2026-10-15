(() => {
  const root = document.documentElement;
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const themeButton = document.querySelector('[data-theme-toggle]');
  const themeLabel = themeButton?.querySelector('[data-theme-label]');
  const themeColorMeta = document.querySelector('meta[name="theme-color"]');
  const heroSection = document.querySelector('.hero[data-hero-light-images]');
  const heroImage = heroSection?.querySelector('[data-hero-background]');
  const heroPools = { light: [], dark: [] };
  try {
    heroPools.light = JSON.parse(heroSection?.dataset.heroLightImages || '[]');
    heroPools.dark = JSON.parse(heroSection?.dataset.heroDarkImages || '[]');
  } catch (_) { /* Keep the server-selected cover if a pool is unavailable. */ }
  const revealHero = () => {
    if (!heroSection) return;
    heroSection.classList.remove('hero-media-pending', 'hero-image-changing');
    heroSection.classList.add('hero-media-ready');
    heroSection.setAttribute('aria-busy', 'false');
  };
  const waitForHeroImage = () => {
    if (!heroSection || !heroImage) { revealHero(); return; }
    const finish = async () => {
      try { if (heroImage.decode) await heroImage.decode(); } catch (_) { /* A failed decode falls back to the CSS cover. */ }
      if (!heroImage.naturalWidth) heroImage.hidden = true;
      revealHero();
    };
    if (heroImage.complete) finish();
    else {
      heroImage.addEventListener('load', finish, { once: true });
      heroImage.addEventListener('error', finish, { once: true });
    }
  };
  const pickHeroForTheme = (theme) => {
    const pool = heroPools[theme] || [];
    if (!pool.length || !heroImage || heroSection?.dataset.heroCustom === 'true') return '';
    const currentPath = (() => { try { return new URL(heroImage.currentSrc || heroImage.src, window.location.href).pathname; } catch (_) { return ''; } })();
    const paths = pool.map((url) => { try { return new URL(url, window.location.href).pathname; } catch (_) { return ''; } });
    const currentIndex = paths.indexOf(currentPath);
    const key = `morrow-hero-${theme}`;
    let previous = -1;
    try { previous = Number(window.sessionStorage.getItem(key)); } catch (_) {}
    let index = Math.floor(Math.random() * pool.length);
    if (pool.length > 1 && (index === previous || index === currentIndex)) index = (index + 1 + Math.floor(Math.random() * (pool.length - 1))) % pool.length;
    try { window.sessionStorage.setItem(key, String(index)); } catch (_) {}
    return pool[index];
  };
  const setHeroTheme = (theme, initial = false) => {
    if (!heroSection || !heroImage) return;
    const pool = heroPools[theme] || [];
    const currentPath = (() => { try { return new URL(heroImage.currentSrc || heroImage.src, window.location.href).pathname; } catch (_) { return ''; } })();
    const poolPaths = pool.map((url) => { try { return new URL(url, window.location.href).pathname; } catch (_) { return ''; } });
    if (heroSection.dataset.heroCustom === 'true' || poolPaths.includes(currentPath) || !pool.length) { waitForHeroImage(); return; }
    const nextUrl = pickHeroForTheme(theme);
    if (!nextUrl) { waitForHeroImage(); return; }
    const requestId = String(Date.now()) + Math.random();
    heroSection.dataset.heroRequest = requestId;
    if (!initial) heroSection.classList.add('hero-image-changing');
    const candidate = new Image();
    candidate.decoding = 'async';
    candidate.fetchPriority = 'high';
    candidate.onload = async () => {
      try { if (candidate.decode) await candidate.decode(); } catch (_) {}
      if (heroSection.dataset.heroRequest !== requestId) return;
      heroImage.hidden = false;
      heroImage.src = nextUrl;
      try { if (heroImage.decode) await heroImage.decode(); } catch (_) {}
      if (initial) revealHero();
      else heroSection.classList.remove('hero-image-changing');
    };
    candidate.onerror = () => {
      if (heroSection.dataset.heroRequest !== requestId) return;
      if (initial) { heroImage.hidden = true; revealHero(); }
      else heroSection.classList.remove('hero-image-changing');
    };
    candidate.src = nextUrl;
    if (candidate.complete && candidate.naturalWidth) candidate.onload();
  };
  const syncThemeControl = () => {
    const isDark = root.dataset.theme === 'dark';
    if (themeLabel) themeLabel.textContent = isDark ? '明' : '暗';
    themeButton?.setAttribute('aria-label', isDark ? '切换到亮色主题' : '切换到暗色主题');
    if (themeColorMeta) themeColorMeta.content = isDark ? '#121212' : '#F5F1EA';
  };
  syncThemeControl();
  themeButton?.addEventListener('click', () => {
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    syncThemeControl();
    setHeroTheme(next);
    try { localStorage.setItem('morrow-theme', next); localStorage.setItem('echo-theme', next); document.cookie = `morrow-theme=${next}; Max-Age=31536000; Path=/; SameSite=Lax`; } catch (_) {}
  });

  const menuButton = document.querySelector('[data-menu-toggle]');
  const nav = document.querySelector('#top-nav');
  const closeMenu = () => {
    document.body.classList.remove('menu-open');
    menuButton?.setAttribute('aria-expanded', 'false');
  };
  menuButton?.addEventListener('click', () => {
    const open = document.body.classList.toggle('menu-open');
    menuButton.setAttribute('aria-expanded', String(open));
  });
  nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));

  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeMenu(); closeSearch(); } });

  const siteHeader = document.querySelector('.site-header');
  const homeHero = document.querySelector('html[data-home="true"] .hero');
  const updateHeader = () => {
    const progress = homeHero && siteHeader
      ? Math.max(0, Math.min(1, window.scrollY / Math.max(1, homeHero.offsetHeight - siteHeader.offsetHeight)))
      : 1;
    siteHeader?.style.setProperty('--header-opacity', progress.toFixed(3));
    document.body.classList.toggle('is-scrolled', window.scrollY > 24);
    document.body.classList.toggle('home-header-solid', !homeHero || progress >= .985);
  };
  window.addEventListener('scroll', updateHeader, { passive: true });
  window.addEventListener('resize', updateHeader, { passive: true });
  updateHeader();
  if (heroSection) setHeroTheme(root.dataset.theme === 'dark' ? 'dark' : 'light', true);

  const homeLayout = document.querySelector('html[data-home="true"] .home-layout');
  if (homeLayout?.querySelector('[data-home-articles]') && homeLayout.querySelector('.pagination-wrap')) {
    let homeFeedController = null;
    const markHomeHistoryEntry = (state) => ({
      ...(state && typeof state === 'object' ? state : {}),
      morrowHomeFeed: true,
    });
    window.history.replaceState(markHomeHistoryEntry(window.history.state), '', window.location.href);

    const loadHomeFeedPage = async (targetUrl, pushHistory = true) => {
      const url = new URL(targetUrl, window.location.href);
      if (url.origin !== window.location.origin) return;
      homeFeedController?.abort();
      const controller = new AbortController();
      homeFeedController = controller;
      const currentFeed = homeLayout.querySelector('[data-home-articles]');
      currentFeed?.setAttribute('aria-busy', 'true');

      try {
        const response = await fetch(url.href, {
          credentials: 'same-origin',
          headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal,
        });
        if (!response.ok) throw new Error('Page request failed');
        const html = await response.text();
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const nextLayout = parsed.documentElement.dataset.home === 'true'
          ? parsed.querySelector('.home-layout')
          : null;
        const nextFeed = nextLayout?.querySelector('[data-home-articles]');
        const nextPagination = nextLayout?.querySelector('.pagination-wrap');
        const livePagination = homeLayout.querySelector('.pagination-wrap');
        if (!nextFeed || !nextPagination || !currentFeed || !livePagination) throw new Error('Home feed not found');

        const scrollY = window.scrollY;
        currentFeed.replaceWith(nextFeed);
        livePagination.replaceWith(nextPagination);
        const finalUrl = response.url || url.href;
        if (pushHistory) window.history.pushState(markHomeHistoryEntry(window.history.state), '', finalUrl);
        if (window.scrollY !== scrollY) window.scrollTo(0, scrollY);
      } catch (error) {
        if (error.name !== 'AbortError' && homeFeedController === controller) window.location.assign(url.href);
      } finally {
        if (homeFeedController === controller) {
          homeFeedController = null;
          homeLayout.querySelector('[data-home-articles]')?.removeAttribute('aria-busy');
        }
      }
    };

    document.addEventListener('click', (event) => {
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      const link = event.target instanceof Element ? event.target.closest('.home-layout .pagination-wrap a[href]') : null;
      if (!link || link.target || link.hasAttribute('download')) return;
      const url = new URL(link.href, window.location.href);
      if (url.origin !== window.location.origin) return;
      event.preventDefault();
      loadHomeFeedPage(url.href);
    });

    window.addEventListener('popstate', (event) => {
      if (event.state?.morrowHomeFeed) loadHomeFeedPage(window.location.href, false);
    });
  }

  const ownerConsole = document.querySelector('[data-owner-console]');
  if (ownerConsole) {
    const ownerNoticeMessages = {
      post_saved: '文章内容已保存。', post_deleted: '内容已从回收站永久删除。', post_trashed: '内容已移入回收站，可以随时恢复。',
      post_restored: '内容已恢复。', status_saved: '内容状态已更新。', comment_approved: '留言已通过。',
      comment_waiting: '留言已移回待审核。', comment_spam: '留言已标记为垃圾。', comment_deleted: '留言已删除。',
      category_added: '分类已添加。', category_updated: '分类资料已保存。', category_deleted: '分类已删除，文章内容保留并已重新归类。',
      tag_added: '标签已添加。', tag_updated: '标签资料已保存。', tag_deleted: '标签已删除，文章内容保留。',
      series_updated: '系列名称已更新，相关内容归属同步调整。', series_deleted: '系列已删除，文章内容本身保留。',
      friend_added: '友情链接已添加。', friend_updated: '友情链接已更新。', friend_deleted: '友情链接已移除。',
      settings_saved: '站点设置已保存。', invalid_input: '没有保存：请检查必填项、重复名称或提交内容。',
      action_error: '操作没有完成，请检查网络后重试。',
    };
    const showOwnerNotice = (notice, isError = false) => {
      let status = ownerConsole.querySelector('[data-owner-live-notice]');
      if (!status) {
        status = document.createElement('p');
        status.className = 'owner-console-save-success';
        status.dataset.ownerLiveNotice = '';
        status.setAttribute('role', 'status');
        ownerConsole.querySelector('.owner-console-heading')?.insertAdjacentElement('afterend', status);
      }
      status.textContent = ownerNoticeMessages[notice] || String(notice || '操作已处理。');
      status.classList.toggle('is-error', isError);
    };
    try {
      const pendingNotice = JSON.parse(window.sessionStorage.getItem('morrow-owner-notice') || 'null');
      if (pendingNotice?.notice) {
        if (!ownerConsole.querySelector('.owner-console-save-success')) showOwnerNotice(pendingNotice.notice);
        window.sessionStorage.removeItem('morrow-owner-notice');
      }
    } catch (_) { /* Storage may be disabled; server-side notices remain available. */ }

    const tabs = [...ownerConsole.querySelectorAll('[data-owner-console-tab]')];
    const views = [...ownerConsole.querySelectorAll('[data-owner-console-view]')];
    const writeKindSelect = ownerConsole.querySelector('[data-owner-write-kind-select]');
    const categoryItems = [...ownerConsole.querySelectorAll('[data-owner-category-item]')];
    const syncCategories = () => {
      const isFeel = writeKindSelect?.value === 'feel';
      categoryItems.forEach((item) => {
        const isFeelCategory = item.dataset.feelCategory === 'true';
        const checkbox = item.querySelector('input[type="checkbox"]');
        item.hidden = isFeel ? !isFeelCategory : isFeelCategory;
        if (checkbox) {
          checkbox.disabled = isFeel ? !isFeelCategory : isFeelCategory;
          if (isFeelCategory) checkbox.checked = isFeel;
        }
      });
    };

    const activateOwnerTab = (key, focus = false) => {
      const selected = tabs.find((tab) => tab.dataset.ownerConsoleTab === key);
      if (!selected) return;
      tabs.forEach((tab) => {
        const active = tab === selected;
        tab.setAttribute('aria-selected', String(active));
        tab.tabIndex = active ? 0 : -1;
      });
      views.forEach((view) => {
        view.hidden = view.dataset.ownerConsoleView !== key;
        if (!view.hidden) view.setAttribute('aria-labelledby', selected.id);
      });
      if (key !== 'write') syncCategories();
      const nextUrl = new URL(window.location.href);
      nextUrl.searchParams.set('tab', key);
      if (key !== 'write') nextUrl.searchParams.delete('edit');
      window.history.replaceState({}, '', nextUrl);
      if (focus) selected.focus();
    };

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', () => activateOwnerTab(tab.dataset.ownerConsoleTab));
      tab.addEventListener('keydown', (event) => {
        let nextIndex = index;
        if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
        else if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
        else if (event.key === 'Home') nextIndex = 0;
        else if (event.key === 'End') nextIndex = tabs.length - 1;
        else return;
        event.preventDefault();
        activateOwnerTab(tabs[nextIndex].dataset.ownerConsoleTab, true);
      });
    });

    ownerConsole.querySelectorAll('[data-owner-console-jump]').forEach((shortcut) => {
      shortcut.addEventListener('click', () => {
        const key = shortcut.dataset.ownerConsoleJump;
        if (shortcut.dataset.ownerWriteKind && writeKindSelect) {
          writeKindSelect.value = shortcut.dataset.ownerWriteKind;
          syncCategories();
        }
        activateOwnerTab(key);
        if (key === 'write') ownerConsole.querySelector('[name="title"]')?.focus();
      });
    });

    writeKindSelect?.addEventListener('change', syncCategories);
    syncCategories();
    activateOwnerTab(ownerConsole.dataset.initialTab || 'overview');

    ownerConsole.querySelectorAll('form[data-owner-confirm]').forEach((form) => {
      form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.ownerConfirm || '确定执行此操作吗？')) event.preventDefault();
      });
    });

    ownerConsole.addEventListener('submit', async (event) => {
      const form = event.target;
      if (event.defaultPrevented || !(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post') return;
      if (!form.querySelector('[name="owner_action"], [name="argon_note_action"]')) return;
      const actionUrl = new URL(form.action || window.location.href, window.location.href);
      if (actionUrl.origin !== window.location.origin) return;
      event.preventDefault();
      const submitter = event.submitter instanceof HTMLButtonElement ? event.submitter : form.querySelector('button[type="submit"],input[type="submit"]');
      const formData = new FormData(form);
      // Disabled submit buttons are omitted from FormData; preserve intent such as "draft".
      if (submitter?.name && !formData.has(submitter.name)) formData.append(submitter.name, submitter.value);
      let reloadScheduled = false;
      if (submitter) submitter.disabled = true;
      try {
        const response = await fetch(actionUrl.href, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (!response.ok || !result.ok) {
          showOwnerNotice(result.notice || 'action_error', true);
          return;
        }
        try { window.sessionStorage.setItem('morrow-owner-notice', JSON.stringify(result)); } catch (_) {}
        // Reload this GET page after the JSON response; never render the POST/redirect response as a blank document.
        reloadScheduled = true;
        window.location.reload();
      } catch (_) {
        showOwnerNotice('action_error', true);
      } finally {
        if (submitter && !reloadScheduled) submitter.disabled = false;
      }
    });

    ownerConsole.querySelectorAll('[data-markdown-insert]').forEach((button) => {
      button.addEventListener('click', () => {
        const editor = ownerConsole.querySelector('[data-owner-markdown-editor]');
        if (!editor) return;
        const start = editor.selectionStart;
        const end = editor.selectionEnd;
        const selected = editor.value.slice(start, end) || '文字';
        const before = button.dataset.insertBefore || '';
        const after = button.dataset.insertAfter || '';
        editor.setRangeText(`${before}${selected}${after}`, start, end, 'select');
        editor.focus();
      });
    });
  }

  const deviceBrowser = document.querySelector('[data-device-browser]');
  if (deviceBrowser) {
    const search = deviceBrowser.querySelector('[data-device-search]');
    const cards = [...deviceBrowser.querySelectorAll('[data-device-searchable]')];
    const groups = [...deviceBrowser.querySelectorAll('[data-device-category]')];
    const filters = [...deviceBrowser.querySelectorAll('[data-device-filter]')];
    const count = deviceBrowser.querySelector('[data-device-count]');
    const empty = deviceBrowser.querySelector('[data-device-empty]');
    let activeCategory = 'all';
    const normalize = (value) => String(value || '').trim().toLocaleLowerCase();
    const updateDevices = () => {
      const query = normalize(search?.value);
      let visibleCount = 0;
      groups.forEach((group) => {
        let groupCount = 0;
        const categoryMatches = activeCategory === 'all' || group.dataset.deviceCategory === activeCategory;
        group.querySelectorAll('[data-device-searchable]').forEach((card) => {
          const matches = categoryMatches && (!query || normalize(card.dataset.deviceSearch).includes(query));
          card.hidden = !matches;
          if (matches) groupCount++;
        });
        group.hidden = groupCount === 0;
        visibleCount += groupCount;
        if (query && groupCount > 0) group.open = true;
      });
      if (count) count.textContent = `${visibleCount} / ${cards.length} 款设备`;
      if (empty) empty.hidden = visibleCount !== 0;
    };
    search?.addEventListener('input', updateDevices);
    filters.forEach((button) => button.addEventListener('click', () => {
      activeCategory = button.dataset.deviceFilter || 'all';
      filters.forEach((candidate) => candidate.setAttribute('aria-pressed', String(candidate === button)));
      updateDevices();
    }));
    updateDevices();
  }

  const calendar = document.querySelector('[data-calendar]');
  if (calendar) {
    const calendarGrid = calendar.querySelector('[data-calendar-grid]');
    const calendarLabel = calendar.querySelector('[data-calendar-label]');
    let calendarYear = Number(calendar.dataset.calendarYear);
    let calendarMonth = Number(calendar.dataset.calendarMonth);
    const weekLabels = ['一', '二', '三', '四', '五', '六', '日'];
    const renderCalendar = (days = []) => {
      if (!calendarGrid || !calendarLabel) return;
      calendarLabel.textContent = `${calendarYear}年${calendarMonth}月`;
      calendar.dataset.calendarYear = String(calendarYear);
      calendar.dataset.calendarMonth = String(calendarMonth);
      calendarGrid.replaceChildren();
      weekLabels.forEach((label) => {
        const weekday = document.createElement('span');
        weekday.className = 'calendar-weekday';
        weekday.textContent = label;
        calendarGrid.append(weekday);
      });
      const firstDay = new Date(calendarYear, calendarMonth - 1, 1);
      const offset = (firstDay.getDay() + 6) % 7;
      const dayCount = new Date(calendarYear, calendarMonth, 0).getDate();
      for (let index = 0; index < offset; index += 1) {
        const blank = document.createElement('span');
        blank.className = 'calendar-blank';
        blank.setAttribute('aria-hidden', 'true');
        calendarGrid.append(blank);
      }
      const publishedDays = new Set(days.map(Number));
      const today = new Date();
      for (let day = 1; day <= dayCount; day += 1) {
        const date = `${calendarYear}-${String(calendarMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const link = document.createElement('a');
        link.className = 'calendar-day';
        link.textContent = String(day);
        link.href = new URL(`archives?date=${date}`, calendar.dataset.calendarUrl).href;
        link.setAttribute('aria-label', `${date}${publishedDays.has(day) ? ' 有文章' : ''}`);
        link.dataset.calendarDay = String(day);
        if (publishedDays.has(day)) link.classList.add('has-post');
        if (today.getFullYear() === calendarYear && today.getMonth() + 1 === calendarMonth && today.getDate() === day) link.classList.add('is-today');
        calendarGrid.append(link);
      }
    };
    const loadCalendarMonth = async () => {
      if (!calendarGrid || !calendarLabel) return;
      const url = new URL(calendar.dataset.calendarUrl);
      url.searchParams.set('morrow_calendar', `${calendarYear}-${String(calendarMonth).padStart(2, '0')}`);
      try {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Calendar request failed');
        const data = await response.json();
        renderCalendar(Array.isArray(data.days) ? data.days : []);
      } catch (_) {
        renderCalendar([]);
      }
    };
    calendar.querySelectorAll('[data-calendar-shift]').forEach((button) => button.addEventListener('click', () => {
      calendarMonth += Number(button.dataset.calendarShift);
      if (calendarMonth < 1) { calendarMonth = 12; calendarYear -= 1; }
      else if (calendarMonth > 12) { calendarMonth = 1; calendarYear += 1; }
      loadCalendarMonth();
    }));
  }

  const readingPlayer = document.querySelector('[data-reading-player]');
  if (readingPlayer) {
    const audio = readingPlayer.querySelector('[data-reading-audio]');
    const title = readingPlayer.querySelector('[data-reading-title]');
    const time = readingPlayer.querySelector('[data-reading-time]');
    const seek = readingPlayer.querySelector('[data-reading-seek]');
    const volume = readingPlayer.querySelector('[data-reading-volume]');
    const toggle = readingPlayer.querySelector('[data-reading-toggle]');
    const playlist = readingPlayer.querySelector('[data-reading-playlist]');
    const playlistButtons = [...readingPlayer.querySelectorAll('[data-reading-track]')];
    const tracks = playlistButtons.map((button) => ({ title: button.textContent.trim(), url: button.dataset.readingUrl || '' }));
    let currentTrack = 0;
    const formatTime = (seconds) => {
      if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
      const minutes = Math.floor(seconds / 60);
      const remainder = Math.floor(seconds % 60).toString().padStart(2, '0');
      return `${minutes}:${remainder}`;
    };
    const syncTime = () => {
      if (!audio || !time) return;
      const duration = audio.duration;
      time.textContent = `${formatTime(audio.currentTime)} / ${formatTime(duration)}`;
      if (seek && Number.isFinite(duration) && duration > 0 && !seek.matches(':active')) {
        seek.value = String(Math.round((audio.currentTime / duration) * 1000));
      }
    };
    const markTrack = () => playlistButtons.forEach((button, index) => {
      if (index === currentTrack) button.setAttribute('aria-current', 'true');
      else button.removeAttribute('aria-current');
    });
    const selectTrack = (index, autoplay = false) => {
      if (!tracks.length || !audio) return;
      currentTrack = (index + tracks.length) % tracks.length;
      audio.src = tracks[currentTrack].url;
      audio.load();
      if (title) title.textContent = tracks[currentTrack].title;
      if (seek) seek.value = '0';
      markTrack();
      syncTime();
      if (autoplay) audio.play().catch(() => { if (time) time.textContent = '无法播放此音频'; });
    };
    const syncPlayButton = () => {
      if (!toggle || !audio) return;
      const playing = !audio.paused;
      toggle.textContent = playing ? 'Ⅱ' : '▶';
      toggle.setAttribute('aria-label', playing ? '暂停' : '播放');
      readingPlayer.classList.toggle('is-playing', playing);
    };
    if (audio && tracks.length) {
      selectTrack(0);
      audio.volume = Number(volume?.value ?? 0.65);
      toggle?.addEventListener('click', () => {
        if (audio.paused) audio.play().catch(() => { if (time) time.textContent = '无法播放此音频'; });
        else audio.pause();
      });
      readingPlayer.querySelector('[data-reading-prev]')?.addEventListener('click', () => {
        if (audio.currentTime > 3) { audio.currentTime = 0; return; }
        selectTrack(currentTrack - 1, true);
      });
      readingPlayer.querySelector('[data-reading-next]')?.addEventListener('click', () => selectTrack(currentTrack + 1, true));
      readingPlayer.querySelector('[data-reading-list-toggle]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const open = playlist?.hidden ?? true;
        if (playlist) playlist.hidden = !open;
        button.setAttribute('aria-expanded', String(open));
      });
      playlistButtons.forEach((button) => button.addEventListener('click', () => selectTrack(Number(button.dataset.readingTrack), true)));
      volume?.addEventListener('input', () => { audio.volume = Number(volume.value); });
      seek?.addEventListener('input', () => {
        if (Number.isFinite(audio.duration) && audio.duration > 0) audio.currentTime = (Number(seek.value) / 1000) * audio.duration;
      });
      audio.addEventListener('timeupdate', syncTime);
      audio.addEventListener('loadedmetadata', syncTime);
      audio.addEventListener('durationchange', syncTime);
      audio.addEventListener('play', syncPlayButton);
      audio.addEventListener('pause', syncPlayButton);
      audio.addEventListener('ended', () => selectTrack(currentTrack + 1, true));
      audio.addEventListener('error', () => { if (time) time.textContent = '音频加载失败，请检查曲目地址'; });
    }
  }

  const typewriterHeading = document.querySelector('[data-typewriter]');
  const typewriterCaret = typewriterHeading?.querySelector('.typewriter-caret');
  if (typewriterHeading) {
    const headlineLines = [...typewriterHeading.querySelectorAll('[data-typewriter-line]')];
    let headlinePool = [];
    try {
      const parsed = JSON.parse(typewriterHeading.dataset.heroPhrases || '[]');
      headlinePool = Array.isArray(parsed) ? parsed.filter((phrase) => Array.isArray(phrase) && phrase.length >= 2 && phrase.slice(0, 2).every((line) => typeof line === 'string')) : [];
    } catch (_) { headlinePool = []; }
    if (headlinePool.length && headlineLines.length >= 2) {
      let choice = Math.floor(Math.random() * headlinePool.length);
      try {
        const previous = Number(window.sessionStorage.getItem('morrow-home-headline'));
        if (headlinePool.length > 1 && Number.isInteger(previous) && previous === choice) choice = (choice + 1 + Math.floor(Math.random() * (headlinePool.length - 1))) % headlinePool.length;
        window.sessionStorage.setItem('morrow-home-headline', String(choice));
      } catch (_) { /* Keep the randomly selected server-rendered fallback. */ }
      const phrase = headlinePool[choice];
      headlineLines.slice(0, 2).forEach((line, index) => {
        line.dataset.typewriterLine = phrase[index];
        line.textContent = phrase[index];
      });
      if (typewriterCaret && headlineLines[1]) headlineLines[1].append(typewriterCaret);
      typewriterHeading.setAttribute('aria-label', phrase.slice(0, 2).join(''));
    }
  }
  if (typewriterHeading && !prefersReducedMotion) {
    const lines = [...typewriterHeading.querySelectorAll('[data-typewriter-line]')];
    if (lines.length) {
      lines.forEach((line) => { line.textContent = ''; });
      if (typewriterCaret && lines[1]) lines[1].append(typewriterCaret);
      typewriterHeading.classList.add('is-typing');
      let lineIndex = 0;
      let characterIndex = 0;
      const typeNextCharacter = () => {
        const line = lines[lineIndex];
        const text = line.dataset.typewriterLine || '';
        line.textContent = text.slice(0, characterIndex + 1);
        if (typewriterCaret && lineIndex === 1) line.append(typewriterCaret);
        characterIndex += 1;
        if (characterIndex >= text.length) {
          lineIndex += 1;
          characterIndex = 0;
          if (lineIndex >= lines.length) {
            typewriterHeading.classList.replace('is-typing', 'is-typed');
            return;
          }
          window.setTimeout(typeNextCharacter, 180);
          return;
        }
        window.setTimeout(typeNextCharacter, 82);
      };
      typeNextCharacter();
    }
  }

  const searchButton = document.querySelector('[data-search-toggle]');
  const searchPanel = document.querySelector('[data-search-panel]');
  const searchBackdrop = document.querySelector('[data-search-backdrop]');
  const searchInput = searchPanel?.querySelector('input[type="search"]');
  const closeSearch = () => {
    if (!searchPanel || !searchButton) return;
    searchPanel.hidden = true;
    if (searchBackdrop) searchBackdrop.hidden = true;
    document.body.classList.remove('search-open');
    searchButton.setAttribute('aria-expanded', 'false');
  };
  searchButton?.addEventListener('click', () => {
    if (!searchPanel) return;
    const opening = searchPanel.hidden;
    searchPanel.hidden = !opening;
    if (searchBackdrop) searchBackdrop.hidden = !opening;
    document.body.classList.toggle('search-open', opening);
    searchButton.setAttribute('aria-expanded', String(opening));
    if (opening) window.setTimeout(() => searchInput?.focus(), 30);
    else searchButton.focus();
  });
  searchPanel?.querySelector('[data-search-close]')?.addEventListener('click', closeSearch);
  searchBackdrop?.addEventListener('click', closeSearch);
  document.addEventListener('click', (event) => {
    if (!searchPanel?.hidden && !searchPanel?.contains(event.target) && !searchButton?.contains(event.target) && !searchBackdrop?.contains(event.target)) closeSearch();
  });

  const backTop = document.querySelector('[data-back-top]');
  const updateBackTop = () => backTop?.classList.toggle('visible', window.scrollY > window.innerHeight * .65);
  window.addEventListener('scroll', updateBackTop, { passive: true });
  updateBackTop();
  backTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  const lyrics = document.querySelector('[data-rotating-lyrics]');
  if (lyrics && !prefersReducedMotion) {
    const lines = [...lyrics.querySelectorAll('li')];
    let activeLine = Math.max(0, lines.findIndex((line) => line.classList.contains('is-current')));
    if (lines.length > 1) window.setInterval(() => {
      lines[activeLine]?.classList.remove('is-current');
      activeLine = (activeLine + 1) % lines.length;
      lines[activeLine]?.classList.add('is-current');
    }, 2400);
  }

  const body = document.querySelector('#article-body');
  const outlineList = document.querySelector('#outline-list');
  const outlineEmpty = document.querySelector('[data-outline-empty]');
  if (body && outlineList) {
    const headings = [...body.querySelectorAll('h2, h3')];
    if (headings.length) {
      const groups = [];
      const linkByHeading = new Map();
      headings.forEach((heading, index) => {
        if (!heading.id) heading.id = `section-${index + 1}`;
        let group = groups[groups.length - 1];
        const startsGroup = heading.tagName === 'H2' || !group;
        if (startsGroup) {
          const item = document.createElement('li');
          const details = document.createElement('details');
          details.className = 'sidebar-outline-chapter';
          const summary = document.createElement('summary');
          const chapterLink = document.createElement('a');
          chapterLink.href = `#${encodeURIComponent(heading.id)}`;
          chapterLink.textContent = heading.textContent.trim();
          summary.append(chapterLink);
          const children = document.createElement('ol');
          children.className = 'sidebar-outline-sublist';
          details.append(summary, children);
          item.append(details);
          outlineList.append(item);
          group = { details, children, headings: [] };
          groups.push(group);
        }
        group.headings.push(heading);
        let link = group.details.querySelector('summary a');
        if (heading.tagName === 'H3' && !startsGroup) {
          const childItem = document.createElement('li');
          link = document.createElement('a');
          link.href = `#${encodeURIComponent(heading.id)}`;
          link.textContent = heading.textContent.trim();
          childItem.append(link);
          group.children.append(childItem);
        }
        linkByHeading.set(heading, link);
      });
      if (outlineEmpty) outlineEmpty.hidden = true;
      const activateHeading = (heading) => {
        const activeGroup = groups.find((group) => group.headings.includes(heading));
        groups.forEach((group) => {
          group.details.open = group === activeGroup;
          group.details.classList.toggle('is-current', group === activeGroup);
        });
        linkByHeading.forEach((link) => link.classList.toggle('current', link === linkByHeading.get(heading)));
      };
      groups.forEach((group) => group.details.addEventListener('toggle', () => {
        if (group.details.open) groups.forEach((other) => { if (other !== group) other.details.open = false; });
      }));
      activateHeading(headings[0]);
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
          const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
          if (visible.length) activateHeading(visible[0].target);
        }, { rootMargin: '-12% 0px -72% 0px' });
        headings.forEach((heading) => observer.observe(heading));
      }
    } else if (outlineEmpty) outlineEmpty.textContent = '本文暂无章节目录。';
  }

  const sidebarTabs = [...document.querySelectorAll('[data-sidebar-mode-tab]')];
  const sidebarPanels = [...document.querySelectorAll('[data-sidebar-mode-panel]')];
  sidebarTabs.forEach((tab) => tab.addEventListener('click', () => {
    const selected = tab.dataset.sidebarModeTab;
    sidebarTabs.forEach((item) => {
      const active = item === tab;
      item.setAttribute('aria-selected', String(active));
      item.tabIndex = active ? 0 : -1;
    });
    sidebarPanels.forEach((panel) => { panel.hidden = panel.dataset.sidebarModePanel !== selected; });
  }));

  document.querySelectorAll('.article-body pre').forEach((pre) => {
    if (pre.querySelector('.code-copy')) return;
    const button = document.createElement('button');
    button.className = 'code-copy';
    button.type = 'button';
    button.textContent = '复制';
    button.addEventListener('click', async () => {
      const code = pre.querySelector('code')?.innerText || pre.innerText;
      try {
        await navigator.clipboard.writeText(code);
        button.textContent = '已复制';
        window.setTimeout(() => { button.textContent = '复制'; }, 1400);
      } catch (_) { button.textContent = '复制失败'; }
    });
    pre.append(button);
  });

  if (body) {
    body.querySelectorAll('a[href]').forEach((link) => {
      let target;
      try { target = new URL(link.href, window.location.href); } catch (_) { return; }
      if (target.origin !== window.location.origin || !target.pathname.startsWith('/usr/uploads/') || !/\.html?$/i.test(target.pathname)) return;

      const preview = document.createElement('details');
      preview.className = 'html-preview';
      const summary = document.createElement('summary');
      summary.textContent = '展开网页预览';
      const frame = document.createElement('iframe');
      frame.className = 'html-preview-frame';
      frame.title = `${link.textContent.trim() || '附件'}网页预览`;
      frame.loading = 'lazy';
      frame.referrerPolicy = 'no-referrer';
      frame.setAttribute('sandbox', 'allow-scripts');
      const themedDocumentUrl = new URL(target.href);
      themedDocumentUrl.searchParams.set('morrow-theme', root.dataset.theme === 'dark' ? 'dark' : 'light');
      frame.dataset.src = themedDocumentUrl.href;
      preview.append(summary, frame);
      link.insertAdjacentElement('afterend', preview);

      const loadPreview = () => {
        if (preview.open && !frame.src) frame.src = frame.dataset.src;
      };
      preview.addEventListener('toggle', loadPreview);
      link.removeAttribute('download');
      link.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        preview.open = true;
        loadPreview();
        preview.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    });
  }

  if (window.Prism && body) window.Prism.highlightAllUnder(body);
})();
