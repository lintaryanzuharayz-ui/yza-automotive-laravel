<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StorePenjualanSparepartRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['id_pelanggan'=>['nullable','integer','exists:pelanggan,id_pelanggan'],'catatan'=>['nullable','string','max:2000']];} }
