// Scoped source edits for this audit; never app data or business logic.
import fs from 'node:fs';
import postcss from 'postcss';
const phase = process.argv[2];
const changes = [];
function edit(file, maps) {
    const original = fs.readFileSync(file, 'utf8');
    const transform = css => {
    const root = postcss.parse(css);
    root.walkRules(rule => {
        const map = maps[rule.selector.trim()];
        if (!map) return;
        for (const [prop, value] of Object.entries(map)) {
            // Keep existing mobile/tablet stacking rules instead of replacing
            // their one-column layout with a desktop composition.
            let parent = rule.parent;
            let narrowQuery = false;
            while (parent) { if (parent.type === 'atrule' && /max-width/.test(parent.params || '')) narrowQuery = true; parent = parent.parent; }
            if (prop === 'grid-template-columns' && narrowQuery) continue;
            rule.walkDecls(prop, decl => {
                if (decl.value === value) return;
                changes.push({file, selector:rule.selector, media:rule.parent.type === 'atrule' ? rule.parent.params : null, prop, before:decl.value, after:value});
                decl.value = value;
            });
        }
    });
    return root.toString();
    };
    if (file.endsWith('.blade.php')) {
        let result = original.replace(/(<style[^>]*>)([\s\S]*?)(<\/style>)/g, (_, start, css, end) => start + transform(css) + end);
        const blocks = [...result.matchAll(/<style[^>]*>[\s\S]*?<\/style>/g)].map(m => m[0]);
        // Keep the last occurrence so the winning cascade position is preserved.
        let position = 0;
        result = result.replace(/<style[^>]*>[\s\S]*?<\/style>/g, block => blocks.lastIndexOf(block) === position++ ? block : '');
        fs.writeFileSync(file, result);
    } else fs.writeFileSync(file, transform(original.replace(/\0+$/, '')) + (original.match(/\0+$/)?.[0] || ''));
}
if (phase === 'B') {
    edit('public/assets/css/responsive-foundation.css', {
        '.showroom .show-hero-inner': {'min-height':'clamp(28rem,calc(76svh - var(--ui-header)),36rem)',padding:'2rem var(--ui-gutter)',gap:'1.5rem','grid-template-columns':'minmax(0,1fr) minmax(14rem,16rem)'},
        '.showroom .hero-copy-premium h1': {'font-size':'clamp(2.5rem,4.1vw,3.5rem)'},
        '.showroom .hero-vehicle-info': {'max-width':'16rem',padding:'1.125rem'},
        '.show-nav.public-navbar .show-brand img': {width:'3rem',height:'2.75rem'},
    });
    edit('public/assets/css/showroom.css', {
        '.showroom .hero-buttons .show-cta,.showroom .hero-buttons .show-ghost': {'min-height':'44px',padding:'10px 20px','font-size':'14px'},
        '.showroom .hero-vehicle-info': {'min-height':'256px',padding:'18px'},
        '.showroom .hero-vehicle-info h2': {'font-size':'26px'},
        '.showroom .hero-vehicle-info strong': {'font-size':'22px'},
        '.showroom .hero-vehicle-info a': {'min-height':'44px',padding:'10px 13px','font-size':'14px'},
        '.showroom .hero-buttons': {gap:'12px'},
        '.showroom .hero-proof-line': {gap:'16px'},
        '.showroom .featured-catalog .featured-media': {height:'190px'},
        '.showroom .featured-catalog .featured-body': {'min-height':'128px',padding:'16px'},
        '.showroom .featured-catalog .featured-body h3': {'font-size':'17px'},
        '.showroom .featured-catalog .featured-body small': {'font-size':'13px'},
        '.showroom .featured-catalog .featured-price-link strong': {'font-size':'18px'},
        '.showroom .featured-catalog .featured-carousel-controls .featured-carousel-button': {width:'44px',height:'44px','font-size':'24px'},
        '.showroom .show-section': {padding:'48px 0'},
        '.showroom .show-section-heading h2': {'font-size':'clamp(1.75rem,2.6vw,2.25rem)'},
        '.showroom .featured-catalog .show-section-heading h2': {'font-size':'clamp(1.75rem,2.6vw,2.25rem)'},
        '.showroom .services-heading h2': {'font-size':'clamp(1.75rem,2.6vw,2.25rem)'},
        '.showroom .testimonials .show-section-heading h2': {'font-size':'clamp(1.75rem,2.6vw,2.25rem)'},
    });
    edit('public/assets/css/mobil-detail.css', {
        '.detail-page': {width:'min(1440px,100%)',padding:'24px clamp(16px,2vw,32px) 48px'},
        '.detail-heading h1': {'font-size':'clamp(1.875rem,3.3vw,3rem)','line-height':'1.1'},
        '.detail-heading-price strong': {'font-size':'clamp(1.5rem,2vw,1.875rem)'},
        '.detail-heading': {gap:'20px'},
        '.detail-heading-grid': {gap:'20px'},
        '.detail-heading>.detail-back': {margin:'0 0 16px'},
        '.detail-summary-panel': {padding:'20px'},
        '.detail-summary-panel .detail-description': {'font-size':'.9375rem',margin:'12px 0 16px'},
        '.detail-actions-reference .detail-button-primary': {'min-height':'46px',padding:'0 14px'},
        '.detail-actions-reference .detail-button-ghost': {'min-height':'44px',padding:'0 12px'},
        '.detail-spec-grid-reference>div': {'min-height':'68px',padding:'10px'},
        '.detail-spec-grid-reference>div strong': {'font-size':'.875rem'},
        '.detail-thumbnail': {'min-height':'72px'},
        '.detail-thumbnail img': {height:'72px'},
        '.detail-gallery-arrow': {width:'44px',height:'44px'},
    });
} else if (phase === 'C') {
    edit('public/assets/css/showroom.css', {
        '.showroom .yza-member-section': {'min-height':'0',padding:'48px 0'},
        '.showroom .yza-member-layout': {'min-height':'0',gap:'32px','grid-template-columns':'minmax(0,1fr) minmax(0,1fr)'},
        '.showroom .yza-member-copy h2': {'font-size':'clamp(2rem,3.3vw,3rem)',margin:'12px 0 18px','line-height':'1.08'},
        '.showroom .yza-member-price': {margin:'20px 0',gap:'6px'},
        '.showroom .yza-member-price strong': {'font-size':'clamp(1.75rem,2.2vw,2.25rem)'},
        '.showroom .yza-member-card': {width:'min(100%,480px)',padding:'22px','min-height':'0'},
        '.showroom .yza-member-card img': {width:'52px',height:'44px'},
        '.showroom .yza-member-card strong': {'font-size':'clamp(1.5rem,2vw,1.875rem)'},
        '.showroom .yza-member-card-line': {margin:'16px 0'},
    });
    edit('public/assets/css/member-home.css', {
        '.yza-home-hero': {'min-height':'280px',padding:'24px clamp(16px,3vw,40px)',gap:'24px','grid-template-columns':'minmax(0,1fr) minmax(260px,.48fr)'},
        '.yza-home-hero h1': {'font-size':'clamp(1.875rem,3.25vw,3rem)','line-height':'1.1'},
        '.yza-home-hero p': {'font-size':'15px'},
        '.yza-home-member-card': {'min-height':'230px',padding:'20px'},
        '.yza-home .yza-home-member-card': {'min-height':'230px',padding:'20px'},
        '.yza-home .yza-member-card-code': {'font-size':'clamp(1rem,1.6vw,1.375rem)'},
        '.yza-member-card-code': {'font-size':'clamp(1rem,1.6vw,1.375rem)'},
        '.yza-home-stats>a': {padding:'16px 18px','min-height':'110px'},
        '.yza-home-stats strong': {'font-size':'30px'},
        '.yza-home-stats small,.yza-home-stats em': {'font-size':'12px'},
        '.yza-home-stats': {padding:'12px clamp(16px,3vw,40px) 0',gap:'12px'},
        '.yza-home-service-card': {'min-height':'190px',padding:'18px'},
        '.yza-home-service-card h3': {'font-size':'22px'},
        '.yza-home-section-head h2': {'font-size':'clamp(1.625rem,2.2vw,2rem)'},
        '.yza-home-garage-showcase .featured-media': {height:'190px','flex-basis':'190px'},
        '.yza-home-garage-showcase .featured-body': {'min-height':'128px',padding:'16px'},
        '.yza-home-garage-showcase .featured-carousel-viewport': {height:'auto'},
    });
    edit('resources/views/pelanggan/member-center.blade.php', {
        '.member-center-hero': {'min-height':'280px',padding:'24px',gap:'24px'},
        '.member-center-hero h1': {'font-size':'clamp(1.875rem,3.25vw,3rem)','line-height':'1.1'},
        '.member-center-hero p': {'font-size':'15px'},
        '.member-center-id-card': {'min-height':'224px',padding:'20px'},
        '.member-center-stats a': {padding:'16px 18px'},
        '.member-center-stats strong': {'font-size':'30px'},
        '.member-center-service-card': {'min-height':'192px',padding:'16px'},
        '.member-center-service-copy': {padding:'12px', 'max-width':'100%'},
        '.member-center-service-copy h3': {'font-size':'22px'},
        '.member-center-panel': {'min-height':'160px',padding:'18px'},
    });
    edit('public/assets/css/responsive-foundation.css', {
        '.member-center-dashboard .member-center-hero': {'min-height':'17.5rem',padding:'1.5rem var(--ui-gutter)',gap:'1.5rem'},
        '.member-center-dashboard .member-center-hero h1': {'font-size':'clamp(1.875rem,3.25vw,3rem)'},
        '.member-center-dashboard :is(.member-center-id-card,.yza-home-member-card)': {'min-height':'14rem',padding:'1.25rem'},
        '.member-center-service-card': {'min-height':'12rem',padding:'1rem'},
    });
    edit('public/assets/css/pages/membership-flow.css', {
        '.membership-payment-card': {width:'min(94%,720px)',padding:'24px clamp(18px,2vw,28px)'},
        '.membership-payment-card h1': {'font-size':'clamp(1.875rem,2.4vw,2.5rem)'},
        '.membership-payment-summary': {padding:'18px'},
        '.membership-payment-summary h2': {'font-size':'24px'},
        '.membership-payment-price': {'font-size':'28px'},
        '.membership-payment-actions .button': {'min-height':'46px'},
    });
} else if (phase === 'D') {
    // Repair the unmatched closing brace by returning its filter rules to the
    // existing mobile media block. This also stops mobile filters applying globally.
    const p='public/assets/css/admin-portal.css';let s=fs.readFileSync(p,'utf8');
    const orphan=/(@media \(max-width: 600px\) \{\s*\.admin-orders-stats-grid \{\s*grid-template-columns: 1fr;\s*\})\s*\}\s*(\.admin-sales-filters \{)/;
    if(orphan.test(s)){s=s.replace(orphan,'$1\n    $2');fs.writeFileSync(p,s);changes.push({file:p,selector:'@media max-width:600px',prop:'structure',before:'orphan filter block / unexpected closing brace',after:'filter rules inside existing mobile query'});}
    edit(p, {
        '.admin-page-heading h1': {'font-size':'var(--ui-page-title)'},
        '.admin-nav-item': {padding:'9px 10px','font-size':'13px'},
        '.admin-panel': {padding:'18px'},
        '.admin-panel-heading h2': {'font-size':'22px'},
        '.admin-service-card': {'min-height':'128px',gap:'10px'},
        '.admin-service-card>strong': {'font-size':'30px'},
        '.admin-hero-copy': {padding:'20px'},
    });
    edit('public/assets/css/owner-executive.css', {
        '.oe-topbar': {'min-height':'68px',padding:'10px 24px'},
        '.oe-page-header h1': {'font-size':'clamp(1.5rem,2.2vw,2rem)'},
        '.oe-kpi': {padding:'16px'},
        '.oe-kpi-value': {'font-size':'28px'},
        '.oe-panel': {padding:'18px'},
        '.oe-panel-heading': {'margin-bottom':'16px'},
        '.oe-panel-heading h2': {'font-size':'20px'},
        '.oe-empty': {'min-height':'190px'},
        '.oe-nav-link': {'min-height':'44px',padding:'9px 12px'},
    });
} else if (phase === 'E') {
    edit('public/assets/css/admin-portal.css', {
        '.admin-sales-heading': {'font-size':'clamp(1.5rem,2.1vw,2rem)'},
        '.admin-preorder-page .admin-sales-heading': {'font-size':'28px'},
        '.admin-sales-page .admin-sales-heading': {'font-size':'28px'},
        '.admin-selling-page .admin-sales-heading': {'font-size':'28px'},
        '.transactions-heading-copy h1': {'font-size':'28px'},
        '.admin-preorder-page .admin-sales-banner': {height:'200px'},
        '.admin-selling-page .admin-sales-banner': {height:'200px'},
        '.admin-sales-page.admin-preorder-page .admin-sales-banner': {height:'200px'},
    });
    edit('resources/views/operations/servis/show-premium.blade.php', {
        '.wo-hero-copy h1': {'font-size':'28px'},
        '.wo-stepper': {padding:'18px'},
        '.wo-panel': {padding:'18px'},
        '.wo-form label': {'font-size':'12px'},
        '.wo-form input,.wo-form select,.wo-form textarea': {'min-height':'44px','font-size':'14px'},
    });
    edit('resources/views/operations/servis/wizard.blade.php', {
        '.wz-page': {padding:'18px clamp(16px,2vw,28px) 40px'},
        '.wz-card': {padding:'18px'},
        '.wz-stepper': {padding:'16px'},
        '.wz-label': {'font-size':'12px'},
        '.wz-input,.wz-select,.wz-textarea': {'min-height':'44px','font-size':'14px'},
        '.wz-btn': {'min-height':'44px'},
    });
    edit('public/assets/css/pages/service-detail-premium.css', {
        '.service-detail-page': {padding:'8px 0 32px'},
        '.service-detail-hero': {'min-height':'220px',padding:'22px'},
        '.service-detail-hero h1': {'font-size':'clamp(1.875rem,3vw,2.5rem)'},
        '.service-detail-card': {padding:'18px'},
        '.service-detail-card h2': {'font-size':'22px'},
    });
    edit('public/assets/css/pages/service-detail-premium-overrides.css', {
        '.service-detail-page .member-approval-section': {padding:'20px'},
        '.member-approval-page .member-approval-section': {padding:'20px'},
        '.member-approval-page': {padding:'20px 16px 40px'},
    });
} else if (phase === 'F') {
    edit('public/assets/css/mobil-detail.css', {
        '.detail-actions-reference .detail-action-row .detail-button': {'min-height':'48px',padding:'8px 12px'},
    });
    edit('public/assets/css/showroom.css', {
        '.showroom .featured-catalog .show-section-heading h2': {'font-size':'clamp(1.75rem,2.6vw,2.25rem)'},
        '.showroom .services-heading h2': {'font-size':'clamp(1.75rem,2.6vw,2.25rem)'},
        '.showroom .yza-member-section': {padding:'40px 0'},
        '.showroom .yza-member-card': {width:'min(100%,430px)'},
        '.showroom .yza-member-visual': {gap:'16px'},
        '.showroom .yza-member-benefits': {'min-height':'0',padding:'16px',gap:'12px 20px'},
        '.showroom .yza-member-benefit': {'grid-template-columns':'44px minmax(0,1fr)',gap:'10px',padding:'6px 0'},
        '.showroom .yza-member-benefit i': {width:'40px',height:'40px','font-size':'21px'},
        '.showroom .yza-member-benefit b': {'font-size':'13px'},
        '.showroom .yza-member-benefit small': {'font-size':'13px'},
        '.showroom .yza-member-actions .show-cta,.showroom .yza-member-actions .show-ghost': {'min-height':'44px',padding:'10px 20px','font-size':'14px'},
    });
    edit('public/assets/css/member-checkout.css', {
        '.checkout-heading h1': {'font-size':'clamp(1.875rem,2.8vw,2.5rem)','line-height':'1.1'},
        '.checkout-heading p': {'font-size':'15px'},
        '.checkout-form-card': {padding:'18px'},
        '.checkout-summary-box': {padding:'16px'},
        '.checkout-summary-list': {gap:'10px'},
    });
} else if (phase === 'auth') {
    edit('public/assets/css/pages/login.css', {
        '.login-brand': {'min-height':'200px'},
        '.login-form-side': {padding:'32px clamp(20px,3vw,48px)'},
        '.login-form h2': {'font-size':'clamp(1.75rem,2.5vw,2.25rem)'},
        '.login-form .intro': {'font-size':'15px'},
        '.login-form .form-label': {'font-size':'14px'},
        '.login-form .form-control': {height:'46px','font-size':'16px'},
        '.login-form-actions .login-submit': {'min-height':'46px','font-size':'15px'},
    });
    edit('public/assets/css/pages/register.css', {
        '.register-showcase': {'min-height':'180px',padding:'24px'},
        '.register-card': {width:'min(100%,680px)',padding:'20px'},
        '.register-card h2': {'font-size':'clamp(1.625rem,2.1vw,2rem)'},
        '.register-card .intro': {'font-size':'15px'},
        '.register-card .form-actions .button': {'min-height':'44px'},
    });
} else if (phase === 'catalog') {
    edit('resources/views/mobil/index.blade.php', {
        '.catalog-hero': {'min-height':'220px',padding:'24px'},
        '.catalog-hero h1': {'font-size':'clamp(1.875rem,3.2vw,2.75rem)'},
        '.catalog-hero p': {'font-size':'15px'},
        '.catalog-card-body': {'min-height':'150px',padding:'16px'},
        '.catalog-card-body h3': {'font-size':'17px'},
        '.catalog-inventory .catalog-card-body': {padding:'16px'},
        '.catalog-card-image': {'aspect-ratio':'16/10'},
        '.catalog-card-image img': {'object-fit':'contain'},
    });
    edit('resources/views/pelanggan/katalog-mobil/index.blade.php', {
        '.member-catalog-hero': {'min-height':'220px',padding:'24px'},
        '.member-catalog-hero h1': {'font-size':'clamp(1.875rem,3.2vw,2.75rem)'},
        '.member-catalog-hero p': {'font-size':'15px'},
        '.member-catalog-page .catalog-card-body': {padding:'16px'},
        '.member-catalog-page .catalog-card-body h3': {'font-size':'18px','min-height':'44px'},
    });
} else throw Error('Unknown phase '+phase);
fs.writeFileSync('docs/qa/compact-100/phase-'+phase+'-source-changes.json',JSON.stringify(changes,null,2)+'\n');
console.log(phase+': '+changes.length+' scoped source declarations updated');
