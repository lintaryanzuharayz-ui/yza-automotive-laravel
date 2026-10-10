<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreBookingMobilRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['id_mobil'=>'required|exists:mobil,id_mobil','catatan'=>'nullable|string'];}}
