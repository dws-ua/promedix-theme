/* ProMedix theme JS: menu/search toggles, size tiles on product page, favourite hearts. */
(function () {
  'use strict';

  /* Toggles: mobile menu, search panel. */
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-pmx-toggle]');
    if (!t) return;
    /* Burger opens the off-canvas drawer when the theme printed one. */
    if (t.classList.contains('pmx-burger') && document.getElementById('pmx-drawer')) { openDrawer(t); return; }
    var target = document.getElementById(t.getAttribute('aria-controls'));
    if (!target) return;
    var open = t.getAttribute('aria-expanded') === 'true';
    t.setAttribute('aria-expanded', open ? 'false' : 'true');
    target.classList.toggle('is-open', !open);
    if (!open) {
      var input = target.querySelector('input[type=search]');
      if (input) input.focus();
    }
  });
  /* Off-canvas drawer: menu, contacts, language. */
  var drawerOpener = null;
  function openDrawer(opener) {
    var d = document.getElementById('pmx-drawer');
    if (!d) return;
    drawerOpener = opener || null;
    d.hidden = false;
    document.documentElement.classList.add('pmx-drawer-open');
    if (opener) opener.setAttribute('aria-expanded', 'true');
    requestAnimationFrame(function () { d.classList.add('is-open'); });
    var close = d.querySelector('.pmx-drawer__close');
    if (close) close.focus();
  }
  function closeDrawer() {
    var d = document.getElementById('pmx-drawer');
    if (!d || d.hidden) return;
    d.classList.remove('is-open');
    document.documentElement.classList.remove('pmx-drawer-open');
    document.querySelectorAll('.pmx-burger').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
    setTimeout(function () { d.hidden = true; }, 250);
    if (drawerOpener) drawerOpener.focus();
  }
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-pmx-drawer-close]')) { closeDrawer(); return; }
    /* A link inside the drawer: close it (same-page anchors otherwise stay hidden behind it). */
    if (e.target.closest('#pmx-drawer a')) closeDrawer();
  });
  document.addEventListener('keydown', function (e) {
    var d = document.getElementById('pmx-drawer');
    if (!d || d.hidden) return;
    if (e.key === 'Escape') { closeDrawer(); return; }
    if (e.key !== 'Tab') return;
    var f = d.querySelectorAll('a[href], button:not([disabled]), input, select');
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });

  /* Bottom bar: search opens the header search panel. */
  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-pmx-bnav-search]')) return;
    var t = document.querySelector('.pmx-header [aria-controls="pmx-search-panel"]');
    if (t) { window.scrollTo({ top: 0, behavior: 'smooth' }); if (t.getAttribute('aria-expanded') !== 'true') t.click(); else { var i = document.querySelector('#pmx-search-panel input[type=search]'); if (i) i.focus(); } }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[data-pmx-toggle][aria-expanded="true"]').forEach(function (t) { t.click(); });
  });

  /* Favourites: heart on product cards and gallery, remembered in this browser. */
  var KEY = 'pmx-fav';
  function favs() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (err) { return []; } }
  function favCount() {
    var n = favs().length;
    document.querySelectorAll('.pmx-bnav__count--fav').forEach(function (el) { el.textContent = n; el.hidden = !n; });
  }
  function saveFavs(list) { try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (err) {} favCount(); }
  function productId(el) {
    var m = (el.className || '').match(/post-(\d+)/);
    return m ? m[1] : null;
  }
  function heart(id) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'pmx-fav';
    b.setAttribute('data-id', id);
    b.setAttribute('aria-label', 'В обране');
    b.setAttribute('aria-pressed', favs().indexOf(id) > -1 ? 'true' : 'false');
    b.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/></svg>';
    return b;
  }
  function addHearts(root) {
    root.querySelectorAll('li.product').forEach(function (li) {
      var id = productId(li);
      if (id && !li.querySelector('.pmx-fav')) li.appendChild(heart(id));
    });
  }
  document.querySelectorAll('ul.products').forEach(addHearts);
  favCount();

  /* "Показати ще": fetch the next catalog page and append its cards to the grid.
     The button is a plain link to ?page N+1, so without JS (or on error) it just navigates. */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-pmx-loadmore]');
    if (!btn || !window.fetch || !window.DOMParser) return;
    var wrap = btn.closest('[data-pmx-loadmore-wrap]');
    var scope = wrap && wrap.parentNode;
    var grid = scope && scope.querySelector('ul.products');
    if (!grid) return;
    e.preventDefault();
    if (btn.classList.contains('is-loading')) return;
    btn.classList.add('is-loading');
    btn.setAttribute('aria-busy', 'true');
    var url = btn.href;
    fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var items = doc.querySelectorAll('ul.products li.product');
        if (!items.length) throw new Error('empty');
        var first = null;
        items.forEach(function (li) {
          var node = document.importNode(li, true);
          grid.appendChild(node);
          if (!first) first = node;
        });
        addHearts(grid);
        /* Numbered pagination follows the last loaded page. */
        var navOld = scope.querySelector('nav.woocommerce-pagination');
        var navNew = doc.querySelector('nav.woocommerce-pagination');
        if (navOld && navNew) navOld.replaceWith(document.importNode(navNew, true));
        var status = wrap.querySelector('[data-pmx-loadmore-status]');
        var nextBtn = doc.querySelector('[data-pmx-loadmore]');
        if (nextBtn) {
          btn.href = nextBtn.getAttribute('href');
          var nextStatus = doc.querySelector('[data-pmx-loadmore-status]');
          if (status && nextStatus) status.textContent = nextStatus.textContent;
          btn.classList.remove('is-loading');
          btn.removeAttribute('aria-busy');
        } else {
          /* Keep the (hidden) wrapper: the numbered pagination after it stays visually hidden. */
          wrap.hidden = true;
        }
        var link = first && first.querySelector('a');
        if (link) link.focus({ preventScroll: true });
      })
      .catch(function () { location.href = url; });
  });
  var single = document.querySelector('div.product[id^="product-"]');
  var gallery = single && single.querySelector('.woocommerce-product-gallery');
  if (gallery) gallery.appendChild(heart(single.id.replace('product-', '')));
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.pmx-fav');
    if (!b) return;
    e.preventDefault();
    var id = b.getAttribute('data-id'), list = favs(), i = list.indexOf(id);
    if (i > -1) list.splice(i, 1); else list.push(id);
    saveFavs(list);
    b.setAttribute('aria-pressed', i > -1 ? 'false' : 'true');
  });

  /* Size tiles: replace variation selects with buttons (select stays as the source of truth). */
  function buildTiles(form) {
    form.querySelectorAll('table.variations select').forEach(function (sel) {
      if (sel.dataset.pmxTiles) return;
      sel.dataset.pmxTiles = '1';
      var wrap = document.createElement('div');
      wrap.className = 'pmx-tiles';
      wrap.setAttribute('role', 'group');
      Array.prototype.forEach.call(sel.options, function (o) {
        if (!o.value) return;
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'pmx-tile-btn';
        b.textContent = o.textContent;
        b.value = o.value;
        b.addEventListener('click', function () {
          var v = sel.value === o.value ? '' : o.value;
          if (window.jQuery) window.jQuery(sel).val(v).trigger('change'); else { sel.value = v; sel.dispatchEvent(new Event('change', { bubbles: true })); }
          sync();
        });
        wrap.appendChild(b);
      });
      sel.classList.add('pmx-visually-hidden');
      sel.parentNode.insertBefore(wrap, sel);
      /* Sizes whose every variation is out of stock (from WooCommerce's own variation data). */
      var soldOut = {};
      try {
        var vars = JSON.parse(form.getAttribute('data-product_variations') || 'false');
        var name = sel.getAttribute('data-attribute_name') || sel.name;
        if (Array.isArray(vars)) {
          var seen = {};
          vars.forEach(function (v) {
            var val = v.attributes && v.attributes[name];
            if (val === undefined || val === '') return;
            seen[val] = seen[val] || v.is_in_stock;
          });
          Object.keys(seen).forEach(function (k) { if (!seen[k]) soldOut[k] = true; });
        }
      } catch (err) {}
      function sync() {
        var enabled = {};
        Array.prototype.forEach.call(sel.options, function (o) { if (o.value && !o.disabled) enabled[o.value] = true; });
        wrap.querySelectorAll('button').forEach(function (b) {
          var off = !enabled[b.value] || soldOut[b.value];
          b.setAttribute('aria-pressed', sel.value === b.value ? 'true' : 'false');
          b.disabled = !!off;
          b.classList.toggle('is-soldout', !!off);
          b.setAttribute('aria-label', b.textContent + (off ? ', немає в наявності' : ''));
          b.title = off ? 'Немає в наявності' : '';
        });
      }
      sel.addEventListener('change', sync);
      if (window.jQuery) window.jQuery(form).on('woocommerce_update_variation_values reset_data', function () { setTimeout(sync, 0); });
      sync();
    });
    /* "Таблиця розмірів" link under the sizes: opens the size help dialog. */
    var dlg = document.getElementById('pmx-size-dialog');
    var tbl = form.querySelector('table.variations');
    if (dlg && tbl && !form.querySelector('.pmx-size-link')) {
      var a = document.createElement('button');
      a.type = 'button';
      a.className = 'pmx-size-link';
      a.innerHTML = '<svg class="pmx-ico" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 15 15 3l6 6L9 21z"/><path d="m7 11 2 2M10 8l2 2M13 5l2 2"/></svg><span>Як обрати розмір</span>';
      a.addEventListener('click', function () { if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', ''); });
      /* Click on the dimmed backdrop (the dialog element itself, outside its content) closes it. */
      if (!dlg.dataset.pmxBackdrop) {
        dlg.dataset.pmxBackdrop = '1';
        dlg.addEventListener('click', function (e) { if (e.target === dlg && dlg.close) dlg.close(); });
      }
      tbl.parentNode.insertBefore(a, tbl.nextSibling);
    }
  }
  document.querySelectorAll('form.variations_form').forEach(buildTiles);
})();

