<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountSessionRevoker
{
    public function revoke(int $userId): array
    {
        $driver = config('session.driver');
        if ($driver === 'database') {
            $count = DB::connection(config('session.connection'))->table(config('session.table'))->where('user_id', $userId)->delete();
            return ['driver' => $driver, 'revoked' => $count, 'unreadable' => 0];
        }
        if ($driver === 'array') return ['driver' => $driver, 'revoked' => 0, 'unreadable' => 0];
        if ($driver !== 'file') throw new RuntimeException('Session driver belum didukung untuk pembersihan fisik; guard akun nonaktif tetap menolak akses.');
        $directory = realpath(config('session.files'));
        if (!$directory) throw new RuntimeException('Direktori session tidak tersedia.');
        $revoked = 0; $unreadable = 0;
        foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            if (!is_file($file) || is_link($file) || !preg_match('/^[A-Za-z0-9]{40}$/', basename($file))) continue;
            $raw = file_get_contents($file);
            try {
                if (config('session.encrypt')) $raw = app('encrypter')->decrypt($raw);
                $payload = config('session.serialization', 'php') === 'json' ? json_decode($raw, true) : @unserialize($raw, ['allowed_classes' => false]);
                if (!is_array($payload)) { $unreadable++; continue; }
                foreach ($payload as $key => $value) {
                    if (str_starts_with((string) $key, 'login_web_') && is_scalar($value) && (string) $value === (string) $userId) {
                        if (!unlink($file)) throw new RuntimeException('Session target gagal dicabut.');
                        $revoked++; break;
                    }
                }
            } catch (\Illuminate\Contracts\Encryption\DecryptException) { $unreadable++; }
        }
        return ['driver' => $driver, 'revoked' => $revoked, 'unreadable' => $unreadable];
    }
}
