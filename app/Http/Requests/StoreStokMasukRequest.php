<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreStokMasukRequest extends FormRequest { public function authorize(): bool{return true;} public function rules():array{return ['id_supplier'=>'nullable|exists:supplier,id_supplier','tanggal'=>'required|date','catatan'=>'nullable|string'];} }
