/* Laura Rivera · JS del sitio (sin dependencias, sin build). */
(function () {
    'use strict';

    var CONSENT_COOKIE = 'lr_consent';

    function readCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : null;
    }

    function writeCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 864e5);
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }

    // API pública mínima para otros scripts (p. ej. métricas)
    window.LR = window.LR || {};
    window.LR.consent = function () { return readCookie(CONSENT_COOKIE); };

    // ---- Menú móvil ----
    function initNav() {
        var toggle = document.querySelector('[data-nav-toggle]');
        var nav = document.querySelector('[data-nav]');
        if (!toggle || !nav) return;
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ---- Pestañas accesibles ----
    function initTabs() {
        document.querySelectorAll('[data-tabs]').forEach(function (root) {
            var tabs = Array.prototype.slice.call(root.querySelectorAll('[data-tab]'));
            var panels = root.querySelectorAll('[data-panel]');

            function select(id, focus) {
                tabs.forEach(function (t) {
                    var on = t.getAttribute('data-tab') === id;
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                    t.setAttribute('tabindex', on ? '0' : '-1');
                    if (on && focus) t.focus();
                });
                panels.forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== id; });
            }

            tabs.forEach(function (t, i) {
                t.addEventListener('click', function () { select(t.getAttribute('data-tab'), false); });
                t.addEventListener('keydown', function (e) {
                    var next = null;
                    if (e.key === 'ArrowRight') next = tabs[(i + 1) % tabs.length];
                    if (e.key === 'ArrowLeft') next = tabs[(i - 1 + tabs.length) % tabs.length];
                    if (e.key === 'Home') next = tabs[0];
                    if (e.key === 'End') next = tabs[tabs.length - 1];
                    if (next) { e.preventDefault(); select(next.getAttribute('data-tab'), true); }
                });
            });
        });
    }

    // ---- Aviso de cookies ----
    function initCookies() {
        var banner = document.querySelector('[data-cookie-banner]');
        if (!banner) return;
        if (!readCookie(CONSENT_COOKIE)) banner.hidden = false;

        banner.querySelectorAll('[data-cookie-choice]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var choice = btn.getAttribute('data-cookie-choice');
                writeCookie(CONSENT_COOKIE, choice, 180);
                banner.hidden = true;
                document.dispatchEvent(new CustomEvent('lr:consent', { detail: choice }));
            });
        });

        document.querySelectorAll('[data-cookie-reopen]').forEach(function (btn) {
            btn.addEventListener('click', function () { banner.hidden = false; });
        });
    }

    // ---- Métricas de clics, desplazamiento y tiempo (solo con consentimiento "all") ----
    var metricsOn = false;
    function initMetrics() {
        if (metricsOn || window.LR.consent() !== 'all' || !navigator.sendBeacon) return;
        var meta = document.querySelector('meta[name="lr-metrics"]');
        if (!meta) return;
        metricsOn = true;
        var endpoint = meta.getAttribute('content');
        var path = location.pathname;

        // Identificador anónimo del visitante (cookie de medición, 13 meses)
        if (!readCookie('lr_vid')) {
            var id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID()
                : 'v' + Date.now().toString(36) + Math.random().toString(36).slice(2, 12);
            writeCookie('lr_vid', id, 395);
        }

        function send(events) {
            try {
                navigator.sendBeacon(endpoint, new Blob([JSON.stringify({ events: events })], { type: 'application/json' }));
            } catch (e) { /* las métricas nunca deben romper el sitio */ }
        }

        document.addEventListener('click', function (e) {
            var el = e.target.closest('a, button, [data-track], summary, [role="tab"]');
            if (!el || el.closest('[data-cookie-banner]')) return;
            var href = el.getAttribute('href') || '';
            var type = 'click';
            if (/^tel:/i.test(href)) type = 'tel';
            else if (/^mailto:/i.test(href)) type = 'mailto';
            else if (/wa\.me|whatsapp\.com/i.test(href)) type = 'whatsapp';
            else if (/\.(pdf|docx?|xlsx?|zip)(\?|$)/i.test(href)) type = 'download';
            else if (el.host && el.host !== location.host) type = 'outbound';
            var label = el.getAttribute('data-track') || el.getAttribute('aria-label') || (el.textContent || '').replace(/\s+/g, ' ').trim();
            send([{ type: type, label: label.slice(0, 150), target: href.slice(0, 500), path: path }]);
        }, { capture: true, passive: true });

        // Profundidad de desplazamiento: 25/50/75/100 %, una vez por página
        var marks = [25, 50, 75, 100], sent = {};
        function onScroll() {
            var h = document.documentElement;
            var max = h.scrollHeight - window.innerHeight;
            var pct = max <= 0 ? 100 : Math.round((window.scrollY / max) * 100);
            marks.forEach(function (m) {
                if (pct >= m && !sent[m]) { sent[m] = true; send([{ type: 'scroll', label: m + '%', path: path, value: m }]); }
            });
        }
        window.addEventListener('scroll', onScroll, { passive: true });

        // Tiempo activo en la página, al salir u ocultar la pestaña
        var start = Date.now(), active = 0;
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                active += Date.now() - start;
                var secs = Math.round(active / 1000);
                if (secs > 0) send([{ type: 'engagement', label: 'segundos', path: path, value: Math.min(secs, 86400) }]);
                active = 0;
            } else {
                start = Date.now();
            }
        });
    }
    document.addEventListener('lr:consent', initMetrics);

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initNav();
        initTabs();
        initCookies();
        initMetrics();
    });
})();