/* "Знайдемо вашу пару?": category select changes where the form goes; empty size is dropped. */
(function () {
  document.addEventListener('submit', function (e) {
    var f = e.target.closest && e.target.closest('[data-pmx-picker]');
    if (!f) return;
    e.preventDefault();
    var cat = f.querySelector('[data-pmx-picker-cat]');
    var size = f.querySelector('[name="filter_size"]');
    var url = new URL(cat && cat.value ? cat.value : f.action, location.href);
    if (size && size.value) {
      url.searchParams.set('filter_size', size.value);
      url.searchParams.set('query_type_size', 'or');
    }
    location.href = url.toString();
  });
})();

/* Hero slider: tabs with progress, arrows, autoplay (pauses on hover/focus/hidden tab), swipe. */
(function () {
  var DELAY = 7000;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function init(root) {
    var track = root.querySelector('.pmx-slider__track');
    var slides = track ? Array.prototype.slice.call(track.querySelectorAll(':scope > .pmx-slide')) : [];
    if (slides.length < 2) return;
    var cur = 0, timer = null, paused = false;
    var nav = document.createElement('div');
    nav.className = 'pmx-slider__nav alignwide';
    var tabs = document.createElement('div');
    tabs.className = 'pmx-slider__tabs';
    tabs.setAttribute('role', 'tablist');
    tabs.setAttribute('aria-label', 'Слайди');
    var btns = slides.map(function (s, i) {
      var tag = s.querySelector('.pmx-slide__tag');
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'pmx-slider__tab';
      b.setAttribute('role', 'tab');
      b.innerHTML = '<b>0' + (i + 1) + '</b><span></span>';
      b.querySelector('span').textContent = tag ? tag.textContent.trim() : 'Слайд ' + (i + 1);
      b.addEventListener('click', function () { go(i, true); });
      tabs.appendChild(b);
      s.setAttribute('role', 'tabpanel');
      return b;
    });
    var arrows = document.createElement('div');
    arrows.className = 'pmx-slider__arrows';
    arrows.innerHTML = '<button type="button" class="pmx-slider__arrow" data-dir="-1" aria-label="Попередній слайд"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 5-7 7 7 7"/></svg></button><button type="button" class="pmx-slider__arrow" data-dir="1" aria-label="Наступний слайд"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 5 7 7-7 7"/></svg></button>';
    arrows.addEventListener('click', function (e) {
      var a = e.target.closest('[data-dir]');
      if (a) go(cur + parseInt(a.getAttribute('data-dir'), 10), true);
    });
    nav.appendChild(tabs);
    nav.appendChild(arrows);
    track.parentNode.insertBefore(nav, track.nextSibling);
    root.style.setProperty('--pmx-delay', DELAY + 'ms');

    function go(i, user) {
      cur = (i + slides.length) % slides.length;
      slides.forEach(function (s, k) {
        var on = k === cur;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', on ? 'false' : 'true');
        if ('inert' in s) s.inert = !on;
        btns[k].setAttribute('aria-selected', on ? 'true' : 'false');
        btns[k].tabIndex = on ? 0 : -1;
      });
      if (user) btns[cur].focus({ preventScroll: true });
      schedule();
    }
    function schedule() {
      clearTimeout(timer);
      root.classList.remove('is-playing');
      if (reduce || paused || document.hidden) return;
      void root.offsetWidth; /* restart the progress bar */
      root.classList.add('is-playing');
      timer = setTimeout(function () { go(cur + 1); }, DELAY);
    }
    function pause(v) { paused = v; schedule(); }
    root.addEventListener('mouseenter', function () { pause(true); });
    root.addEventListener('mouseleave', function () { pause(false); });
    root.addEventListener('focusin', function () { pause(true); });
    root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) pause(false); });
    document.addEventListener('visibilitychange', schedule);
    tabs.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { go(cur + 1, true); e.preventDefault(); }
      if (e.key === 'ArrowLeft') { go(cur - 1, true); e.preventDefault(); }
    });
    var x0 = null, y0 = null;
    track.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
    track.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0, dy = e.changedTouches[0].clientY - y0;
      if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) go(cur + (dx < 0 ? 1 : -1));
      x0 = null;
    });
    root.classList.add('is-ready');
    go(0);
  }
  function boot() { Array.prototype.forEach.call(document.querySelectorAll('.pmx-slider'), init); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();

/* Quantity stepper (product page and classic cart): − / + around WooCommerce's own number input. */
(function () {
  function wrap(q) {
    var input = q.querySelector('input.qty');
    if (!input || q.dataset.pmxStep || input.type === 'hidden') return;
    q.dataset.pmxStep = '1';
    q.classList.add('pmx-qty');
    function btn(dir, label, text) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'pmx-qty__btn';
      b.setAttribute('aria-label', label);
      b.textContent = text;
      b.addEventListener('click', function () {
        var step = parseFloat(input.step) || 1, min = input.min === '' ? 1 : parseFloat(input.min), max = input.max === '' ? Infinity : parseFloat(input.max);
        var v = (parseFloat(input.value) || 0) + dir * step;
        v = Math.max(min, Math.min(max, v));
        if (String(v) === input.value) return;
        input.value = v;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        if (window.jQuery) window.jQuery(input).trigger('change');
        var upd = document.querySelector('button[name="update_cart"]');
        if (upd) { upd.disabled = false; upd.removeAttribute('aria-disabled'); }
      });
      return b;
    }
    q.insertBefore(btn(-1, 'Зменшити кількість', '−'), input);
    q.appendChild(btn(1, 'Збільшити кількість', '+'));
  }
  function scan() { document.querySelectorAll('.quantity').forEach(wrap); }
  scan();
  if (window.jQuery) window.jQuery(document.body).on('updated_cart_totals updated_wc_div', scan);
})();

