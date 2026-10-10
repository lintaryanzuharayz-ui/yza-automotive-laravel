<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdatePetugasProfileRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->roleName()==='petugas'; }
 public function rules(): array { return ['nama_lengkap'=>['required','string','max:120'],'no_hp'=>['required','string','max:25'],'alamat'=>['nullable','string']]; }
}
