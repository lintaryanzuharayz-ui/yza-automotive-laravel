<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdatePelangganRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->roleName()==='pelanggan' || $this->user()?->roleName()==='admin'; }
 public function rules(): array { $p=$this->route('pelanggan'); $id=$p instanceof \App\Models\Pelanggan?$p->id_pelanggan:null; return ['nama_lengkap'=>['required','string','max:120'],'no_hp'=>['required','string','max:25','regex:/^[0-9+()\-\s]{8,25}$/'],'jenis_kelamin'=>['nullable',Rule::in(['L','P'])],'tanggal_lahir'=>['nullable','date'],'alamat'=>['nullable','string'],'email'=>['sometimes','nullable','email','max:120',Rule::unique('users','email')->ignore($this->user()?->id_user,'id_user')]]; }
}
