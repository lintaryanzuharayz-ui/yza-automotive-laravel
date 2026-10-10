<?php
namespace App\Http\Requests;
use Illuminate\Validation\Rule;
class UpdateKendaraanPelangganRequest extends \App\Http\Requests\StoreKendaraanPelangganRequest { public function rules(): array { return self::base(); } }
