<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
class MidtransClient
{
 private function key():string{ $key=(string)config('midtrans.server_key'); if($key==='')throw new RuntimeException('Midtrans Sandbox Server Key belum dikonfigurasi.'); if((bool)config('midtrans.is_production'))throw new RuntimeException('Mode pembayaran harus menggunakan Midtrans Sandbox.'); return $key; }
 public function createSnapTransaction(array $payload):array
 {
  $response=Http::withBasicAuth($this->key(),'')->timeout((int)config('midtrans.timeout',15))->acceptJson()->post(config('midtrans.snap_url'),$payload);
  if(!$response->successful()){ Log::error('Midtrans Sandbox create transaction failed',['status'=>$response->status(),'response'=>$response->json() ?: $response->body(),'order_id'=>$payload['transaction_details']['order_id']??null]); throw new RuntimeException('Midtrans Sandbox tidak dapat membuat pembayaran.'); }
  $data=$response->json();if(empty($data['token'])||empty($data['redirect_url']))throw new RuntimeException('Respons Midtrans Sandbox tidak lengkap.');return $data;
 }
 public function getTransactionStatus(string $orderId):array
 {
  $response=Http::withBasicAuth($this->key(),'')->timeout((int)config('midtrans.timeout',15))->acceptJson()->get(rtrim(config('midtrans.api_url'),'/').'/'.urlencode($orderId).'/status');
  if(!$response->successful()){ Log::error('Midtrans Sandbox status request failed',['status'=>$response->status(),'response'=>$response->json() ?: $response->body(),'order_id'=>$orderId]); throw new RuntimeException('Status Midtrans Sandbox tidak dapat diperiksa.'); }return $response->json();
 }
 public function verifySignature(array $data):bool
 {
  foreach(['order_id','status_code','gross_amount','signature_key'] as $field)if(!array_key_exists($field,$data))return false;
  $expected=hash('sha512',(string)$data['order_id'].(string)$data['status_code'].(string)$data['gross_amount'].$this->key());
  return hash_equals($expected,(string)$data['signature_key']);
 }
}
