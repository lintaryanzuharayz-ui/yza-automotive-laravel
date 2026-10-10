<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreDetailServisRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['id_jenis_servis'=>'nullable|integer|exists:jenis_servis,id_jenis_servis','deskripsi_pekerjaan'=>'required|string|max:255','biaya'=>'required|numeric|min:0','catatan'=>'nullable|string'];} }
