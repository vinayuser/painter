@extends('vendor.layout')

@section('title', 'Notifications')

@section('content')
<div class="dash-header">
    <div>
        <h1 class="dash-title">Notifications
            @if($unreadCount > 0)
                <span class="badge" style="background:#dc2626;color:#fff;border-radius:999px;padding:2px 8px;font-size:12px;">{{ $unreadCount }}</span>
            @endif
        </h1>
        <p class="dash-subtitle">New orders, packing reminders, and delivery updates</p>
    </div>
</div>

<div class="card" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    @include('partials.notification-inbox', [
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
        'panel' => 'vendor',
        'filter' => $filter,
        'markAllRoute' => route('vendor.notifications.read-all'),
        'markReadRouteName' => 'vendor.notifications.read',
    ])
</div>
@endsection
