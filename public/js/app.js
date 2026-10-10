document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.yza-menu-toggle');
    const sidebar = document.querySelector('.yza-sidebar');
    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        sidebar.classList.toggle('is-open', !expanded);
    });
});
