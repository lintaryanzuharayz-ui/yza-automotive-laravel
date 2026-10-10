<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StorePenjualanMobilRequest extends FormRequest { public function authorize():bool{return in_array($this->user()?->roleName(),['admin','petugas'],true);} public function rules():array{return ['id_pelanggan'=>['required','integer','exists:pelanggan,id_pelanggan'],'id_mobil'=>['required','integer','exists:mobil,id_mobil'],'id_booking'=>['nullable','integer','exists:booking_mobil,id_booking'],'diskon'=>['required','numeric','min:0'],'dp'=>['required','numeric','min:0'],'metode_pembelian'=>['required','in:tunai,kredit'],'catatan'=>['nullable','string','max:2000']];}}
