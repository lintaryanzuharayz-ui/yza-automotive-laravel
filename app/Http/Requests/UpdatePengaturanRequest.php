<?php
namespace App\Http\Requests;
use App\Services\PengaturanService;
use Illuminate\Foundation\Http\FormRequest;
class UpdatePengaturanRequest extends FormRequest { public function authorize():bool{return $this->user()?->roleName()==='admin';} public function rules():array{return ['nilai'=>['nullable','string','max:10000']];} public function settingKey():string{return (string)$this->route('pengaturan');} public function withValidator($v):void{$v->after(function($v){$s=app(PengaturanService::class);if(!$s->validateValue($this->settingKey(),$this->input('nilai')))$v->errors()->add('nilai','Nilai pengaturan tidak valid.');});} }
