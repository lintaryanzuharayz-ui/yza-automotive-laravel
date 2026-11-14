const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

class Element {
    constructor(value = '') { this.value = value; this.dataset = {}; this.listeners = {}; this.hidden = false; this.textContent = ''; this.checked = false; this.attributes = {}; this.classList = {toggle() {},remove() {},add() {}}; }
    addEventListener(event, fn) { (this.listeners[event] ||= []).push(fn); }
    async fire(event, extra = {}) { for (const fn of this.listeners[event] || []) await fn({target:this,...extra}); }
    setCustomValidity(value) { this.validityMessage = value; }
    focus() { this.focused = true; }
    setAttribute(key, value) { this.attributes[key] = value; }
    removeAttribute(key) { delete this.attributes[key]; }
    scrollIntoView() {}
}

function saleHarness() {
    const nodes = new Map(), node = key => { if (!nodes.has(key)) nodes.set(key, new Element()); return nodes.get(key); };
    const form = node('form'), box = node('[data-sale-promo]'), radio = new Element('1');
    const car = node('#sale-car'), customer = node('#sale-customer'), booking = node('#sale-booking');
    car.selectedOptions = [{value:'', dataset:{price:'100000000', brand:'QA', model:'Unit', code:'QA-1', year:'2026', image:'/real.jpg', status:'tersedia'}}];
    booking.selectedOptions = [{dataset:{}}];
    node('#sale-discount').value = '0'; node('#sale-down-payment').value = '0';
    form.querySelector = selector => selector.startsWith('[data-validation=') ? {classList:{toggle() {}},querySelector:part => node(selector+part)} : node(selector);
    box.querySelector = node; box.querySelectorAll = () => [radio]; box.dataset.url = '/admin/penjualan-mobil/promo-preview';
    const requests = [];
    const context = {document:{getElementById:()=>form,querySelector:node}, window:{addEventListener(){}}, Intl, URLSearchParams, AbortController, setTimeout,
        fetch:(url,options)=>new Promise(resolve=>requests.push({url,options,resolve}))};
    vm.runInNewContext(fs.readFileSync('public/assets/js/sale-create.js','utf8'), context);
    return {node,form,box,radio,car,customer,booking,requests};
}

test('sale starts with informative placeholders and hidden promo summary', () => {
    const h = saleHarness();
    assert.equal(h.node('#sale-total').textContent, '—');
    assert.equal(h.node('.sale-promo-summary').hidden, true);
    assert.equal(h.node('#sale-summary-car').hidden, true);
    assert.equal(h.requests.length, 0);
});

test('promo requires customer and vehicle and rejects stacking manual discount', async () => {
    const h = saleHarness(); h.radio.checked = true;
    await h.radio.fire('change');
    assert.match(h.node('.sale-promo-status').textContent, /Pilih pelanggan/);
    assert.ok(h.radio.validityMessage);
    h.customer.value = '2'; h.car.value = '3'; h.node('#sale-discount').value = '1';
    await h.node('#sale-discount').fire('input');
    assert.match(h.node('.sale-promo-status').textContent, /tidak dapat digabungkan/);
    assert.equal(h.requests.length, 0);
});

test('server preview follows selected customer and updates both summaries without claiming applied', async () => {
    const h = saleHarness(); h.customer.value = '2'; h.car.value = '3'; h.radio.checked = true;
    const pending = h.radio.fire('change');
    assert.match(h.requests[0].url, /id_pelanggan=2&id_mobil=3&promo_id=1/);
    h.requests[0].resolve({ok:true,json:async()=>({eligible:true,subtotal:100000000,discount:10000000,total_final:90000000})});
    await pending;
    assert.equal(h.radio.validityMessage, '');
    assert.equal(h.node('.sale-promo-summary').hidden, false);
    assert.match(h.node('#sale-total').textContent, /90.000.000/);
    assert.match(h.node('.sale-promo-status').textContent, /Estimasi/);
    assert.doesNotMatch(h.node('.sale-promo-status').textContent, /berhasil diterapkan/);
    await h.node('[data-sale-promo-remove]').fire('click');
    assert.equal(h.node('.sale-promo-summary').hidden, true);
    assert.equal(h.radio.checked, false);
    assert.match(h.node('#sale-total').textContent, /100.000.000/);
});

