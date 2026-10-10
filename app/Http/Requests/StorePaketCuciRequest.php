<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePaketCuciRequest extends FormRequest { public function authorize():bool{return $this->user()?->roleName()==='admin';} public function rules():array{return ['nama_paket'=>['required','string','max:100',Rule::unique('paket_cuci','nama_paket')],'deskripsi'=>['nullable','string'],'harga'=>['required','numeric','min:0'],'estimasi_menit'=>['nullable','integer','min:0'],'status'=>['required',Rule::in(['aktif','nonaktif'])]];} }
