<?php
namespace App\Services;
use App\Models\Pelanggan;
use RuntimeException;
class MemberIdentityService
{
 public function memberCode(Pelanggan $customer):string{return 'YZA-MBR-'.str_pad((string)$customer->id_pelanggan,6,'0',STR_PAD_LEFT);}
 public function since(Pelanggan $customer){return $customer->created_at;}
 public function assertActive(Pelanggan $customer, $user):void{if(!$customer->exists||$customer->status!=='aktif'||$user->status!=='aktif'||($customer->membership&& !$customer->membership->isActive()))throw new RuntimeException('Aktifkan YZA Member terlebih dahulu untuk menggunakan layanan ini.');}
}