/* Product tabs (roving tabindex, arrow keys). */
(function () {
  document.querySelectorAll('[data-pmx-tabs]').forEach(function (root) {
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    function show(t, focus) {
      tabs.forEach(function (x) {
        var on = x === t;
        x.setAttribute('aria-selected', on ? 'true' : 'false');
        x.tabIndex = on ? 0 : -1;
        document.getElementById(x.getAttribute('aria-controls')).hidden = !on;
      });
      if (focus) t.focus();
    }
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { show(t); });
      t.addEventListener('keydown', function (e) {
        var n = null;
        if (e.key === 'ArrowRight') n = tabs[(i + 1) % tabs.length];
        if (e.key === 'ArrowLeft') n = tabs[(i - 1 + tabs.length) % tabs.length];
        if (e.key === 'Home') n = tabs[0];
        if (e.key === 'End') n = tabs[tabs.length - 1];
        if (n) { e.preventDefault(); show(n, true); }
      });
    });
  });
})();

/* Catalog filters: mobile panel, price range, clean URLs on submit. */
(function () {
  var panel = document.getElementById('pmx-filters');
  var open = document.querySelector('[data-pmx-filters-open]');
  if (panel && open) {
    var last = null;
    function set(on) {
      panel.classList.toggle('is-open', on);
      document.documentElement.classList.toggle('pmx-lock', on);
      open.setAttribute('aria-expanded', on ? 'true' : 'false');
      if (on) { last = document.activeElement; var f = panel.querySelector('summary, input, button'); if (f) f.focus(); }
      else if (last) last.focus();
    }
    open.addEventListener('click', function () { set(true); });
    panel.querySelector('[data-pmx-filters-close]').addEventListener('click', function () { set(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && panel.classList.contains('is-open')) set(false); });
  }
  document.querySelectorAll('[data-pmx-price]').forEach(function (box) {
    var lo = +box.dataset.lo, hi = +box.dataset.hi;
    var rMin = box.querySelector('[data-pmx-range="min"]'), rMax = box.querySelector('[data-pmx-range="max"]');
    var nMin = box.querySelector('[name="min_price"]'), nMax = box.querySelector('[name="max_price"]');
    function paint() {
      var a = (+rMin.value - lo) / (hi - lo) * 100, b = (+rMax.value - lo) / (hi - lo) * 100;
      box.style.setProperty('--a', a + '%');
      box.style.setProperty('--b', b + '%');
    }
    rMin.addEventListener('input', function () { if (+rMin.value > +rMax.value) rMin.value = rMax.value; nMin.value = rMin.value; paint(); });
    rMax.addEventListener('input', function () { if (+rMax.value < +rMin.value) rMax.value = rMin.value; nMax.value = rMax.value; paint(); });
    nMin.addEventListener('change', function () { rMin.value = Math.max(lo, Math.min(+nMin.value || lo, +rMax.value)); nMin.value = rMin.value; paint(); });
    nMax.addEventListener('change', function () { rMax.value = Math.min(hi, Math.max(+nMax.value || hi, +rMin.value)); nMax.value = rMax.value; paint(); });
    paint();
  });
  document.querySelectorAll('[data-pmx-filters]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var url = new URL(form.action, location.href), groups = {};
      new FormData(form).forEach(function (v, k) {
        if (v === '') return;
        if (/\[\]$/.test(k)) { k = k.slice(0, -2); (groups[k] = groups[k] || []).push(v); }
        else url.searchParams.set(k, v);
      });
      Object.keys(groups).forEach(function (k) { url.searchParams.set(k, groups[k].join(',')); });
      ['size', 'color'].forEach(function (a) { if (!groups['filter_' + a]) url.searchParams.delete('query_type_' + a); });
      var box = form.querySelector('[data-pmx-price]');
      if (box && +url.searchParams.get('min_price') <= +box.dataset.lo && +url.searchParams.get('max_price') >= +box.dataset.hi) {
        url.searchParams.delete('min_price');
        url.searchParams.delete('max_price');
      }
      location.href = url.toString();
    });
  });
  /* Sorting: submit on change (WooCommerce does this with jQuery; keep it working without). */
  document.querySelectorAll('.woocommerce-ordering select').forEach(function (s) {
    s.setAttribute('aria-label', 'Сортувати');
  });
})();

