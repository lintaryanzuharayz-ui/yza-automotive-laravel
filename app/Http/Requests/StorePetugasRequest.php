<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePetugasRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->roleKey()==='super_admin'; }
 public function rules(): array { return ['nama_lengkap'=>['required','string','max:120'],'jabatan'=>['required','string','max:80'],'no_hp'=>['required','string','max:25'],'alamat'=>['nullable','string'],'username'=>['required','string','max:50','regex:/^[A-Za-z0-9._-]{4,50}$/',Rule::unique('users','username')],'email'=>['required','email','max:120',Rule::unique('users','email')],'password'=>['required','string','min:8','confirmed']]; }
}
