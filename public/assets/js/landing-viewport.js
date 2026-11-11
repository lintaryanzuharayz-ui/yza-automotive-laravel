/* Account for the actual navbar height, including zoom and font changes. */
(() => {
    const landing = document.querySelector('.showroom.landing');
    const navbar = landing?.querySelector('[data-public-navbar]');
    if (!navbar) return;
    const updateHeight = () => {
        landing.style.setProperty('--landing-nav-height', `${navbar.getBoundingClientRect().height}px`);
    };
    updateHeight();
    if ('ResizeObserver' in window) new ResizeObserver(updateHeight).observe(navbar);
    else window.addEventListener('resize', updateHeight, { passive: true });
})();
