<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreTestDriveRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['id_mobil'=>'required|exists:mobil,id_mobil','tanggal'=>'required|date','jam'=>'required|date_format:H:i','lokasi'=>'nullable|string|max:255','catatan'=>'nullable|string'];}}
