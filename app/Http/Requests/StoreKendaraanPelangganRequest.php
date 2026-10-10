<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreKendaraanPelangganRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->roleName()==='pelanggan'; }
 public function rules(): array { return self::base(); }
 public static function base(): array { return ['merk'=>['required','string','max:80'],'model'=>['required','string','max:100'],'tipe'=>['nullable','string','max:100'],'tahun'=>['nullable','integer','between:1886,'.(now()->year+1)],'warna'=>['nullable','string','max:50'],'transmisi'=>['nullable',Rule::in(['manual','automatic','cvt','dct','lainnya'])],'bahan_bakar'=>['nullable',Rule::in(['bensin','diesel','hybrid','listrik','lainnya'])],'nomor_polisi'=>['nullable','string','max:20'],'nomor_rangka'=>['nullable','string','max:100'],'nomor_mesin'=>['nullable','string','max:100'],'kilometer'=>['required','integer','min:0']]; }
}