/* Favourites page: products from the WooCommerce Store API, ids from this browser. */
(function () {
  var root = document.querySelector('[data-pmx-favs]');
  if (!root) return;
  var ids = [];
  try { ids = JSON.parse(localStorage.getItem('pmx-fav')) || []; } catch (e) {}
  var list = root.querySelector('.pmx-favs__list'), empty = root.querySelector('[data-pmx-favs-empty]');
  var loading = root.querySelector('.pmx-favs__loading'), error = root.querySelector('.pmx-favs__error');
  if (!ids.length) return;
  empty.hidden = true;
  loading.hidden = false;
  fetch(root.dataset.api + '?per_page=50&include=' + ids.map(encodeURIComponent).join(','), { credentials: 'same-origin' })
    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(function (items) {
      loading.hidden = true;
      if (!items.length) { empty.hidden = false; return; }
      items.forEach(function (p) {
        var li = document.createElement('li');
        li.className = 'product post-' + p.id;
        var img = p.images && p.images[0];
        var price = p.prices ? (parseInt(p.prices.price, 10) / Math.pow(10, p.prices.currency_minor_unit)).toLocaleString('uk-UA') + ' грн' : '';
        li.innerHTML = '<a class="woocommerce-LoopProduct-link" href=""><img width="600" height="450" loading="lazy" alt=""><h2 class="woocommerce-loop-product__title"></h2><span class="price"></span></a>';
        var a = li.querySelector('a');
        a.href = p.permalink;
        if (img) { li.querySelector('img').src = img.thumbnail || img.src; li.querySelector('img').alt = img.alt || ''; }
        li.querySelector('h2').textContent = p.name.replace(/^.*?(ProMedix)/i, '$1');
        li.querySelector('.price').textContent = price;
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'pmx-fav'; b.setAttribute('data-id', String(p.id)); b.setAttribute('aria-pressed', 'true'); b.setAttribute('aria-label', 'Прибрати з обраного');
        b.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/></svg>';
        b.addEventListener('click', function () { setTimeout(function () { li.remove(); if (!list.children.length) { list.hidden = true; empty.hidden = false; } }, 0); });
        li.appendChild(b);
        list.appendChild(li);
      });
      list.hidden = false;
    })
    .catch(function () { loading.hidden = true; error.hidden = false; });
})();

