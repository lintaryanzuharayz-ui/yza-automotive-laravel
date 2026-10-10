<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StorePengajuanMobilRequest extends FormRequest { public function authorize():bool{return $this->user()?->roleName()==='pelanggan';} public function rules():array{return ['id_kendaraan_pelanggan'=>['required','integer','exists:kendaraan_pelanggan,id_kendaraan_pelanggan'],'harga_harapan'=>['required','numeric','min:0'],'catatan'=>['nullable','string','max:2000']];}}
