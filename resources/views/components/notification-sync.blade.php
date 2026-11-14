@auth
@php
    $syncState = $notificationState ?? app(\App\Services\NotificationStateService::class)->summary(auth()->user());
    $syncState['account_id'] = (int) auth()->id();
    $syncState['sync_url'] = route('notifications.sync');
@endphp
<script type="application/json" data-notification-state>@json($syncState)</script>
<script src="{{ asset('assets/js/notification-sync.js') }}?v={{ filemtime(public_path('assets/js/notification-sync.js')) }}"></script>
@endauth
