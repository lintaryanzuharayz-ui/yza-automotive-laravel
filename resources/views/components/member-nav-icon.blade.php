@props(['name'])
<svg class="member-reference-icon" viewBox="0 0 80 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    @switch($name)
        @case('car')
            <path d="m15 25 7-14a5 5 0 0 1 4-2h28a5 5 0 0 1 4 2l7 14M14 27l-6 6v18h9v-6h46v6h9V33l-6-6H14Z"/>
            <path d="m18 26 6-12h32l6 12H18Zm-7 9 13 4m45-4-13 4M26 40h28M14 45h52"/>
            <path class="member-icon-accent" d="M7 23h7m52 0h7M10 48v3h7m46 0h9v-3M27 33h26"/>
            @break
        @case('orders')
            <path d="M29 14H19a3 3 0 0 0-3 3v40h42V17a3 3 0 0 0-3-3H45M30 10V7h13v3m-16 0h19v10H27V10Z"/>
            <path d="M36 29h14M36 40h14M36 50h14"/>
            <path class="member-icon-accent" d="m23 29 3 3 5-6m-8 14 3 3 5-6m-8 13 3 3 5-6"/>
            @break
        @case('garage')
            <path class="member-icon-accent" d="M8 23 40 8l32 15M14 24v11m52-11v11"/>
            <path d="m20 36 6-10h28l6 10M18 37l-4 5v14h8v-5h36v5h8V42l-4-5H18Zm5 0h34M16 43l12 3m36-3-12 3M30 47h20M17 51h46"/>
            <path class="member-icon-accent" d="M32 40h16"/>
            @break
        @case('tools')
            <path d="M27 11a13 13 0 0 1 15 17l25 25a5 5 0 0 1-7 7L35 35a13 13 0 0 1-17-16l8 8 8-8-7-8Z" fill="#f7f7f8" stroke="#f7f7f8"/>
            <path d="m59 9 7-3 4 4-3 7-8 4-5-5 5-7ZM53 20 18 55a4 4 0 0 1-6-6l35-35" fill="#f7f7f8"/>
            <path d="m24 38 10 10" stroke="#0b0d10" stroke-width="5"/>
            <path class="member-icon-accent" d="m49 39 14 14m-7-10 10 10"/>
            <circle cx="63" cy="56" r="1" fill="#0b0d10" stroke="none"/>
            @break
        @case('activity')
            <rect x="13" y="15" width="52" height="43" rx="5"/>
            <path d="M13 27h52M25 9v14M53 9v14"/>
            <path class="member-icon-accent" d="m40 30 4.3 8.6 9.5 1.4-6.9 6.7 1.6 9.4-8.5-4.4-8.5 4.4 1.6-9.4-6.9-6.7 9.5-1.4L40 30Z" fill="#f7f7f8"/>
            @break
        @case('benefits')
            <path class="member-icon-accent" d="M8 24h64M25 10l15 46L55 10M8 24l32 33 32-33"/>
            <path d="m8 24 13-14h38l13 14-32 33L8 24Zm0 0h64M21 10l19 14 19-14"/>
            @break
    @endswitch
</svg>
