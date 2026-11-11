(function (root, factory) {
    'use strict';
    const api = factory();
    if (typeof module === 'object' && module.exports) module.exports = api;
    if (root.document) { root.YzaRupiah = api; api.mount(root.document); }
})(typeof globalThis === 'object' ? globalThis : this, function () {
    'use strict';

    function expand(value) {
        let text = String(value ?? '').trim();
        const exponent = text.match(/^(-?)(\d+)(?:\.(\d*))?[eE]([+-]?\d+)$/);
        if (exponent) {
            const power = Number(exponent[4]);
            if (!Number.isSafeInteger(power) || Math.abs(power) > 400) return null;
            const digits = exponent[2] + (exponent[3] || ''), point = exponent[2].length + power;
            text = exponent[1] + (point <= 0 ? '0.' + '0'.repeat(-point) + digits
                : point >= digits.length ? digits + '0'.repeat(point - digits.length) : digits.slice(0, point) + '.' + digits.slice(point));
        }
        if (!/^-?\d+(?:\.\d*)?$/.test(text)) return text === '' ? '' : null;
        const negative = text.startsWith('-'), [integer, fraction] = text.replace(/^-/, '').split('.');
        return (negative ? '-' : '') + integer.replace(/^0+(?=\d)/, '') + (fraction !== undefined ? '.' + fraction : '');
    }

    function format(value, editing = false) {
        const canonical = expand(value);
        if (canonical === null || canonical === '') return String(value ?? '');
        const [whole, fraction] = canonical.split('.');
        const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return grouped + (fraction !== undefined && (editing || /[1-9]/.test(fraction)) ? ',' + fraction : '');
    }

    function parse(value) {
        const text = String(value ?? '').trim().replace(/^rp\.?\s*/i, '').replace(/\s/g, '');
        if (text === '') return '';
        if (!/^-?[\d.]+(?:,\d*)?$/.test(text)) return null;
        const [integer, fraction] = text.split(',');
        return expand(integer.replace(/\./g, '') + (fraction !== undefined ? '.' + fraction : ''));
    }

    function isMonetary(name, label = '', modes = {}) {
        const key = String(name || '').replace(/[\[\]]+/g, '_').replace(/_+$/, '').toLowerCase();
        if (/(?:^|_)(?:id|tahun|stok|qty|quantity|kilometer|poin|points|persen|percentage|telepon|phone)(?:_|$)/.test(key)) return false;
        if (key === 'manual_discount_value') return modes.discount === 'nominal';
        if (key === 'nilai_benefit') return modes.benefit === 'nominal';
        if (/(?:^|_)(?:harga|biaya|tarif|nominal|amount)(?:_|$)/.test(key)) return true;
        if (['dp', 'diskon', 'discount', 'subtotal', 'total', 'total_akhir', 'total_tagihan', 'uang_muka', 'minimum_transaksi', 'maksimum_benefit'].includes(key)) return true;
        return /\brp\b|rupiah|harga|biaya|tarif|nominal|jumlah\s+(?:bayar|pembayaran)/i.test(label) && !/%|persen/i.test(label);
    }

    function mount(doc) {
        if (doc.documentElement.dataset.rupiahReady === '1') return;
        doc.documentElement.dataset.rupiahReady = '1';
        const states = new Map(), nativeValue = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
        let sequence = 0;
        function labelFor(input) {
            const labels = input.labels ? [...input.labels] : [];
            return labels.map(label => label.textContent).join(' ') || input.closest('label')?.textContent
                || input.parentElement?.querySelector(':scope > label')?.textContent || input.getAttribute('aria-label') || '';
        }
        function eligible(input, state) {
            if (input.dataset.rupiahDisplay !== undefined || input.dataset.rupiah === 'off') return false;
            const type = state?.type || input.type;
            if (!['number', 'text'].includes(type)) return false;
            const modes = { discount: input.form?.elements.manual_discount_type?.value, benefit: input.form?.elements.jenis_benefit?.value };
            return input.dataset.rupiah === 'on' || isMonetary(input.name, state?.label || labelFor(input), modes);
        }
        function validate(state) {
            const {raw, display, probe} = state;
            for (const attr of ['min', 'max', 'step']) {
                if (raw.hasAttribute(attr)) probe.setAttribute(attr, raw.getAttribute(attr)); else probe.removeAttribute(attr);
            }
            probe.required = raw.required; probe.value = nativeValue.get.call(raw);
            probe.setCustomValidity(state.customMessage || '');
            const value = nativeValue.get.call(raw);
            const malformed = (value !== '' && (expand(value) === null || probe.value === '')) || state.invalid;
            display.setCustomValidity(malformed ? 'Masukkan nominal rupiah yang valid. Gunakan koma untuk desimal.' : probe.validationMessage);
        }
        function sync(state, editing = doc.activeElement === state.display) {
            const {raw, display} = state;
            const before = display.value, caret = display.selectionStart;
            const digitsLeft = before.slice(0, caret ?? 0).replace(/\D/g, '').length;
            const afterComma = caret !== null && before[caret - 1] === ',';
            const value = nativeValue.get.call(raw);
            display.value = state.invalid ? state.invalidText : format(value, editing);
            display.disabled = raw.disabled; display.readOnly = raw.readOnly; display.required = raw.required;
            display.className = raw.className;
            for (const attr of ['aria-invalid', 'aria-describedby']) {
                if (raw.hasAttribute(attr)) display.setAttribute(attr, raw.getAttribute(attr)); else display.removeAttribute(attr);
            }
            validate(state);
            if (editing && caret !== null) {
                let position = 0, digits = 0;
                while (position < display.value.length && digits < digitsLeft) { if (/\d/.test(display.value[position])) digits++; position++; }
                if (afterComma && display.value[position] === ',') position++;
                if (before === '-' && display.value === '-') position = 1;
                display.setSelectionRange(position, position);
            }
        }
        function unbind(state) {
            const {raw, display} = state, value = nativeValue.get.call(raw);
            raw.removeEventListener('input', state.rawSync); raw.removeEventListener('change', state.rawSync);
            delete raw.value; delete raw.focus; delete raw.setCustomValidity; delete raw.checkValidity; delete raw.reportValidity;
            raw.type = state.type; raw.defaultValue = state.defaultValue; nativeValue.set.call(raw, value);
            for (const label of state.labels) if (label.htmlFor === display.id) label.htmlFor = state.id;
            raw.removeAttribute('data-rupiah-raw'); display.remove(); states.delete(raw);
        }
        function bind(raw) {
            const state = {raw, type:raw.type, defaultValue:raw.defaultValue, id:raw.id, label:labelFor(raw), labels:[...(raw.labels || [])], invalid:false,
                customMessage:raw.validity.customError ? raw.validationMessage : ''};
            const display = raw.cloneNode(false), probe = doc.createElement('input'); probe.type = 'number';
            display.type = 'text'; display.removeAttribute('name'); display.removeAttribute('min'); display.removeAttribute('max');
            display.removeAttribute('step'); display.removeAttribute('maxlength'); display.removeAttribute('pattern');
            display.removeAttribute('autofocus');
            for (const attribute of [...display.attributes]) if (attribute.name.startsWith('data-') || attribute.name.startsWith('on')) display.removeAttribute(attribute.name);
            display.dataset.rupiahDisplay = ''; display.inputMode = 'decimal';
            display.defaultValue = format(state.defaultValue);
            display.id = (raw.id || 'yza-price-' + (++sequence)) + '__rupiah';
            state.display = display; state.probe = probe;
            raw.type = 'hidden'; raw.dataset.rupiahRaw = ''; raw.after(display);
            for (const label of state.labels) if (label.htmlFor === state.id && state.id) label.htmlFor = display.id;
            states.set(raw, state);
            Object.defineProperty(raw, 'value', {configurable:true, get() {return nativeValue.get.call(this);}, set(value) {
                nativeValue.set.call(this, value); state.invalid = false; sync(state);
            }});
            raw.focus = options => display.focus(options);
            const nativeCustomValidity = HTMLInputElement.prototype.setCustomValidity;
            raw.setCustomValidity = message => { nativeCustomValidity.call(raw, message); state.customMessage = String(message); validate(state); };
            raw.checkValidity = () => display.checkValidity(); raw.reportValidity = () => display.reportValidity();
            function update(event) {
                event.stopPropagation();
                const value = parse(display.value);
                state.invalid = value === null; state.invalidText = display.value;
                // Invalid text must never silently submit the previous valid amount.
                nativeValue.set.call(raw, value === null ? display.value : value);
                sync(state, true);
                raw.dispatchEvent(new Event(event.type, {bubbles:true}));
            }
            display.addEventListener('input', update);
            display.addEventListener('change', update);
            display.addEventListener('paste', event => {
                const text = event.clipboardData?.getData('text')?.trim();
                // Accept a pasted canonical decimal too, without mistaking a valid
                // Indonesian grouping such as 1.000 for the decimal amount 1.
                if (text && /^-?\d+\.\d{1,2}$/.test(text) && !/^-?\d{1,3}(?:\.\d{3})+$/.test(text)
                    && (display.value === '' || (display.selectionStart === 0 && display.selectionEnd === display.value.length))) {
                    event.preventDefault(); display.value = text.replace('.', ',');
                    display.dispatchEvent(new Event('input', {bubbles:true}));
                }
            });
            display.addEventListener('blur', () => {
                if (!state.invalid && nativeValue.get.call(raw).endsWith('.')) nativeValue.set.call(raw, nativeValue.get.call(raw).slice(0, -1));
                sync(state, false);
            });
            state.rawSync = () => sync(state);
            raw.addEventListener('input', state.rawSync); raw.addEventListener('change', state.rawSync);
            sync(state, false);
        }
        function scan(scope = doc) {
            for (const [raw, state] of states) {
                if (!raw.isConnected) { states.delete(raw); continue; }
                if (!eligible(raw, state)) unbind(state);
            }
            const inputs = scope.matches?.('input') ? [scope] : [...scope.querySelectorAll('input')];
            for (const input of inputs) if (!states.has(input) && eligible(input)) bind(input);
        }
        scan();
        new MutationObserver(records => {
            for (const record of records) {
                if (record.type === 'attributes') { const state = states.get(record.target); if (state) sync(state); }
                else for (const node of record.addedNodes) if (node.nodeType === 1 && node.dataset.rupiahDisplay === undefined) scan(node);
            }
        }).observe(doc.body, {subtree:true,childList:true,attributes:true,attributeFilter:['disabled','readonly','required','min','max','step','class','aria-invalid','aria-describedby']});
        doc.addEventListener('change', event => { if (event.target.matches('select')) scan(event.target.form || doc); }, true);
        // Native reset completes after the reset event's default action.
        doc.addEventListener('reset', event => setTimeout(() => {
            if (event.defaultPrevented) return;
            for (const state of states.values()) if (state.raw.form === event.target) {
                nativeValue.set.call(state.raw, state.defaultValue); state.invalid = false; sync(state, false);
            }
        }, 0), true);
        return {refresh:scan};
    }
    return {format, parse, isMonetary, mount};
});
