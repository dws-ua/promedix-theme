/* ProMedix theme JS: menu/search toggles, size tiles on product page, favourite hearts. */
(function () {
  'use strict';

  /* Toggles: mobile menu, search panel. */
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-pmx-toggle]');
    if (!t) return;
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
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[data-pmx-toggle][aria-expanded="true"]').forEach(function (t) { t.click(); });
  });

  /* Favourites: heart on product cards and gallery, remembered in this browser. */
  var KEY = 'pmx-fav';
  function favs() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (err) { return []; } }
  function saveFavs(list) { try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (err) {} }
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
  document.querySelectorAll('ul.products li.product').forEach(function (li) {
    var id = productId(li);
    if (id && !li.querySelector('.pmx-fav')) li.appendChild(heart(id));
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
      function sync() {
        var enabled = {};
        Array.prototype.forEach.call(sel.options, function (o) { if (o.value && !o.disabled) enabled[o.value] = true; });
        wrap.querySelectorAll('button').forEach(function (b) {
          b.setAttribute('aria-pressed', sel.value === b.value ? 'true' : 'false');
          b.disabled = !enabled[b.value];
        });
      }
      sel.addEventListener('change', sync);
      if (window.jQuery) window.jQuery(form).on('woocommerce_update_variation_values reset_data', function () { setTimeout(sync, 0); });
      sync();
    });
    /* "Розмірна сітка ›" link next to the size label. */
    var label = form.querySelector('table.variations th.label');
    var acc = document.getElementById('pmx-acc-sizes');
    if (label && acc && !label.querySelector('.pmx-size-link')) {
      var a = document.createElement('a');
      a.href = '#pmx-acc-sizes';
      a.className = 'pmx-size-link';
      a.textContent = 'Розмірна сітка ›';
      a.addEventListener('click', function () { acc.open = true; });
      label.appendChild(a);
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
