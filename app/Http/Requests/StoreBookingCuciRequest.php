<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreBookingCuciRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['id_kendaraan_pelanggan'=>'required|integer','id_paket'=>'required|exists:paket_cuci,id_paket','tanggal'=>'required|date','jam'=>'required|date_format:H:i','catatan'=>'nullable|string'];}}
