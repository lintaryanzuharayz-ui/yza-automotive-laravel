/* Layout and keyboard behavior only. No business requests, permissions or pricing. */
(() => {
    'use strict';
    document.querySelectorAll('table').forEach(table => {
        if (table.closest('[data-responsive-table]')) return;
        const parent = table.parentElement;
        if (['auto','scroll'].includes(getComputedStyle(parent).overflowX)) {
            parent.tabIndex = parent.tabIndex < 0 ? 0 : parent.tabIndex;
            parent.setAttribute('role', 'region');
            parent.setAttribute('aria-label', table.caption?.textContent || 'Tabel data, geser untuk kolom lainnya');
            return;
        }
        const wrap = document.createElement('div');
        wrap.className = 'yza-responsive-table'; wrap.dataset.responsiveTable = '';
        wrap.tabIndex = 0; wrap.setAttribute('role', 'region');
        wrap.setAttribute('aria-label', table.caption?.textContent || 'Tabel data, geser untuk kolom lainnya');
        table.before(wrap); wrap.append(table);
    });
    const nav = document.querySelector('[data-public-navbar]');
    const links = nav?.querySelector('.show-nav-links');
    const toggle = nav?.querySelector('.show-mobile-toggle');
    if (links && toggle) {
        const account = document.createElement('div'); account.className = 'show-mobile-account-links';
        nav.querySelectorAll('.show-actions a').forEach(link => account.append(link.cloneNode(true)));
        links.append(account);
        const close = () => { links.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); };
        document.addEventListener('click', e => { if (!nav.contains(e.target)) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && links.classList.contains('open')) { close(); toggle.focus(); } });
        matchMedia('(min-width:1181px)').addEventListener('change', close);
    }
    const member = document.querySelector('[data-member-mobile-drawer]');
    // Keep the drawer above the backdrop, outside the sticky header's stacking context.
    const memberShell = document.querySelector('[data-member-shell]');
    if (member && memberShell) memberShell.append(member);
    const admin = document.querySelector('.admin-shell:not(.owner-shell) [data-admin-sidebar]');
    const configurations = [
        { drawer: member, query: '(max-width:1439px)', trigger: '[data-member-mobile-toggle]',
          isOpen: () => member?.classList.contains('is-open'), close: () => document.querySelector('[data-member-mobile-close]')?.click() },
        { drawer: admin, query: '(max-width:1024px)', trigger: '[data-admin-toggle]',
          isOpen: () => admin?.classList.contains('is-open'), close: () => {
              admin?.classList.remove('is-open'); document.querySelector('.admin-backdrop')?.classList.remove('is-visible');
          } },
    ];
    const focusable = node => [...node.querySelectorAll('a[href],button,input,select,textarea,summary,[tabindex="0"]')]
        .filter(el => !el.disabled && el.getClientRects().length && getComputedStyle(el).visibility !== 'hidden');
    configurations.forEach(config => {
        const drawer = config.drawer; if (!drawer) return;
        const media = matchMedia(config.query); const trigger = document.querySelector(config.trigger);
        let wasOpen = false;
        drawer.querySelectorAll('a').forEach(a => { if (!a.title) a.title = a.textContent.trim().replace(/\s+/g,' '); });
        const update = () => {
            const open = media.matches && config.isOpen();
            if (!open && wasOpen && drawer.contains(document.activeElement)) trigger?.focus();
            drawer.inert = media.matches && !open;
            trigger?.setAttribute('aria-expanded', String(open));
            if (drawer === admin) {
                drawer.setAttribute('aria-hidden', String(media.matches && !open));
                document.body.classList.toggle('admin-drawer-open', Boolean(open));
            }
            if (open && !wasOpen) focusable(drawer)[0]?.focus();
            wasOpen = open;
            const closeButton = drawer.querySelector('[data-admin-sidebar-collapse]');
            if (closeButton && media.matches) { closeButton.setAttribute('aria-label','Tutup navigasi'); closeButton.title = 'Tutup navigasi'; }
        };
        new MutationObserver(update).observe(drawer, { attributes: true, attributeFilter: ['class'] });
        media.addEventListener('change', () => { config.close(); update(); });
        drawer.querySelector('[data-admin-sidebar-collapse]')?.addEventListener('click', () => { if (media.matches) config.close(); });
        document.addEventListener('keydown', event => {
            if (!media.matches || !config.isOpen()) return;
            if (event.key === 'Escape') { config.close(); update(); }
            if (event.key === 'Tab') {
                const items = focusable(drawer), first = items[0], last = items.at(-1);
                if (!first) return;
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
        update();
    });
    document.addEventListener('keydown', event => {
        const modal = [...document.querySelectorAll('[aria-modal="true"],dialog[open]')]
            .filter(e => e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden').at(-1);
        if (!modal) return;
        const items = focusable(modal), first = items[0], last = items.at(-1);
        if (event.key === 'Tab' && first) {
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
        if (event.key === 'Escape') modal.querySelector('[data-modal-close],.modal-close,[aria-label^="Tutup"]')?.click();
    });
})();
