<?php
namespace App\Http\Requests;
use Illuminate\Validation\Rule;
class UpdateJenisServisRequest extends \App\Http\Requests\StoreJenisServisRequest { public function rules():array{$m=$this->route('jenis_servis');$id=$m instanceof \App\Models\JenisServis?$m->id_jenis_servis:$m;return ['nama_servis'=>['required','string','max:120',Rule::unique('jenis_servis','nama_servis')->ignore($id,'id_jenis_servis')],'deskripsi'=>['nullable','string'],'estimasi_harga'=>['required','numeric','min:0'],'duration_minutes'=>['required','integer','min:15','max:480'],'status'=>['required',Rule::in(['aktif','nonaktif'])]];} }
