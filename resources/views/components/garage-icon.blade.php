@props(['name'])
@php
    $paths = [
        'car' => 'm3 13 2-6h14l2 6v6h-3v-2H6v2H3v-6Zm0 0h18M7 7l2-3h6l2 3M7 14h.01M17 14h.01',
        'shield' => 'M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Zm-4 9 3 3 5-6',
        'distance' => 'M4 19a9 9 0 1 1 16 0H4Zm8-4 4-5M5 13h2m10 0h2M8 7l1 2m6-2-1 2',
        'calendar' => 'M5 5h14v16H5V5Zm3-3v6m8-6v6M5 11h14M8 14h2m4 0h2m-8 4h2m4 0h2',
        'info' => 'M12 11v6m0-10h.01M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0',
        'history' => 'M3 4v6h6M3 10a9 9 0 1 1 1 8m8-11v6l4 2',
        'edit' => 'm15 4 5 5M4 20l1-6L16 3l5 5-11 11-6 1Z',
        'trade' => 'M3 7h18m-5-5 5 5-5 5M21 17H3m5-5-5 5 5 5',
        'plus' => 'M12 3v18M3 12h18',
    ];
@endphp
<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="{{ $paths[$name] ?? $paths['car'] }}"/></svg>
