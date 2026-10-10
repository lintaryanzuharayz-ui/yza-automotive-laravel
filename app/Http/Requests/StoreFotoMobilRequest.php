<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFotoMobilRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleName() === 'admin'; }
    public function rules(): array { return ['foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]; }
}
