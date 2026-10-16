<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\IndonesiaRegionService;
class RegisterRequest extends FormRequest
{
 public function authorize(): bool { return !$this->user(); }
 public function rules(): array { return ['current_step'=>['nullable','integer',Rule::in([1,2,3])],'membership_confirmed'=>[Rule::requiredIf(fn(): bool => $this->integer('current_step') === 3),Rule::in(['1'])],'nama_lengkap'=>['required','string','max:120'],'no_hp'=>['required','string','max:25','regex:/^[0-9+()\-\s]{8,25}$/'],'email'=>['required','email','max:120',Rule::unique('users','email')],'username'=>['required','string','max:50','regex:/^[A-Za-z0-9._-]{4,50}$/',Rule::unique('users','username')],'password'=>['required','string','min:8','confirmed'],'jenis_kelamin'=>['required',Rule::in(['L','P'])],'tanggal_lahir'=>['required','date'],'province_code'=>['required','string','max:10'],'city_code'=>['required','string','max:10'],'district_code'=>['required','string','max:12'],'village_code'=>['required','string','max:16'],'address_detail'=>['nullable','string','max:500']]; }
 public function messages(): array
 {
     return [
         'membership_confirmed.required' => 'Konfirmasi biaya aktivasi membership terlebih dahulu.',
         'membership_confirmed.in' => 'Konfirmasi biaya aktivasi membership terlebih dahulu.',
         'email.unique' => 'Email tersebut sudah terdaftar. Gunakan email lain atau masuk ke akun yang sudah ada.',
         'username.unique' => 'Username tersebut sudah digunakan. Silakan pilih username lain.',
         'email.email' => 'Format email belum benar.',
         'password.confirmed' => 'Konfirmasi password tidak sama.',
         'password.min' => 'Password minimal terdiri dari 8 karakter.',
         'no_hp.regex' => 'Nomor HP hanya boleh berisi angka dan karakter telepon yang valid.',
         'current_step.in' => 'Tahap registrasi tidak valid.',
     ];
 }
 public function withValidator(Validator $validator): void
 {
     $validator->after(function (Validator $validator): void {
         if (!$validator->errors()->isEmpty()) return;
         if (!app(IndonesiaRegionService::class)->resolveHierarchy($this->only(['province_code', 'city_code', 'district_code', 'village_code']))) {
             $validator->errors()->add('village_code', 'Kombinasi wilayah tidak valid. Silakan pilih ulang alamat secara berjenjang.');
         }
     });
 }

 protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
 {
     $from = in_array($this->query('from'), ['membership', 'login'], true)
         ? $this->query('from')
         : 'login';
     $parameters = ['from' => $from];
     if ($this->filled('current_step')) $parameters['step'] = $this->integer('current_step');

     throw (new ValidationException($validator))
         ->redirectTo(route('register', $parameters));
 }
}
