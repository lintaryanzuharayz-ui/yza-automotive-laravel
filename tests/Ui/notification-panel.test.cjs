const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('public/assets/js/notification-panel.js', 'utf8');

function panelHarness() {
    let document;
    class Element {
        constructor(tag = 'div') { this.tagName = tag.toUpperCase(); this.children = []; this.dataset = {}; this.attributes = {}; this.listeners = {}; this.hidden = false; this.className = ''; this.style = {setProperty() {}}; }
        get childNodes() { return this.children; }
        get childElementCount() { return this.children.length; }
        get classList() { return { add: key => { this.className += ` ${key}`; }, remove: key => { this.className = this.className.split(' ').filter(v => v !== key).join(' '); }, toggle: (key, active) => { this.classList.remove(key); if (active) this.classList.add(key); } }; }
        append(...nodes) { for (const node of nodes) this.children.push(...(node.tagName === 'FRAGMENT' ? node.children : [node])); }
        replaceChildren(...nodes) { this.children = []; this.append(...nodes); }
        setAttribute(name, value) { this.attributes[name] = value; }
        addEventListener(type, callback) { this.listeners[type] = callback; }
        contains(element) { return element === this || this.children.some(child => child.contains?.(element)); }
        focus() { document.activeElement = this; }
        closest() { return {getBoundingClientRect() { return {bottom: 64}; }}; }
        getBoundingClientRect() { return {bottom: 64}; }
    }
    const panel = new Element(), trigger = new Element('button'), list = new Element(), count = new Element(), total = new Element();
    const all = new Element('button'), unread = new Element('button'); all.dataset.panelFilter = 'all'; unread.dataset.panelFilter = 'unread';
    const timers = new Map(), events = {}, requested = [];
    let timer = 0;
    const data = { unread_count: 3, total_count: 6, pending_actions: [{ count: 15, label: 'Task count, not unread' }], notifications: Array.from({length:6}, (_, index) => ({id:index + 1,title:`Notification ${index + 1}`,message:'Long notification message remains text, not HTML.',is_read:index % 2 === 0,created_at:'2026-11-08T00:00:00Z',open_url:`http://localhost/notifikasi/${index + 1}/open`})) };
    panel.id = 'yza-notification-panel'; panel.hidden = true; panel.dataset.syncUrl = 'http://localhost/notifikasi/sync';
    panel.querySelector = selector => ({'[data-panel-list]':list,'[data-panel-count]':count,'[data-panel-total]':total,'[data-panel-initial]':{textContent:JSON.stringify(data)},'[data-panel-filter]':all}[selector] || null);
    panel.querySelectorAll = selector => selector === '[data-panel-filter]' ? [all, unread] : [];
    panel.contains = element => [list, all, unread].some(item => item.contains(element));
    document = { activeElement:null, documentElement:{setAttribute() {},removeAttribute() {}},
        querySelector: () => panel, querySelectorAll: selector => selector.includes('[data-member-notification-toggle]') ? [trigger] : [],
        createElement: tag => new Element(tag), createDocumentFragment: () => new Element('fragment'),
        addEventListener: (type,callback) => {events[type]=callback;}, dispatchEvent() {},
    };
    const window = {innerHeight:700,addEventListener() {}};
    const context = {document,window,location:{origin:'http://localhost'},URL,AbortController,Event,
        matchMedia:() => ({matches:false}),setTimeout:(fn,ms) => {timers.set(++timer,{fn,ms});return timer;},clearTimeout:id=>timers.delete(id),
        fetch:async url=>{requested.push(String(url));return {ok:true,status:200,json:async()=>data};},
    };
    vm.runInNewContext(source,context);
    const click = element => element.listeners.click({preventDefault() {},stopImmediatePropagation() {}});
    return {panel,trigger,list,count,total,all,unread,timers,events,window,data,requested,click,
        finishClose() { for (const [id,value] of timers) {timers.delete(id);value.fn();} },
    };
}

test('panel shows four latest messages, keeps unread 3 separate from total 6, and ignores operational summaries', () => {
    const h=panelHarness();
    assert.equal(h.list.childElementCount,4); assert.equal(h.count.textContent,'3 belum dibaca'); assert.equal(h.total.textContent,'(6)');
    assert.equal(h.list.children[0].dataset.notificationId,6);
    h.window.YzaNotificationPanel.clear(); assert.equal(h.list.childElementCount,1); // empty-state message; no missing pending DOM access
});

test('bell opens without an auto-close timer and can close again', () => {
    const h=panelHarness(); h.click(h.trigger);
    assert.equal(h.panel.hidden,false); assert.equal(h.trigger.attributes['aria-expanded'],'true'); assert.equal(h.timers.size,0);
    h.click(h.trigger); h.finishClose(); assert.equal(h.panel.hidden,true); assert.equal(h.trigger.attributes['aria-expanded'],'false');
});

test('unread filter changes items without changing global total', () => {
    const h=panelHarness(); h.click(h.unread);
    assert.equal(h.list.childElementCount,3); assert.equal(h.total.textContent,'(6)');
    assert.equal(h.unread.attributes['aria-pressed'],'true'); assert.match(h.requested[0],/filter=unread/);
    h.click(h.all); assert.equal(h.list.childElementCount,4);
});

test('Escape and outside click close, and ArrowDown enters keyboard controls', () => {
    const h=panelHarness(); h.trigger.listeners.keydown({key:'ArrowDown',preventDefault() {}});
    assert.equal(h.panel.hidden,false); h.events.keydown({key:'Escape'}); h.finishClose(); assert.equal(h.panel.hidden,true);
    h.click(h.trigger); h.events.click({target:{}}); h.finishClose(); assert.equal(h.panel.hidden,true);
});

test('message links retain server open URLs and payloads are rendered as text', () => {
    const h=panelHarness(); const card=h.list.children[0];
    assert.equal(card.href,'http://localhost/notifikasi/6/open'); assert.equal(card.children[1].children[0].textContent,'Notification 6');
    const unsafe={...h.data,notifications:[{id:9,title:'<script>unsafe</script>',message:'<b>text only</b>',open_url:'https://another-host.invalid/'}]};
    h.window.YzaNotificationPanel.sync(unsafe,true); assert.equal(h.list.childElementCount,1); assert.equal(h.list.children[0].className,'ynp-empty');
});