/* Client-side form validation: messages under the field (the server validates again). */
(function () {
  document.querySelectorAll('[data-pmx-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var bad = null;
      form.querySelectorAll('[required]').forEach(function (f) {
        var box = f.closest('.pmx-field'), err = box && box.querySelector('.pmx-field__err');
        var empty = f.type === 'checkbox' ? !f.checked : !f.value.trim();
        if (box) box.classList.toggle('has-error', empty);
        f.setAttribute('aria-invalid', empty ? 'true' : 'false');
        if (err) {
          err.hidden = !empty;
          if (empty && !err.textContent) err.textContent = f.type === 'checkbox' ? 'Потрібна ваша згода.' : 'Заповніть це поле.';
          if (empty) f.setAttribute('aria-describedby', err.id);
        }
        if (empty && !bad) bad = f;
      });
      if (bad) { e.preventDefault(); bad.focus(); return; }
      var btn = form.querySelector('[type=submit]');
      if (btn) { btn.disabled = true; btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true'); }
    });
  });
})();

/* Bottom bar vs third-party floating buttons (Unitalk call button etc.):
   lift any fixed element that would sit on top of the bar. */
(function () {
  var bar = document.querySelector('.pmx-bnav');
  if (!bar) return;
  function lift() {
    if (getComputedStyle(bar).display === 'none') return;
    var top = bar.getBoundingClientRect().top;
    var all = document.body.querySelectorAll('body > *:not(.pmx-bnav):not(.pmx-drawer):not(script):not(style), body > * > *:not(script), #unitalk-root *, #divForNextelButton2 *');
    for (var i = 0; i < all.length; i++) {
      var el = all[i];
      if (el.closest('.pmx-bnav, .pmx-drawer, .pmx-header, .pmx-filters, .wp-site-blocks, #wpadminbar')) continue;
      var cs = getComputedStyle(el);
      if (cs.position !== 'fixed') continue;
      var r = el.getBoundingClientRect();
      if (!r.width || !r.height || r.height > window.innerHeight / 2) continue;
      var cur = parseFloat(el.dataset.pmxLift || '0');
      var overlap = r.bottom + cur - top + 12;
      if (overlap > 0 && Math.abs(overlap - cur) > 1) {
        el.dataset.pmxLift = overlap;
        el.style.setProperty('translate', '0 ' + (-overlap) + 'px', 'important');
      }
    }
  }
  var t;
  function soon() { clearTimeout(t); t = setTimeout(lift, 150); }
  window.addEventListener('load', function () { lift(); setTimeout(lift, 1500); setTimeout(lift, 4000); });
  window.addEventListener('resize', soon);
  if (window.MutationObserver) {
    var mo = new MutationObserver(function () {
      soon();
      var root = document.getElementById('unitalk-root');
      if (root && !root.dataset.pmxWatched) { root.dataset.pmxWatched = '1'; new MutationObserver(soon).observe(root, { childList: true, subtree: true }); }
    });
    mo.observe(document.body, { childList: true });
  }
})();

