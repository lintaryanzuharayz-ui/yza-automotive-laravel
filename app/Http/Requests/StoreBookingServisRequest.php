<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreBookingServisRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['id_kendaraan_pelanggan'=>'required|integer','id_jenis_servis'=>'required|exists:jenis_servis,id_jenis_servis','tanggal'=>'required|date','jam'=>'required|date_format:H:i','keluhan'=>'nullable|string','catatan'=>'nullable|string'];}}
