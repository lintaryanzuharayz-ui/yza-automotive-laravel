<?php
namespace App\Services;
use App\Models\Pengaturan;
class PengaturanService
{
 public const KEYS=['nama_perusahaan','nama_singkat','nama_sistem','alamat_perusahaan','no_hp_perusahaan','email_perusahaan','mata_uang','stok_notifikasi_default','timezone','membership_activation_fee'];
 public function get(string $key, ?string $default=null): ?string { return in_array($key,self::KEYS,true)?(Pengaturan::where('kunci_pengaturan',$key)->value('nilai')??$default):$default; }
 public function validateValue(string $key, ?string $value): bool { if(!in_array($key,self::KEYS,true))return false;if($key==='membership_activation_fee')return $value!==null&&is_numeric($value)&&((float)$value>=0);if($key==='stok_notifikasi_default')return $value!==null&&ctype_digit($value)&&((int)$value>=0);if($key==='timezone')return $value!==null&&in_array($value,['Asia/Jakarta','Asia/Makassar','Asia/Jayapura','UTC'],true);if($key==='mata_uang')return $value!==null&&preg_match('/^[A-Z]{3}$/',$value)===1;return $value===null||mb_strlen($value)<=10000; }
 public function membershipFee(): float { return (float) ($this->get('membership_activation_fee', '249000') ?: 249000); }
}