test('stale promo response cannot overwrite changed selection', async () => {
    const h = saleHarness(); h.customer.value = '2'; h.car.value = '3'; h.radio.checked = true;
    const first = h.radio.fire('change');
    h.customer.value = '4'; const second = h.customer.fire('change');
    h.requests[1].resolve({ok:false,json:async()=>({message:'Member tidak memenuhi syarat.'})}); await second;
    h.requests[0].resolve({ok:true,json:async()=>({eligible:true,subtotal:100,discount:10,total_final:90})}); await first;
    assert.equal(h.node('.sale-promo-summary').hidden, true);
    assert.match(h.node('.sale-promo-status').textContent, /tidak memenuhi syarat/);
    assert.ok(h.radio.validityMessage);
});

test('DP and booking relationship are validated against selected unit', async () => {
    const h = saleHarness(); h.customer.value = '2'; h.car.value = '3';
    h.node('#sale-down-payment').value = '100000001'; await h.node('#sale-down-payment').fire('input');
    assert.match(h.node('#sale-down-payment').validityMessage, /melebihi/);
    h.booking.value = '5'; h.booking.selectedOptions = [{dataset:{customer:'9',car:'3'}}]; await h.booking.fire('input');
    assert.match(h.booking.validityMessage, /harus sesuai/);
});

test('modal opens in place, closes, restores focus, and fills buyer data', async () => {
    const dialog = new Element(), opener = new Element(), close = new Element(), form = new Element();
    form.elements = {id_pelanggan:new Element(),nama_pembeli:new Element(),telepon_pembeli:new Element(),alamat_pembeli:new Element()};
    const picker = new Element(), search = new Element(), list = new Element(), empty = new Element(), option = new Element();
    option.dataset = {id:'12', name:'Nama QA', member:'YZA-QA-12', code:'PLG-QA', phone:'08123', address:'Alamat QA'};
    form.querySelector = () => picker;
    picker.querySelector = key => ({'[data-member-search]':search,'[role=listbox]':list,'[data-member-empty]':empty}[key]);
    picker.querySelectorAll = () => [option]; picker.contains = item => [search,list,option].includes(item);
    dialog.querySelector = () => form; dialog.querySelectorAll = () => [close];
    dialog.showModal = () => {dialog.open = true;}; dialog.close = () => {dialog.open = false; dialog.fire('close');};
    const document = {getElementById:()=>dialog,querySelectorAll:()=>[opener],querySelector:()=>opener};
    vm.runInNewContext(fs.readFileSync('public/assets/js/order-create-modal.js','utf8'), {document});
    await opener.fire('click'); assert.equal(dialog.open,true);
    search.value = 'tidak ada'; await search.fire('input'); assert.equal(option.hidden,true); assert.equal(empty.hidden,false);
    search.value = 'YZA-QA'; await search.fire('input'); assert.equal(option.hidden,false);
    assert.equal(form.elements.id_pelanggan.value,''); assert.ok(search.validityMessage);
    await option.fire('click'); assert.equal(form.elements.nama_pembeli.value, 'Nama QA');
    assert.equal(form.elements.id_pelanggan.value,'12'); assert.equal(search.validityMessage,''); assert.equal(list.hidden,true);
    assert.equal(form.elements.telepon_pembeli.value,'08123'); assert.equal(form.elements.alamat_pembeli.value,'Alamat QA');
    await search.fire('click'); assert.equal(list.hidden,false);
    const keyEvent = key => ({key, preventDefault() {}, stopPropagation() {}});
    await search.fire('keydown', keyEvent('ArrowUp'));
    assert.equal(search.attributes['aria-activedescendant'],option.id);
    await search.fire('keydown', keyEvent('Enter')); assert.equal(list.hidden,true);
    await search.fire('click'); await search.fire('keydown', keyEvent('Escape')); assert.equal(list.hidden,true);
    await search.fire('click'); await search.fire('keydown', keyEvent('Tab')); assert.equal(list.hidden,true);
    await close.fire('click'); assert.equal(dialog.open,false); assert.equal(opener.focused,true);
});
