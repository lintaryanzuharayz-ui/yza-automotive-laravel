<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ChangePasswordRequest extends FormRequest
{
 public function authorize(): bool { return (bool)$this->user(); }
 public function rules(): array { return ['password_lama'=>['required','string'],'password_baru'=>['required','string','min:8','confirmed']]; }
}
