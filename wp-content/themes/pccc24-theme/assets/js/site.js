// PCCC24 iOS Handoff: menu, segmented control, slider translateX, image fallback, quickview + rossark-motion-skill engine.
(function () {
  var CFG = window.PCCC24_CONFIG || {};
  var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; };

  // =========================================================================
  // 1. ROSSARK-MOTION-SKILL ENGINE (Web / SVG / Vanilla JS)
  // Curves, track, flow (monotone cubic Hermite), seg, wave, loop, bell, seeded
  // =========================================================================
  function cubicBezier(x1, y1, x2, y2) {
    var cx = 3 * x1, bx = 3 * (x2 - x1) - cx, ax = 1 - cx - bx;
    var cy = 3 * y1, by = 3 * (y2 - y1) - cy, ay = 1 - cy - by;
    function sampleX(t) { return ((ax * t + bx) * t + cx) * t; }
    function sampleY(t) { return ((ay * t + by) * t + cy) * t; }
    function sampleDerivX(t) { return (3 * ax * t + 2 * bx) * t + cx; }
    return function (x) {
      if (x <= 0) return 0;
      if (x >= 1) return 1;
      var t = x;
      for (var i = 0; i < 6; i++) {
        var err = sampleX(t) - x;
        if (Math.abs(err) < 1e-5) break;
        var d = sampleDerivX(t);
        if (Math.abs(d) < 1e-5) break;
        t -= err / d;
      }
      return sampleY(Math.min(1, Math.max(0, t)));
    };
  }

  var EASE_IN_OUT = cubicBezier(0.77, 0, 0.175, 1);
  var EASE_OUT    = cubicBezier(0.23, 1, 0.32, 1);

  function wave(t, period, phase) {
    return Math.sin((t / period + (phase || 0)) * Math.PI * 2);
  }
  function loop(t, period, phase) {
    return ((t / period + (phase || 0)) % 1 + 1) % 1;
  }
  function bell(p) {
    return Math.sin(Math.PI * Math.min(1, Math.max(0, p)));
  }
  function seg(t, a, b, curve) {
    var p = Math.min(1, Math.max(0, (t - a) / (b - a)));
    return (curve || EASE_IN_OUT)(p);
  }
  function seeded(i, salt) {
    var x = Math.sin((i + 1) * 9301 + (salt || 0) * 49297) * 49297;
    return x - Math.floor(x);
  }
  function track(t, keys) {
    if (t <= keys[0][0]) return keys[0][1];
    for (var i = 0; i < keys.length - 1; i++) {
      var t0 = keys[i][0], v0 = keys[i][1], mode = keys[i][2];
      var t1 = keys[i + 1][0], v1 = keys[i + 1][1];
      if (t <= t1) {
        if (t1 === t0 || v1 === v0) return v0;
        var fn = mode === 'out' ? EASE_OUT : EASE_IN_OUT;
        return v0 + (v1 - v0) * fn((t - t0) / (t1 - t0));
      }
    }
    return keys[keys.length - 1][1];
  }
  function flow(t, keys) {
    var n = keys.length;
    if (t <= keys[0][0]) return keys[0][1];
    if (t >= keys[n - 1][0]) return keys[n - 1][1];
    var dx = [], dy = [], m = [];
    for (var i = 0; i < n - 1; i++) {
      dx[i] = keys[i + 1][0] - keys[i][0];
      dy[i] = keys[i + 1][1] - keys[i][1];
      m[i]  = dx[i] > 0 ? dy[i] / dx[i] : 0;
    }
    var tg = [m[0]];
    for (var j = 1; j < n - 1; j++) {
      tg[j] = m[j - 1] * m[j] <= 0 ? 0 : (m[j - 1] + m[j]) / 2;
    }
    tg[n - 1] = m[n - 2];
    for (var k = 0; k < n - 1; k++) {
      if (m[k] === 0) { tg[k] = 0; tg[k + 1] = 0; continue; }
      var a = tg[k] / m[k], b = tg[k + 1] / m[k], s = a * a + b * b;
      if (s > 9) { var tau = 3 / Math.sqrt(s); tg[k] = tau * a * m[k]; tg[k + 1] = tau * b * m[k]; }
    }
    var idx = 0;
    while (idx < n - 2 && t > keys[idx + 1][0]) idx++;
    var h = dx[idx];
    if (h <= 0) return keys[idx][1];
    var u = (t - keys[idx][0]) / h, u2 = u * u, u3 = u2 * u;
    return (2 * u3 - 3 * u2 + 1) * keys[idx][1]
      + (u3 - 2 * u2 + u) * h * tg[idx]
      + (-2 * u3 + 3 * u2) * keys[idx + 1][1]
      + (u3 - u2) * h * tg[idx + 1];
  }

  window.PCCC24_MOTION = { EASE_IN_OUT: EASE_IN_OUT, EASE_OUT: EASE_OUT, wave: wave, loop: loop, bell: bell, seg: seg, seeded: seeded, track: track, flow: flow };

  // Khởi tạo chuyển động cho các thẻ kiểm định [data-motion-tag]
  var tags = [].slice.call(document.querySelectorAll('[data-motion-tag]'));
  if (tags.length) {
    var reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    var IDLE_DURATION = 6500;
    var scenes = tags.map(function (el, idx) {
      return {
        el: el,
        rig: el.querySelector('.tag-rig'),
        seed: seeded(idx, 7),
        visible: true,
        awake: true,
        elapsed: 0,
        idleElapsed: 0,
        wakeCount: 0,
        lastTick: 0,
        glow: el.querySelector('.rg-glow'),
        needle: el.querySelector('.rg-needle'),
        lever: el.querySelector('.rg-lever'),
        pin: el.querySelector('.rg-pin'),
        led: el.querySelector('.rg-led'),
        wisps: [].slice.call(el.querySelectorAll('.rg-wisp'))
      };
    });

    function renderScene(sc, loopTime, oneShotTime, env) {
      if (sc.needle) {
        var needleSweep = (1 - seg(oneShotTime, 60, 1100, EASE_OUT)) * -54;
        var needleIdle  = wave(loopTime, 3600, sc.seed + 0.15) * 1.8 * env;
        sc.needle.style.transform = 'rotate(' + (needleSweep + needleIdle).toFixed(2) + 'deg)';
      }
      if (sc.lever) {
        var leverPress = (1 - seg(oneShotTime, 120, 900, EASE_OUT)) * -4;
        var leverIdle  = wave(loopTime, 4800, sc.seed + 0.22) * 1.25 * env;
        sc.lever.style.transform = 'rotate(' + (leverPress + leverIdle).toFixed(2) + 'deg)';
      }
      if (sc.pin) {
        var pinIdle = wave(loopTime, 4800, sc.seed + 0.38) * 2.4 * env;
        sc.pin.style.transform = 'rotate(' + pinIdle.toFixed(2) + 'deg)';
      }
      if (sc.glow) {
        var glowScale = 1 + wave(loopTime, 3200, sc.seed) * 0.04 * env;
        sc.glow.style.transform = 'scale(' + glowScale.toFixed(3) + ')';
      }
      if (sc.led) {
        var ledScale = 1 + wave(loopTime, 2800, sc.seed + 0.4) * 0.05 * env;
        sc.led.style.transform = 'scale(' + ledScale.toFixed(3) + ')';
      }
      for (var i = 0; i < sc.wisps.length; i++) {
        if (reducedMotion) {
          sc.wisps[i].style.opacity = '0.28';
          continue;
        }
        var period = 3200 + seeded(i, 1) * 1100;
        var phase  = seeded(i, 2) + sc.seed;
        var p      = loop(loopTime, period, phase);
        var dx     = (9 + seeded(i, 3) * 8) * p;
        var dy     = (-3.5 - seeded(i, 4) * 4.5) * p + wave(loopTime, 2400, phase) * 0.8;
        var scWisp = 0.75 + p * 0.45;
        sc.wisps[i].style.opacity   = (bell(p) * 0.48 * env).toFixed(3);
        sc.wisps[i].style.transform = 'translate(' + dx.toFixed(2) + 'px,' + dy.toFixed(2) + 'px) scale(' + scWisp.toFixed(2) + ')';
      }
    }

    if (reducedMotion) {
      scenes.forEach(function (sc) { renderScene(sc, 0, 2000, 0); });
    } else {
      var rafId = 0;
      function updateClockState() {
        var anyActive = !document.hidden && scenes.some(function (s) { return s.visible && s.awake; });
        if (anyActive && !rafId) {
          var now = performance.now();
          scenes.forEach(function (s) { if (s.visible && s.awake) s.lastTick = now; });
          rafId = requestAnimationFrame(tick);
        } else if (!anyActive && rafId) {
          cancelAnimationFrame(rafId);
          rafId = 0;
        }
      }
      function wakeScene(sc) {
        if (!sc.awake) {
          sc.awake = true;
          sc.idleElapsed = 0;
          sc.wakeCount++;
          sc.lastTick = performance.now();
          updateClockState();
        } else if (sc.idleElapsed > 1500) {
          sc.idleElapsed = 450;
        }
      }
      function tick(now) {
        rafId = 0;
        for (var i = 0; i < scenes.length; i++) {
          var sc = scenes[i];
          if (!sc.visible || !sc.awake) continue;
          var dt = sc.lastTick ? Math.min(64, now - sc.lastTick) : 16;
          sc.lastTick = now;
          sc.elapsed += dt;
          sc.idleElapsed += dt;
          var fadeIn  = sc.wakeCount > 0 ? seg(sc.idleElapsed, 0, 450, EASE_OUT) : 1;
          var fadeOut = 1 - seg(sc.idleElapsed, IDLE_DURATION - 1500, IDLE_DURATION, EASE_IN_OUT);
          var env     = fadeIn * fadeOut;
          renderScene(sc, sc.elapsed, sc.elapsed, env);
          if (sc.idleElapsed >= IDLE_DURATION && sc.elapsed >= 1450) {
            renderScene(sc, sc.elapsed, 2000, 0);
            sc.awake = false;
          }
        }
        updateClockState();
      }
      scenes.forEach(function (sc) {
        sc.el.addEventListener('pointerenter', function () { wakeScene(sc); });
        sc.el.addEventListener('focusin', function () { wakeScene(sc); });
        sc.el.addEventListener('touchstart', function () { wakeScene(sc); }, { passive: true });
      });
      if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            scenes.forEach(function (sc) {
              if (sc.el === entry.target) {
                sc.visible = entry.isIntersecting;
                if (sc.visible && sc.awake) sc.lastTick = performance.now();
              }
            });
          });
          updateClockState();
        }, { threshold: 0.05 });
        scenes.forEach(function (sc) { io.observe(sc.el); });
      }
      document.addEventListener('visibilitychange', updateClockState);
      updateClockState();
    }
  }

  // =========================================================================
  // 2. IMAGE FALLBACK (Section 12: striped industrial placeholder on error)
  // =========================================================================
  function bindImageFallbacks() {
    document.querySelectorAll('img[data-fb]').forEach(function (img) {
      if (img.dataset.fbBound) return;
      img.dataset.fbBound = '1';
      function onErr() { img.classList.add('is-broken'); }
      img.addEventListener('error', onErr);
      if (img.complete && img.naturalWidth === 0) onErr();
    });
  }
  bindImageFallbacks();

  // =========================================================================
  // 3. GIAO DIỆN TƯƠNG TÁC (Menu, Dropdown, Segmented Control, Slider, Quickview)
  // =========================================================================
  document.addEventListener('click', function (e) {
    var open = e.target.closest('[data-open]');
    if (open) document.getElementById(open.dataset.open).showModal();
    var close = e.target.closest('[data-close]');
    if (close) close.closest('dialog').close();
    if (e.target.tagName === 'DIALOG') e.target.close();
    var quick = e.target.closest('[data-quick]');
    if (quick) quickView(quick.dataset.quick);

    // iOS Segmented Control: chuyển tab "Sản phẩm nổi bật" không tải lại trang
    var segBtn = e.target.closest('[data-seg-tab]');
    if (segBtn) {
      var targetId = segBtn.dataset.segTab;
      var group = segBtn.closest('.seg-control');
      if (group) {
        group.querySelectorAll('.seg-btn').forEach(function (b) {
          var active = b === segBtn;
          b.classList.toggle('is-active', active);
          b.setAttribute('aria-selected', active ? 'true' : 'false');
        });
      }
      document.querySelectorAll('.featured-pane').forEach(function (pane) {
        if (pane.id === targetId) {
          pane.removeAttribute('hidden');
        } else {
          pane.setAttribute('hidden', '');
        }
      });
      var moreLink  = document.getElementById('featured-more-link');
      var moreCount = document.getElementById('featured-more-count');
      if (moreLink && segBtn.dataset.moreUrl) moreLink.href = segBtn.dataset.moreUrl;
      if (moreCount && segBtn.dataset.moreCount) moreCount.textContent = segBtn.dataset.moreCount;
    }
  });

  // Đóng dropdown "Sản phẩm" khi bấm ra ngoài.
  document.addEventListener('click', function (e) {
    document.querySelectorAll('details.nav-cats[open]').forEach(function (d) {
      if (!d.contains(e.target)) d.open = false;
    });
  });

  // Slider Hero: translateX(-N*100%) + chấm điều hướng kính mờ
  var slides = document.querySelector('.slides');
  if (slides && slides.children.length > 1) {
    var dots = document.querySelector('.slide-dots');
    var n = slides.children.length, cur = 0;
    for (var i = 0; i < n; i++) {
      dots.insertAdjacentHTML('beforeend', '<button type="button" aria-label="Ảnh ' + (i + 1) + '"></button>');
    }
    var mark = function () {
      slides.style.transform = 'translateX(-' + (cur * 100) + '%)';
      [].forEach.call(dots.children, function (b, idx) {
        b.setAttribute('aria-current', idx === cur ? 'true' : 'false');
      });
    };
    var go = function (idx) {
      cur = ((idx % n) + n) % n;
      mark();
    };
    dots.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      var idx = [].indexOf.call(dots.children, btn);
      if (idx >= 0) go(idx);
    });
    mark();
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
      var timer = setInterval(function () { go(cur + 1); }, 5000);
      slides.parentElement.addEventListener('pointerdown', function () { clearInterval(timer); }, { once: true });
    }
  }

  function quickView(id) {
    var dlg = document.getElementById('quickview');
    var box = dlg.querySelector('.quickview');
    box.innerHTML = '<p>Đang tải…</p>';
    dlg.showModal();
    fetch(CFG.restUrl + '/products/' + encodeURIComponent(id))
      .then(function (r) { if (!r.ok) throw r; return r.json(); })
      .then(function (p) {
        var specs = (p.specs || []).slice(0, 8).map(function (s) {
          return '<tr><th>' + esc(s.name) + '</th><td>' + esc(s.content) + '</td></tr>';
        }).join('');
        var tel = document.querySelector('.float-contact .btn-call');
        box.innerHTML =
          '<div class="main-img"><img src="' + esc(p.image_url || CFG.noImageUrl) + '" alt="' + esc(p.name) + '"></div>' +
          '<div><h2>' + esc(p.name) + '</h2>' +
          '<p class="product-price">' + (p.price > 0 ? Number(p.price).toLocaleString('vi-VN') + 'đ' : 'Giá: liên hệ') + '</p>' +
          (specs ? '<table class="specs">' + specs + '</table>' : '') +
          '<div class="actions"><a class="btn btn-line" href="' + esc(p.url) + '">Xem chi tiết</a>' +
          (tel ? '<a class="btn btn-call" href="' + tel.getAttribute('href') + '">Gọi báo giá</a>' : '') + '</div></div>';
      })
      .catch(function () { box.innerHTML = '<p>Không tải được sản phẩm. Thử mở trang chi tiết hoặc gọi để được báo giá.</p>'; });
  }
})();
