const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

class Element {
    constructor(value=''){this.value=value;this.dataset={};this.listeners={};this.children=[];this.hidden=false;this.disabled=false;this.attributes={};this.style={};this.textContent='';this.valid=true;this.tagName='INPUT';}
    addEventListener(type,fn){(this.listeners[type]||=[]).push(fn);}
    async fire(type,extra={}){const event={target:this,defaultPrevented:false,preventDefault(){this.defaultPrevented=true;},...extra};for(const fn of this.listeners[type]||[])await fn(event);return event;}
    checkValidity(){return this.valid&&!this.validationMessage;}
    reportValidity(){this.reported=true;return this.checkValidity();}
    setCustomValidity(value){this.validationMessage=value;}
    setAttribute(name,value){this.attributes[name]=value;}
    removeAttribute(name){delete this.attributes[name];}
    focus(){this.focused=true;}
    append(...nodes){this.children.push(...nodes);}
    replaceChildren(){this.children=[];}
    click(){return this.fire('click');}
}
function harness(ready=true, colorBuilder=null, carBuilder=null){
    const nodes=new Map(), node=key=>{if(!nodes.has(key))nodes.set(key,new Element());return nodes.get(key);};
    const form=new Element();form.dataset={quoteUrl:'/admin/pesanan-mobil/quote',storageReady:ready?'1':'0'};
    const fields=['id_pelanggan','nama_pembeli','telepon_pembeli','email_pembeli','alamat_pembeli','manual_discount_type','manual_discount_value','manual_discount_reason','warna_pilihan','expected_base_price'];
    form.elements=Object.fromEntries(fields.map(key=>[key,new Element()]));form.elements.manual_discount_type.value='none';form.elements.manual_discount_value.value='0';
    const steps=[0,1,2].map(i=>{const p=new Element();p.dataset.spkStep=String(i);p.legend=new Element();p.querySelector=()=>p.legend;p.querySelectorAll=()=>[];return p;});
    const labels=steps.map(()=>new Element());
    form.querySelector=node;form.querySelectorAll=key=>key==='[data-spk-step]'?steps:labels;
    let cars=[{id:1,group:'A',color:'White',code:'VIN-W',vin:'VIN-REAL-W',stock:1,name:'QA Coupe',variant:'V8 · 2026',base_price:'900000000.00',image:'/white-real.jpg',swatch:'#ffffff'},
        {id:2,group:'A',color:'Red',code:'VIN-R',vin:'VIN-REAL-R',stock:1,name:'QA Coupe',variant:'V8 · 2026',base_price:'900000000.00',image:'/red-real.jpg',swatch:'#ff0000'},
        {id:3,group:'B',color:'Blue',code:'OTHER',vin:null,stock:4,name:'Other QA',variant:'V6 · 2026',base_price:'100000000.00',image:'/blue.jpg',swatch:'#0000ff'}];
    if(carBuilder)cars=carBuilder(cars);
    node('[data-spk-catalog]').textContent=JSON.stringify(cars);
    if(colorBuilder)node('[data-spk-color-catalog]').textContent=JSON.stringify(colorBuilder(cars));
    const unit=node('[data-spk-unit]');unit.options=[new Element(),...cars.map(car=>new Element(String(car.id)))];
    const requests=[],opener=new Element();
    const document={querySelector:()=>form,querySelectorAll:()=>[opener],createElement:()=>new Element(),createTextNode:text=>({textContent:text})};
    vm.runInNewContext(fs.readFileSync('public/assets/js/smart-spk-modal.js','utf8'),{document,Intl,URLSearchParams,AbortController,
        fetch:(url,options)=>new Promise(resolve=>requests.push({url,options,resolve}))});
    const answer=async(index,{discount='0.00',total='900000000.00',pending=false,ok=true,message}={})=>{
        const request=requests[index],query=new URLSearchParams(request.url.split('?')[1]);
        request.resolve({ok,json:async()=>message?{message}:{base_price:'900000000.00',discount_amount:discount,estimated_total:total,approval_required:pending,vehicle:cars.find(c=>String(c.id)===query.get('id_mobil'))}});
        await new Promise(resolve=>setImmediate(resolve));
    };
    const choose=async()=>{node('[data-spk-model]').value='A';await node('[data-spk-model]').fire('change');};
    return{form,node,steps,labels,cars,unit,requests,answer,choose,opener};
}

test('wizard starts at step one with no invented quote or unit',()=>{
    const h=harness();assert.equal(h.steps[0].hidden,false);assert.equal(h.steps[1].hidden,true);
    assert.equal(h.node('[data-spk-save]').hidden,true);assert.equal(h.node('[data-spk-base]').textContent,'—');assert.equal(h.requests.length,0);
});

