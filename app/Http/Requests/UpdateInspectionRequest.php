<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateInspectionRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['hasil_pemeriksaan'=>'required|string','catatan'=>'nullable|string'];} }
