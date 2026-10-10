<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdatePetugasRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->roleName()==='admin'; }
 public function rules(): array { $p=$this->route('petugas'); $id=$p instanceof \App\Models\Petugas?$p->id_user:null; return ['nama_lengkap'=>['required','string','max:120'],'jabatan'=>['required','string','max:80'],'no_hp'=>['nullable','string','max:25'],'alamat'=>['nullable','string'],'username'=>['required','string','max:50',Rule::unique('users','username')->ignore($id,'id_user')],'email'=>['required','email','max:120',Rule::unique('users','email')->ignore($id,'id_user')]]; }
}
