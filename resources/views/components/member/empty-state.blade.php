@props(['icon' => '✦', 'title' => 'Belum ada data', 'description' => '', 'action' => null, 'route' => null])
<div class="member-empty">
    <span class="member-empty-icon" aria-hidden="true">{{ $icon }}</span>
    <strong>{{ $title }}</strong>
    @if($description)<span>{{ $description }}</span>@endif
    @if($action && $route)<a class="yza-button" href="{{ $route }}">{{ $action }}</a>@endif
</div>
