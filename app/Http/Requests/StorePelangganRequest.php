<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePelangganRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->roleName()==='admin'; }
 public function rules(): array { return ['nama_lengkap'=>['required','string','max:120'],'no_hp'=>['required','string','max:25'],'jenis_kelamin'=>['nullable',Rule::in(['L','P'])],'tanggal_lahir'=>['nullable','date'],'alamat'=>['nullable','string'],'username'=>['nullable','string','max:50',Rule::unique('users','username')],'email'=>['nullable','email','max:120',Rule::unique('users','email')],'password'=>['nullable','string','min:8','confirmed']]; }
}