/* "Купити в один клік": take the chosen size from the cart form, ask name + phone, create the order. */
(function () {
  var dlg = document.getElementById('pmx-oneclick-dialog');
  if (!dlg) return;
  var f = dlg.querySelector('[data-pmx-oneclick-form]');
  var done = dlg.querySelector('[data-pmx-oneclick-done]');
  var err = f.querySelector('.pmx-oneclick-form__err');
  dlg.addEventListener('click', function (e) { if (e.target === dlg && dlg.close) dlg.close(); });
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-pmx-oneclick]');
    if (!b) return;
    e.preventDefault();
    var cart = b.closest('form.cart');
    if (!cart) return;
    var need = cart.querySelector('.pmx-oneclick__need');
    var vInput = cart.querySelector('input[name=variation_id]');
    var isVar = cart.classList.contains('variations_form');
    var vid = vInput ? parseInt(vInput.value, 10) || 0 : 0;
    if (isVar && !vid) {
      if (need) need.hidden = false;
      var tiles = cart.querySelector('.pmx-tiles, table.variations');
      if (tiles) tiles.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    if (need) need.hidden = true;
    var pid = cart.getAttribute('data-product_id') || (cart.querySelector('[name=add-to-cart]') || {}).value || '';
    var qty = cart.querySelector('input.qty');
    f.product_id.value = pid;
    f.variation_id.value = vid || '';
    f.quantity.value = qty ? qty.value : 1;
    var title = document.querySelector('.product_title');
    var size = [];
    cart.querySelectorAll('table.variations select').forEach(function (s) { if (s.value) size.push(s.options[s.selectedIndex].textContent); });
    var what = dlg.querySelector('[data-pmx-oneclick-what]');
    if (what) what.textContent = (title ? title.textContent.trim() : '') + (size.length ? ', розмір ' + size.join(', ') : '') + ', ' + f.quantity.value + ' шт.';
    f.hidden = false; done.hidden = true; err.hidden = true;
    if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', '');
    setTimeout(function () { f.phone.focus(); }, 50);
  });
  /* Size picked later: hide the hint. */
  document.addEventListener('change', function (e) {
    var cart = e.target.closest && e.target.closest('form.cart');
    var need = cart && cart.querySelector('.pmx-oneclick__need');
    if (need) need.hidden = true;
  });
  f.addEventListener('submit', function (e) {
    e.preventDefault();
    var digits = (f.phone.value || '').replace(/\D+/g, '');
    if (digits.length < 10) { err.textContent = 'Вкажіть номер телефону, наприклад +380 63 100 86 86.'; err.hidden = false; f.phone.focus(); return; }
    var go = f.querySelector('[type=submit]');
    go.disabled = true; go.classList.add('is-loading');
    fetch(f.getAttribute('action'), { method: 'POST', body: new FormData(f), credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { success: false, data: {} }; }); })
      .then(function (res) {
        var msg = res && res.data && res.data.message;
        if (!res || res.success === undefined) msg = 'Не вдалося надіслати (помилка сервера). Зателефонуйте нам, будь ласка.';
        if (res && res.success) {
          f.hidden = true; done.textContent = msg || 'Дякуємо! Ми зателефонуємо вам.'; done.hidden = false;
        } else {
          err.textContent = msg || 'Не вдалося надіслати. Зателефонуйте нам, будь ласка.'; err.hidden = false;
        }
      })
      .catch(function () { err.textContent = 'Немає з’єднання. Спробуйте ще раз або зателефонуйте нам.'; err.hidden = false; })
      .then(function () { go.disabled = false; go.classList.remove('is-loading'); });
  });
})();

