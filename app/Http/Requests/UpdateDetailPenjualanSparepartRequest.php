<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateDetailPenjualanSparepartRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['jumlah'=>['required','integer','min:1']];} }
