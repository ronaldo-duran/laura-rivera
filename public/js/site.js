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

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initNav();
        initTabs();
        initCookies();
    });
})();
