<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreJenisServisRequest extends FormRequest { public function authorize():bool{return $this->user()?->roleName()==='admin';} public function rules():array{return ['nama_servis'=>['required','string','max:120',Rule::unique('jenis_servis','nama_servis')],'deskripsi'=>['nullable','string'],'estimasi_harga'=>['required','numeric','min:0'],'duration_minutes'=>['required','integer','min:15','max:480'],'status'=>['required',Rule::in(['aktif','nonaktif'])]];} }