test('catalog-only color remains visible with photo, no eligible unit and no save',async()=>{
    const h=harness(true, cars=>[{group:'A',model_key:'A',name:'QA Coupe',variant:'V8',colors:[
        {key:'white',name:'White',swatch:'#fff',image:'/white-real.jpg',available:true,stock:1,aggregate_stock:0,unit_ids:[1]},
        {key:'yellow',name:'Yellow',code:'yellow-catalog',swatch:'#ff0',image:'/yellow-catalog.jpg',available:false,stock:0,aggregate_stock:0,unit_ids:[]}
    ]}]);
    await h.choose();const buttons=h.node('[data-spk-swatches]').children;assert.equal(buttons.length,2);
    assert.match(buttons[1].attributes['aria-label'],/Tidak tersedia/);
    await buttons[1].click();assert.equal(h.unit.value,'');assert.equal(h.form.elements.warna_pilihan.value,'');
    assert.equal(h.node('[data-spk-image]').src,'/yellow-catalog.jpg');assert.equal(h.node('[data-spk-save]').disabled,true);
    const step=h.node('[data-spk-next]').click();await step;assert.equal(h.steps[0].hidden,false);
    assert.match(h.node('[data-spk-color-status]').textContent,/Tidak tersedia/);
});

test('variant change updates the actual unit and filters colors without changing model',async()=>{
    const h=harness(true,null,cars=>[...cars.map(c=>({...c,model_key:c.group})),{...cars[0],id:4,model_key:'A',group:'A2',variant:'V10',color:'Green',image:'/green-real.jpg'}]);
    await h.choose();assert.equal(h.unit.value,'1');
    h.node('[data-spk-variant]').value='A2';await h.node('[data-spk-variant]').fire('change');
    assert.equal(h.node('[data-spk-model]').value,'A');assert.equal(h.unit.value,'4');
    assert.equal(h.node('[data-spk-image]').src,'/green-real.jpg');assert.equal(h.node('[data-spk-swatches]').children.length,1);
    h.node('[data-spk-model]').value='B';await h.node('[data-spk-model]').fire('change');assert.equal(h.unit.value,'3');
});
test('swatches select actual compatible unit and image, without including another model',async()=>{
    const h=harness();await h.choose();assert.equal(h.unit.value,'1');
    const colors=h.node('[data-spk-swatches]').children;assert.equal(colors.length,2);
    await colors[1].click();assert.equal(h.unit.value,'2');assert.equal(h.form.elements.warna_pilihan.value,'Red');
    assert.equal(h.node('[data-spk-image]').src,'/red-real.jpg');assert.match(h.node('[data-spk-vin]').textContent,/VIN-REAL-R/);
    assert.equal(h.unit.options[3].disabled,true);
});
test('step navigation preserves buyer information and requires server quote',async()=>{
    const h=harness();await h.choose();await h.answer(0);
    h.form.elements.nama_pembeli.value='Member QA';
    const moving=h.node('[data-spk-next]').click();await h.answer(1);await moving;
    assert.equal(h.steps[1].hidden,false);await h.node('[data-spk-back]').click();
    assert.equal(h.form.elements.nama_pembeli.value,'Member QA');assert.equal(h.steps[0].hidden,false);
});
test('nonzero quotes are marked pending and cannot be saved without proposal storage',async()=>{
    const h=harness(false);await h.choose();await h.answer(0);
    h.form.elements.manual_discount_type.value='percentage';h.form.elements.manual_discount_value.value='30';
    const pending=h.form.elements.manual_discount_value.fire('input');await h.answer(1,{discount:'270000000.00',total:'630000000.00',pending:true});await pending;
    assert.equal(h.form.elements.manual_discount_reason.required,true);assert.equal(h.node('[data-spk-save]').disabled,true);
    assert.match(h.node('[data-spk-confirm-approval]').textContent,/Menunggu/);
    assert.match(h.node('[data-spk-total]').textContent,/630.000.000/);
    assert.equal(h.form.elements.expected_base_price.value,'900000000.00');
});
test('stale quote cannot overwrite a newly selected color or failed quote',async()=>{
    const h=harness();await h.choose();await h.node('[data-spk-swatches]').children[1].click();
    await h.answer(1,{ok:false,message:'Stok sudah habis'});await h.answer(0);
    assert.match(h.node('[data-spk-status]').textContent,/Stok sudah habis/);
    assert.equal(h.node('[data-spk-total]').textContent,'—');assert.equal(h.node('[data-spk-save]').disabled,true);
});
test('step-three save is a draft and never marks discount approved in the UI',async()=>{
    const h=harness();await h.choose();await h.answer(0);
    for(let i=0;i<2;i++){const p=h.node('[data-spk-next]').click();await h.answer(i+1);await p;}
    assert.equal(h.steps[2].hidden,false);assert.equal(h.node('[data-spk-next]').hidden,true);
    assert.equal(h.node('[data-spk-save]').disabled,false);
    const event=await h.form.fire('submit');assert.equal(event.defaultPrevented,false);assert.equal(h.node('[data-spk-save]').disabled,true);
});
