<?php
if (! function_exists('money')) {
    function money($amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('yza_whatsapp_url')) {
    function yza_whatsapp_url(?string $message = null): string
    {
        $number = app(\App\Services\PengaturanService::class)->get('no_hp_perusahaan', '087784304417');
        $number = preg_replace('/[^0-9+]/', '', (string) $number);
        $number = str_starts_with($number, '+') ? substr($number, 1) : $number;
        $number = str_starts_with($number, '0') ? '62'.substr($number, 1) : $number;

        return 'https://wa.me/'.$number.($message !== null && $message !== '' ? '?text='.rawurlencode($message) : '');
    }
}
