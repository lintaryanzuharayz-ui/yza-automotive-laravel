<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreDetailStokMasukRequest extends FormRequest { public function authorize(): bool{return true;} public function rules():array{return ['id_sparepart'=>'required|exists:sparepart,id_sparepart','jumlah'=>'required|integer|min:1','harga_beli'=>'required|numeric|min:0'];} }
