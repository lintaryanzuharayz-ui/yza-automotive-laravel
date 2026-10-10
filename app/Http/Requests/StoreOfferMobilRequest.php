<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreOfferMobilRequest extends FormRequest { public function authorize():bool{return in_array($this->user()?->roleName(),['admin','petugas'],true);} public function rules():array{return ['harga_penawaran'=>['required','numeric','min:0']];}}
