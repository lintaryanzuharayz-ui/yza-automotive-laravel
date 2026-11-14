const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const context = {module:{exports:{}}};
vm.runInNewContext(fs.readFileSync('public/assets/js/rupiah-input.js','utf8'), context);
const {format,parse,isMonetary} = context.module.exports;

test('groups every three digits without floating point rounding', () => {
    for (const [raw,expected] of [['0','0'],['999','999'],['1000','1.000'],['9000000000','9.000.000.000'],
        ['68000000000.00','68.000.000.000'],['12345678901234567890.50','12.345.678.901.234.567.890,50'],
        ['1234.25','1.234,25'],['-12500.50','-12.500,50'],['1e6','1.000.000'],['','']]) assert.equal(format(raw),expected);
});
test('Indonesian input round trips to canonical numeric strings', () => {
    for (const raw of ['0','1000','9000000000','12345678901234567890.50','-1200.75']) assert.equal(parse(format(raw)),raw);
    assert.equal(parse('Rp 9.000.000.000'),'9000000000');
    assert.equal(parse('1.000,50'),'1000.50');
    assert.equal(parse(''), '');
    assert.equal(parse('9abc000'),null);
    assert.equal(parse('1,2,3'),null);
    assert.equal(format('1.00',true),'1,00');
});
test('only monetary fields qualify, never years, quantity, phone, IDs or percentage', () => {
    for (const name of ['harga_jual','harga_beli','estimasi_harga','harga_harapan','harga_min','biaya','tarif','dp','diskon','amount','minimum_transaksi','maksimum_benefit','target_nominal','details[0][harga_satuan]']) assert.equal(isMonetary(name),true,name);
    for (const name of ['tahun','tahun_min','stok','stok_minimum','jumlah','qty','no_hp','id_mobil','persentase','total_poin']) assert.equal(isMonetary(name),false,name);
    assert.equal(isMonetary('jumlah','Jumlah pembayaran (Rp)'),true);
    assert.equal(isMonetary('jumlah','Jumlah sparepart'),false);
    assert.equal(isMonetary('manual_discount_value','Nilai diskon',{discount:'nominal'}),true);
    assert.equal(isMonetary('manual_discount_value','Nilai diskon',{discount:'percentage'}),false);
    assert.equal(isMonetary('nilai_benefit','Nilai benefit',{benefit:'persen'}),false);
});
