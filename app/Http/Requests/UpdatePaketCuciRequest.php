<?php
namespace App\Http\Requests;
use Illuminate\Validation\Rule;
class UpdatePaketCuciRequest extends \App\Http\Requests\StorePaketCuciRequest { public function rules():array{$m=$this->route('paket_cuci');$id=$m instanceof \App\Models\PaketCuci?$m->id_paket:$m;return ['nama_paket'=>['required','string','max:100',Rule::unique('paket_cuci','nama_paket')->ignore($id,'id_paket')],'deskripsi'=>['nullable','string'],'harga'=>['required','numeric','min:0'],'estimasi_menit'=>['nullable','integer','min:0'],'status'=>['required',Rule::in(['aktif','nonaktif'])]];} }
