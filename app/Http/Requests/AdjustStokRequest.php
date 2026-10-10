<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AdjustStokRequest extends FormRequest { public function authorize(): bool{return true;} public function rules():array{return ['id_sparepart'=>'required|exists:sparepart,id_sparepart','stok_baru'=>'required|integer|min:0','alasan'=>'required|string','catatan'=>'required|string'];} }
