<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreTradeInRequest extends FormRequest { public function authorize():bool{return $this->user()?->roleName()==='pelanggan';} public function rules():array{return ['id_pengajuan'=>'required|integer|exists:pengajuan_mobil,id_pengajuan','id_inspeksi'=>'required|integer|exists:inspeksi_mobil,id_inspeksi','id_mobil_dibeli'=>'required|integer|exists:mobil,id_mobil','catatan'=>'nullable|string|max:2000'];} }
