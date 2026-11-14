<section class="service-detail-timeline member-service-timeline" aria-label="Status proses servis">
    @foreach(($executionTracking['stages'] ?? []) as $step)
        @php($status = $step['status'] ?? 'upcoming')
        <div class="service-detail-step is-{{ $status === 'completed' ? 'complete' : $status }}" @if($status === 'current') aria-current="step" @elseif($status === 'upcoming') aria-disabled="true" @endif>
            <span aria-hidden="true">{{ $loop->iteration }}</span>
            <strong>{{ $step['label'] }}</strong>
            @if($status === 'current' && ($executionTracking['current_stage'] ?? null) === 'customer_approval' && ($executionTracking['pending_approval_count'] ?? 0) > 0)
                <small>Menunggu keputusan Anda</small>
            @endif
        </div>
    @endforeach
</section>
