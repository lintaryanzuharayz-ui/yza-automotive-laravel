<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreOfferMobilRequest extends FormRequest { public function authorize():bool{return $this->user()?->isStaff() ?? false;} public function rules():array{return ['harga_penawaran'=>['required','numeric','min:0']];}}
