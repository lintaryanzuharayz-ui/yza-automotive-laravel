@props(['name' => 'car'])
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($name)
@case('car')<path d="m3 11 3-6h12l3 6v8h-3v-2H6v2H3Zm0 0h18M7 14h1m8 0h1M7 5l1-2h8l1 2"/>@break
@case('calendar')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18m-13 4h3m-3 3h3"/><circle cx="17" cy="17" r="3"/>@break
@case('coins')<ellipse cx="15" cy="5" rx="6" ry="3"/><path d="M9 5v5c0 4 12 4 12 0V5M9 10v5c0 4 12 4 12 0v-5M9 15v4c0 4 12 4 12 0v-4"/><path d="M9 10c-8-2-10 4-4 6m-3-3v5c0 3 4 4 7 3"/>@break
@case('tag')<path d="M13 3h8v8L10 22 2 14Z"/><circle cx="17" cy="7" r="1"/>@break
@case('diamond')<path d="m3 8 4-5h10l4 5-9 13Zm0 0h18M7 3l5 18 5-18M8 8l4-5 4 5"/>@break
@case('crown')<path d="m3 7 5 4 4-7 4 7 5-4-2 13H5Z"/>@break
@case('tools')<path d="m4 3 5 5-2 2-5-5m6 4 12 12m-4-16a5 5 0 0 0-6 6L3 18a2 2 0 0 0 3 3l7-7a5 5 0 0 0 7-6l-4 3-3-3 3-4Z"/>@break
@case('wheel')<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="m3 9 6 2m6 0 6-2m-9 6v6M6 5l3 4m9-4-3 4"/>@break
@case('wash')<path d="M3 17v4m5-4v4m8-4v4m5-4v4M4 17l2-6h12l2 6ZM7 8V4m5 4V2m5 6V4M3 8l1 1m17-1-1 1"/>@break
@case('trade')<path d="M3 7h18m-5-5 5 5-5 5M21 17H3m5-5-5 5 5 5"/>@break
@case('palette')<path d="M12 3a9 9 0 1 0 0 18h1a2 2 0 0 0 1-3.7 1.7 1.7 0 0 1 1-3.1h3A3 3 0 0 0 21 11a9 9 0 0 0-9-8Z"/><circle cx="7" cy="10" r=".8"/><circle cx="10" cy="6.5" r=".8"/><circle cx="15" cy="6.5" r=".8"/>@break
@endswitch
</svg>
