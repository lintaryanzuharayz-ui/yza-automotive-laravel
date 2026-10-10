<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreDetailPenjualanSparepartRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['id_sparepart'=>['required','integer','exists:sparepart,id_sparepart'],'jumlah'=>['required','integer','min:1']];} }
