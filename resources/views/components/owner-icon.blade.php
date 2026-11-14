@props(['name' => 'grid'])
<svg {{ $attributes->merge(['class' => 'oe-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('car')<path d="m5 7 2-3h10l2 3 2 3v7H3v-7Z M3 10h18 M7 17v3 M17 17v3 M6 13h2 M16 13h2"/>@break
@case('check')<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>@break
@case('wallet')<rect x="3" y="5" width="18" height="15" rx="3"/><path d="M3 9h18 M15 13h6v4h-6Z M6 5V3h12"/>@break
@case('orders')<path d="M8 5H5v16h14V5h-3 M9 3h6v4H9Z M8 12h8 M8 16h5"/>@break
@case('tool')<path d="M14 5a5 5 0 0 0-6 6L3 16a3 3 0 0 0 4 4l5-5a5 5 0 0 0 7-6l-3 3-4-4 3-3Z"/>@break
@case('users')<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3 M16 4a3 3 0 0 1 0 6 M18 14a5 5 0 0 1 3 4v3"/>@break
@case('chart')<path d="M4 3v17h17 M8 15l4-5 4 2 5-7"/>@break
@case('award')<circle cx="12" cy="8" r="5"/><path d="m8 12-2 9 6-3 6 3-2-9"/>@break
@case('bell')<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9 M10 21h4"/>@break
@case('arrow')<path d="M5 12h14 m-5-5 5 5-5 5"/>@break
@case('menu')<path d="M4 6h16 M4 12h16 M4 18h16"/>@break
@case('close')<path d="m6 6 12 12 M6 18 18 6"/>@break
@case('user')<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
@default<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
@endswitch
</svg>