/* "Додати в кошик" on the product page: add by AJAX and open the mini-cart panel on the right. */
(function () {
  var dlg = document.getElementById('pmx-minicart');
  if (!dlg || !window.fetch || !window.FormData) return;
  var url = dlg.getAttribute('data-ajax');
  function close() { if (dlg.open) { dlg.classList.add('is-closing'); setTimeout(function () { dlg.classList.remove('is-closing'); if (dlg.close) dlg.close(); else dlg.removeAttribute('open'); }, 200); } }
  dlg.addEventListener('click', function (e) { if (e.target === dlg) close(); });
  dlg.addEventListener('cancel', function (e) { e.preventDefault(); close(); });
  dlg.querySelectorAll('form[method=dialog]').forEach(function (f) { f.addEventListener('submit', function (e) { e.preventDefault(); close(); }); });
  document.addEventListener('change', function (e) {
    var err = e.target.closest && e.target.closest('form.cart') && e.target.closest('form.cart').querySelector('.pmx-atc-err');
    if (err) err.hidden = true;
  });

  function applyFragments(fr) {
    Object.keys(fr || {}).forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        var t = document.createElement('template');
        t.innerHTML = String(fr[sel]).trim();
        if (t.content.firstElementChild) el.replaceWith(t.content.firstElementChild.cloneNode(true));
      });
    });
  }

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches || !form.matches('form.cart') || !form.closest('.product')) return;
    if (form.closest('.grouped_form') || form.querySelector('input[name^="quantity["]')) return;
    var btn = form.querySelector('button.single_add_to_cart_button');
    if (!btn || btn.classList.contains('disabled')) return;
    var pid = (form.querySelector('[name=add-to-cart]') || {}).value || form.getAttribute('data-product_id');
    if (!pid) return;
    e.preventDefault();
    if (btn.classList.contains('is-loading')) return;
    var fd = new FormData(form);
    fd.delete('add-to-cart');
    if (!fd.get('product_id')) fd.set('product_id', pid);
    fd.set('action', 'pmx_add_to_cart');
    btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true');
    var errEl = form.querySelector('.pmx-atc-err');
    if (!errEl) { errEl = document.createElement('p'); errEl.className = 'pmx-atc-err'; errEl.setAttribute('role', 'alert'); errEl.hidden = true; btn.insertAdjacentElement('afterend', errEl); }
    errEl.hidden = true;
    function fail(msg) { errEl.textContent = msg || 'Не вдалося додати товар. Оновіть сторінку і спробуйте ще раз.'; errEl.hidden = false; }
    fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) {
        return r.json().then(function (res) {
          if (!res || !res.success) { fail(res && res.data && res.data.message); return; }
          var body = dlg.querySelector('[data-pmx-minicart-body]');
          if (body && res.data.body) { var t = document.createElement('template'); t.innerHTML = res.data.body.trim(); body.replaceWith(t.content.firstElementChild); }
          applyFragments(res.data.fragments);
          if (window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');
          if (dlg.showModal) { if (!dlg.open) dlg.showModal(); } else dlg.setAttribute('open', '');
          var go = dlg.querySelector('.pmx-minicart__go'); if (go) go.focus();
        }, function () { fail(); });
      }, function () {
        /* No connection to the AJAX endpoint: post the form the usual way. */
        var h = document.createElement('input'); h.type = 'hidden'; h.name = 'add-to-cart'; h.value = pid;
        if (!form.querySelector('input[name=add-to-cart]')) form.appendChild(h);
        form.submit();
      })
      .then(function () { btn.classList.remove('is-loading'); btn.removeAttribute('aria-busy'); });
  });
})();
