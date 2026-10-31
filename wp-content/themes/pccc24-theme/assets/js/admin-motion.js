/* PCCC24 WP-Admin — Dashboard V18 Motion Engine (rossark-motion-skill) */
(function () {
  'use strict';

  /* ---------- 1. Primitives & Cubic-Bezier Solver (rossark-motion-skill) ---------- */
  function bezier(x1, y1, x2, y2) {
    return function (x) {
      if (x <= 0) return 0;
      if (x >= 1) return 1;
      var t = x;
      for (var i = 0; i < 5; i++) {
        var inv = 1 - t;
        var curX = 3 * inv * inv * t * x1 + 3 * inv * t * t * x2 + t * t * t - x;
        var slope = 3 * inv * inv * x1 + 6 * inv * t * (x2 - x1) + 3 * t * t * (1 - x2);
        if (Math.abs(slope) < 1e-6) break;
        t -= curX / slope;
      }
      var u = 1 - t;
      return 3 * u * u * t * y1 + 3 * u * t * t * y2 + t * t * t;
    };
  }

  var EASE_IN_OUT = bezier(0.77, 0, 0.175, 1);
  var EASE_OUT = bezier(0.23, 1, 0.32, 1);

  function clamp(v, lo, hi) { return Math.min(hi, Math.max(lo, v)); }
  function lerp(a, b, t) { return a + (b - a) * t; }
  function wave(t, period, phase) {
    return Math.sin(((t / period) + (phase || 0)) * Math.PI * 2);
  }
  function loop(t, period, offset) {
    return (((t + (offset || 0)) % period) + period) % period / period;
  }
  function bell(p) { return Math.sin(clamp(p, 0, 1) * Math.PI); }
  function seg(t, s, e, ease) {
    if (t <= s) return 0;
    if (t >= e) return 1;
    var p = (t - s) / (e - s);
    return ease ? ease(p) : p;
  }
  function seeded(i) {
    var x = Math.sin((i + 1) * 127.1 + 311.7) * 43758.5453;
    return x - Math.floor(x);
  }
  function track(p, stops, ease) {
    if (!stops || !stops.length) return 0;
    if (p <= stops[0][0]) return stops[0][1];
    for (var i = 0; i < stops.length - 1; i++) {
      var a = stops[i], b = stops[i + 1];
      if (p <= b[0]) {
        var fn = b[2] || ease || EASE_IN_OUT;
        var local = (p - a[0]) / Math.max(1e-6, b[0] - a[0]);
        return lerp(a[1], b[1], fn(clamp(local, 0, 1)));
      }
    }
    return stops[stops.length - 1][1];
  }
  function flow(t, stops, ease) {
    if (!stops || !stops.length) return 0;
    var end = stops[stops.length - 1][0] || 1;
    var norm = stops.map(function (s) { return [s[0] / end, s[1], s[2]]; });
    return track(clamp(t / end, 0, 1), norm, ease);
  }

  window.PCCC24_ADMIN_MOTION = {
    bezier: bezier,
    EASE_IN_OUT: EASE_IN_OUT,
    EASE_OUT: EASE_OUT,
    clamp: clamp,
    lerp: lerp,
    wave: wave,
    loop: loop,
    bell: bell,
    seg: seg,
    seeded: seeded,
    track: track,
    flow: flow
  };

  function initAdminMotion() {
    var reducedQuery = window.matchMedia
      ? window.matchMedia('(prefers-reduced-motion: reduce)')
      : { matches: false };

    /* ---------- 2. Staggered Bento Cards (--stagger-i) ---------- */
    var cards = document.querySelectorAll('.v18-bento-wrap > *, .v18-bento-grid > *');
    cards.forEach(function (card, idx) {
      card.style.setProperty('--stagger-i', String(idx));
    });

    /* ---------- 3. Traveling White Pill on Static Admin Dock (.v18-nav) ---------- */
    var v18Nav = document.querySelector('.v18-nav');
    var v18Dock = document.getElementById('v18-dock');
    if (v18Nav) {
      var navItems = Array.from(v18Nav.querySelectorAll('li[data-tab]'));
      var pill = v18Nav.querySelector('.v18-nav-pill');
      if (!pill && navItems.length) {
        pill = document.createElement('span');
        pill.className = 'v18-nav-pill';
        pill.setAttribute('aria-hidden', 'true');
        v18Nav.insertBefore(pill, v18Nav.firstChild);
        v18Nav.classList.add('has-pill');
      }
      var syncNavPill = function () {
        var active = v18Nav.querySelector('li.active') || navItems[0];
        if (!pill || !active) return;
        pill.style.transform = 'translate3d(' + active.offsetLeft + 'px, ' + active.offsetTop + 'px, 0)';
        pill.style.width = active.offsetWidth + 'px';
        pill.style.height = active.offsetHeight + 'px';
      };
      requestAnimationFrame(syncNavPill);
      window.addEventListener('resize', syncNavPill, { passive: true });
      navItems.forEach(function (li) {
        li.addEventListener('click', function () {
          requestAnimationFrame(syncNavPill);
          var pane = document.getElementById('tab-' + li.dataset.tab);
          if (pane && !reducedQuery.matches) {
            pane.classList.remove('v18-pane-enter');
            void pane.offsetWidth;
            pane.classList.add('v18-pane-enter');
          }
        });
      });
      if (v18Dock) {
        var toggleBtn = document.getElementById('v18-toggle');
        if (toggleBtn) {
          toggleBtn.addEventListener('click', function () {
            setTimeout(syncNavPill, 230);
          });
        }
      }
    }

    /* ---------- 4. Living V18 Telemetry & Fire-Safety Rig ([data-v18-motion]) ---------- */
    var heroCard = document.querySelector('[data-v18-motion]') || document.querySelector('.v18-hero-card');
    if (!heroCard) return;

    var needleEl = heroCard.querySelector('.v18-needle');
    var glowEl   = heroCard.querySelector('.v18-glow');
    var leverEl  = heroCard.querySelector('.v18-lever');
    var pinEl    = heroCard.querySelector('.v18-pin');
    var ledEl    = heroCard.querySelector('.v18-led');
    var wisps    = Array.from(heroCard.querySelectorAll('.v18-wisp'));
    var bars     = Array.from(heroCard.querySelectorAll('.v18-bar'));

    var inView = true;
    var rafId = 0;
    var lastFrame = 0;
    var elapsed = 0;
    var oneShotTime = 0;
    var activeUntil = 6800;

    function applyRestState() {
      if (needleEl) needleEl.style.transform = 'rotate(0deg)';
      if (glowEl) {
        glowEl.style.transform = 'scale(1)';
        glowEl.style.opacity = '0.5';
      }
      if (leverEl) leverEl.style.transform = 'rotate(0deg)';
      if (pinEl) pinEl.style.transform = 'translate3d(0,0,0) rotate(0deg)';
      if (ledEl) {
        ledEl.style.transform = 'scale(1)';
        ledEl.style.opacity = '0.9';
      }
      wisps.forEach(function (w) {
        w.style.opacity = '0';
        w.style.transform = 'translate3d(0,0,0) scale(0.94)';
      });
      bars.forEach(function (b) {
        b.style.transform = 'scaleY(1)';
        b.style.opacity = '0.92';
      });
    }

    function step(now) {
      rafId = 0;
      if (reducedQuery.matches || document.hidden || !inView) {
        lastFrame = 0;
        if (reducedQuery.matches) applyRestState();
        return;
      }

      var dt = lastFrame ? Math.min(48, now - lastFrame) : 16;
      lastFrame = now;
      elapsed += dt;
      oneShotTime += dt;

      /* 1. Gauge Needle: One-shot calibration (-50deg -> 0deg) + ±1.5deg breathing */
      var needleIntro = (1 - seg(oneShotTime, 80, 1180, EASE_OUT)) * -50;
      var needleIdle = wave(elapsed, 3100, 0.12) * 1.5 + wave(elapsed, 1550, 0.38) * 0.4;
      var needleDeg = needleIntro + needleIdle;
      if (needleEl) {
        needleEl.style.transform = 'rotate(' + needleDeg.toFixed(2) + 'deg)';
      }

      /* 2. Pressure Glow & Specular Reflection on .v18-hero-card */
      var glowPulse = 0.52 + 0.18 * wave(elapsed, 2900, 0);
      var glowScale = 1 + 0.045 * wave(elapsed, 2900, 0.25);
      if (glowEl) {
        glowEl.style.opacity = glowPulse.toFixed(3);
        glowEl.style.transform = 'scale(' + glowScale.toFixed(3) + ')';
      }
      heroCard.style.setProperty('--gx', (22 + wave(elapsed, 4600, 0.1) * 14).toFixed(1) + '%');
      heroCard.style.setProperty('--gy', (26 + wave(elapsed, 3600, 0.35) * 12).toFixed(1) + '%');

      /* 3. Valve Lever + Safety Pin (phase-delayed overlap & follow-through) */
      var leverDeg = wave(elapsed, 2800, 0.05) * 2.2;
      if (leverEl) {
        leverEl.style.transform = 'rotate(' + leverDeg.toFixed(2) + 'deg)';
      }
      var pinShift = wave(elapsed, 2800, -0.16) * 1.1;
      var pinDeg = wave(elapsed, 2800, -0.22) * 3.0;
      if (pinEl) {
        pinEl.style.transform = 'translate3d(' + pinShift.toFixed(2) + 'px, 0, 0) rotate(' + pinDeg.toFixed(2) + 'deg)';
      }

      /* 4. Mist Wisps: loop + bell + seeded(i) */
      for (var i = 0; i < wisps.length; i++) {
        var period = 2400 + Math.round(seeded(i) * 520);
        var p = loop(elapsed, period, seeded(i + 9) * period);
        var env = bell(p);
        var wx = p * (14 + seeded(i + 3) * 8);
        var wy = -p * (5 + seeded(i + 5) * 5) + wave(elapsed, 1300, seeded(i)) * 1.1;
        var ws = 0.93 + 0.22 * p;
        wisps[i].style.opacity = (env * 0.68).toFixed(3);
        wisps[i].style.transform = 'translate3d(' + wx.toFixed(2) + 'px,' + wy.toFixed(2) + 'px,0) scale(' + ws.toFixed(3) + ')';
      }

      /* 5. Chart Recipe (MOTION.md): 5 Telemetry Bars with 55ms staggered draw-on + subtle idle wave */
      for (var b = 0; b < bars.length; b++) {
        var draw = seg(oneShotTime, 90 + b * 55, 760 + b * 55, EASE_OUT);
        var baseScale = lerp(0.18, 1, draw);
        var breath = draw >= 1 ? wave(elapsed, 2600 + b * 240, b * 0.19) * 0.035 : 0;
        var scaleY = clamp(baseScale + breath, 0.15, 1.06);
        bars[b].style.transform = 'scaleY(' + scaleY.toFixed(3) + ')';
        bars[b].style.opacity = (0.45 + 0.50 * draw).toFixed(3);
      }

      /* 6. Status LED pulse */
      if (ledEl) {
        var ledEnv = 0.72 + 0.26 * wave(elapsed, 2000, 0.2);
        var ledSc  = 1 + 0.07 * wave(elapsed, 2000, 0.2);
        ledEl.style.opacity = ledEnv.toFixed(3);
        ledEl.style.transform = 'scale(' + ledSc.toFixed(3) + ')';
      }

      if (elapsed < activeUntil) {
        schedule();
      } else {
        lastFrame = 0;
      }
    }

    function schedule() {
      if (!rafId && !reducedQuery.matches && !document.hidden && inView) {
        rafId = requestAnimationFrame(step);
      }
    }

    function wake(durationMs, replayIntro) {
      if (reducedQuery.matches) {
        applyRestState();
        return;
      }
      if (replayIntro) oneShotTime = 0;
      activeUntil = Math.max(activeUntil, elapsed + (durationMs || 5200));
      schedule();
    }

    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          inView = entry.isIntersecting;
          if (inView) wake(5200, false);
        });
      }, { threshold: 0.08 });
      io.observe(heroCard);
    }

    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) wake(4500, false);
    });

    heroCard.addEventListener('pointerenter', function () { wake(5500, true); }, { passive: true });
    heroCard.addEventListener('focusin', function () { wake(5500, false); }, { passive: true });
    heroCard.addEventListener('touchstart', function () { wake(5500, true); }, { passive: true });

    if (reducedQuery.matches) {
      applyRestState();
    } else {
      schedule();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminMotion);
  } else {
    initAdminMotion();
  }
})();
