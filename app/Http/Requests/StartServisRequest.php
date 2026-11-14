<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StartServisRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['kilometer'=>'nullable|integer|min:0','catatan'=>'nullable|string','id_petugas'=>'nullable|integer|exists:petugas,id_petugas','id_service_advisor'=>'nullable|integer|exists:petugas,id_petugas'];} }
