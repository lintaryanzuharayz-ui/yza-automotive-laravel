<?php

namespace Tests\Ui;

use App\Models\{Notifikasi, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PremiumNotificationPreviewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_export_notification_panels_from_isolated_test_pages(): void
    {
        $this->assertSame('yza_automotive_laravel_test', \DB::connection()->getDatabaseName());
        $directory = base_path('docs/qa/premium-notification');
        File::ensureDirectoryExists($directory);
        foreach (['pelanggan' => 'pelanggan.member', 'admin' => 'admin.dashboard', 'owner' => 'owner.dashboard'] as $role => $page) {
            $user = User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
            foreach ([['Servis mulai dikerjakan', 'Pemeriksaan untuk Tim teknisi mulai mengerjakan.'],
                ['Persetujuan servis diperlukan', 'Ada pekerjaan tambahan yang menunggu persetujuan Anda.'],
                ['Persetujuan servis diterima', 'Pekerjaan tambahan disetujui customer.'],
                ['Pembayaran ditolak', 'Pembayaran pesanan Anda belum dapat diverifikasi.']] as $index => [$title, $message]) {
                $item = new Notifikasi();
                $item->forceFill(['id_user' => $user->id_user, 'tipe' => 'servis', 'judul' => $title,
                    'pesan' => $message, 'is_read' => $index !== 3, 'created_at' => now()->subDays(3 - $index)])->save();
            }
            $response = $this->actingAs($user)->get(route($page));
            $response->assertOk()->assertSee('data-yza-notification-panel', false);
            $sync = $this->getJson(route($role === 'admin' ? 'admin.notifikasi.sync' : 'notifications.sync', ['panel' => 1]));
            $sync->assertOk();
            $json = str_replace('127.0.0.1:8000', '127.0.0.1:8765', $sync->getContent());
            File::put($directory.'/'.$role.'.json', $json);
            $html = str_replace('http://127.0.0.1:8000/', 'http://127.0.0.1:8765/public/', $response->getContent());
            $html = str_replace('127.0.0.1:8000', '127.0.0.1:8765', $html);
            $html = preg_replace('/(data-yza-notification-panel data-sync-url=")[^"]+/', '$1http://127.0.0.1:8765/docs/qa/premium-notification/'.$role.'.json', $html);
            File::put($directory.'/'.$role.'.html', $html);
            $auto = '<script>document.querySelector("[aria-controls=yza-notification-panel]")?.click();setTimeout(()=>{const p=document.querySelector("[data-yza-notification-panel]"),r=p.getBoundingClientRect(),f=p.querySelector(".ynp-footer").getBoundingClientRect();parent.postMessage({type:"qa-notification",theme:p.className,width:innerWidth,clientWidth:document.documentElement.clientWidth,left:r.left,right:r.right,bottom:r.bottom,footerBottom:f.bottom,open:!p.hidden,fits:r.left>=0&&r.right<=document.documentElement.clientWidth&&r.bottom<=innerHeight},location.origin)},600);</script>';
            File::put($directory.'/'.$role.'-auto.html', str_replace('</body>', $auto.'</body>', $html));
        }
        $frames = '';
        foreach ([320, 375, 390, 430, 768, 1024, 1366, 1440, 1920] as $width) {
            foreach (['pelanggan', 'admin'] as $role) {
                $frames .= '<h2>'.$role.' '.$width.'px</h2><iframe title="'.$role.' '.$width.'" src="'.$role.'-auto.html" width="'.$width.'" height="650"></iframe>';
            }
        }
        File::put($directory.'/responsive.html', '<!doctype html><html><head><meta charset="utf-8"><title>Notification responsive QA</title></head><body><pre data-results hidden></pre><script>const results=[];addEventListener("message",e=>{if(e.origin!==location.origin||e.data?.type!=="qa-notification")return;results.push(e.data);document.querySelector("[data-results]").textContent=JSON.stringify(results,null,2)});</script>'.$frames.'</body></html>');
    }
}
