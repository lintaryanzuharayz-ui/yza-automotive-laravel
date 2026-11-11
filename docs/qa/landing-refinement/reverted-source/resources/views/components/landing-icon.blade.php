@props(['name' => 'car'])
@php
    $paths = [
        'car' => '<path d="m3 14 2-6h14l2 6v5H3zM5 8l2-4h10l2 4M3 14h18M7 19v2m10-2v2M7 16h.01M17 16h.01"/>',
        'wrench' => '<path d="M14 6a5 5 0 0 0-6 6l-5 5a2 2 0 0 0 4 4l5-5a5 5 0 0 0 6-6l-3 3-4-4 3-3Z"/>',
        'engine' => '<path d="M3 10v7h3l3 3h8l4-4V9h-4l-3-3H8v4H3Zm6-7h5M2 12H0m21-1h2v7h-2"/>',
        'oil' => '<path d="M12 3C9 7 5 11 5 15a7 7 0 0 0 14 0c0-4-4-8-7-12Z"/><path d="M9 16a3 3 0 0 0 3 3"/>',
        'brakes' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="M18 4h3v8h-3M5 6l2 2m-3 4h3m-2 6 2-2"/>',
        'snowflake' => '<path d="M12 2v20M3.3 7l17.4 10M3.3 17 20.7 7M9 4l3 3 3-3M9 20l3-3 3 3M3 10l4-1-1-4m12 14-1-4 4-1M3 14l4 1-1 4m12-14-1 4 4 1"/>',
        'scan' => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="m6 12 3-3 3 5 3-6 3 3M8 22h8m-4-4v4"/>',
        'tag' => '<path d="M3 3h8l10 10-8 8L3 11V3Z"/><circle cx="7.5" cy="7.5" r="1"/>',
        'exchange' => '<path d="M3 7h17l-4-4m4 4-4 4M21 17H4l4-4m-4 4 4 4"/>',
        'spark' => '<path d="m12 3 3 6 6 3-6 3-3 6-3-6-6-3 6-3 3-6Z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'bolt' => '<path d="m13 2-9 12h7l-1 8 10-13h-7l0-7Z"/>',
        'shield' => '<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone' => '<path d="m5 3 4 4-2 3a14 14 0 0 0 7 7l3-2 4 4-2 3C10 23 1 14 2 5l3-2Z"/>',
    ];
@endphp
<svg class="landing-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? $paths['car'] !!}</svg>
