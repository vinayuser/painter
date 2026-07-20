@props(['order'])

<div class="timer-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:20px;">
    <div class="timer-card {{ $order->isPackingTimerActive() ? 'timer-active' : 'timer-done' }}" data-timer="packing" data-deadline="{{ $order->packing_deadline_at?->toIso8601String() }}" data-active="{{ $order->isPackingTimerActive() ? '1' : '0' }}">
        <div class="timer-label">Vendor Packing Timer</div>
        <div class="timer-value" id="packing-timer-{{ $order->id }}">
            @if($order->vendor_packing_status->value === 'packed')
                <span class="badge badge-green">Packed</span>
            @elseif($order->isPackingTimerActive())
                <span class="countdown">--:--</span>
            @else
                <span class="badge badge-red">Expired</span>
            @endif
        </div>
        <div class="timer-meta">
            Deadline: {{ $order->packing_deadline_at?->format('M d, H:i') ?? '-' }}
            @if($order->packed_at)
                · Packed at {{ $order->packed_at->format('M d, H:i') }}
            @endif
        </div>
    </div>

    <div class="timer-card {{ $order->isDeliveryTimerActive() ? 'timer-active' : 'timer-done' }}" data-timer="delivery" data-deadline="{{ $order->delivery_deadline_at?->toIso8601String() }}" data-active="{{ $order->isDeliveryTimerActive() ? '1' : '0' }}">
        <div class="timer-label">Delivery Timer</div>
        <div class="timer-value" id="delivery-timer-{{ $order->id }}">
            @if($order->order_status->value === 'delivered')
                <span class="badge badge-green">Delivered</span>
            @elseif($order->isDeliveryTimerActive())
                <span class="countdown">--:--</span>
            @else
                <span class="badge badge-red">Expired</span>
            @endif
        </div>
        <div class="timer-meta">
            Deadline: {{ $order->delivery_deadline_at?->format('M d, H:i') ?? '-' }}
            @if($order->delivered_at)
                · Delivered at {{ $order->delivered_at->format('M d, H:i') }}
            @endif
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.querySelectorAll('[data-timer]').forEach(function(card) {
    if (card.dataset.active !== '1') return;
    var deadline = new Date(card.dataset.deadline);
    var countdownEl = card.querySelector('.countdown');
    if (!countdownEl) return;

    function tick() {
        var diff = Math.max(0, Math.floor((deadline - new Date()) / 1000));
        var m = String(Math.floor(diff / 60)).padStart(2, '0');
        var s = String(diff % 60).padStart(2, '0');
        countdownEl.textContent = m + ':' + s;
        if (diff <= 0) {
            countdownEl.textContent = '00:00';
            card.classList.remove('timer-active');
            card.classList.add('timer-expired');
            clearInterval(interval);
        }
    }
    tick();
    var interval = setInterval(tick, 1000);
});
</script>
@endpush
@endonce
