<?php
namespace App\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
class StorePembayaranRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['jumlah'=>'required|numeric|min:0.01','metode'=>'required|in:tunai,transfer,kredit,lainnya','catatan'=>'nullable|string','bukti_transfer'=>'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120'];} }
