<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.mysql.database'=>'yza_automotive_laravel_test','app.url'=>'http://127.0.0.1:8000']);Illuminate\Support\Facades\DB::purge('mysql');$db=Illuminate\Support\Facades\DB::connection();
if($db->getDatabaseName()!=='yza_automotive_laravel_test')throw new RuntimeException('Testing DB required');
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
$r=Illuminate\Http\Request::create('http://127.0.0.1:8000/admin/mobil');$r->setLaravelSession(new Illuminate\Session\Store('qa',new Illuminate\Session\ArraySessionHandler(120)));$r->setRouteResolver(fn()=>app('router')->getRoutes()->getByName('admin.mobil.index')->bind($r));
$u=App\Models\User::whereHas('role',fn($q)=>$q->where('nama_role','admin'))->firstOrFail();Illuminate\Support\Facades\Auth::setUser($u);$r->setUserResolver(fn()=>$u);app()->instance('request',$r);app('url')->setRequest($r);view()->share('errors',new Illuminate\Support\ViewErrorBag());
$html=app(App\Http\Controllers\Admin\MobilController::class)->index(app(App\Services\VehicleAvailabilityService::class))->render();
$html=str_replace('<head>','<head><script>window.fetch=async()=>new Response("{}",{headers:{"Content-Type":"application/json"}});document.addEventListener("submit",e=>e.preventDefault());document.addEventListener("click",e=>{if(e.target.closest("a"))e.preventDefault()});</script>',$html);
$qa=<<<'HTML'
<section style="position:relative;z-index:9999;background:white;padding:24px" id="qa"><h2>Uji nominal rupiah (data simulasi)</h2><form id="qaform"><label for="qa_price">Harga QA (Rp)</label><input id="qa_price" name="harga" type="number" step="0.01" min="0" required value="9000000000"><label for="qa_year">Tahun QA</label><input id="qa_year" name="tahun" type="number" value="2024"><label for="qa_qty">Jumlah QA</label><input id="qa_qty" name="jumlah" type="number" value="3"><label for="qa_mode">Jenis diskon QA</label><select id="qa_mode" name="manual_discount_type"><option value="nominal">Nominal</option><option value="percentage">Persentase</option></select><label for="qa_discount">Diskon QA</label><input id="qa_discount" type="number" name="manual_discount_value" value="1000"><button type="reset">Reset QA</button><button type="button" onclick="document.querySelector('#qa_price').value='1250000.50'">Ubah harga lewat perhitungan QA</button><button type="button" onclick="document.querySelector('#qa_price').setCustomValidity('DP melebihi total QA')">Validasi QA</button><button type="button" onclick="document.querySelector('#qa_price').setCustomValidity('')">Hapus validasi QA</button><button type="button" onclick="document.querySelector('#qaout').textContent=JSON.stringify(Object.fromEntries(new FormData(this.form)))">Periksa data QA</button></form><pre id="qaout"></pre></section>
HTML;
$html=str_replace('</body>',$qa.'</body>',$html);file_put_contents(public_path('__rupiah_input_qa.html'),$html);echo 'QA preview generated from isolated testing DB';
}finally{$db->rollBack();}

