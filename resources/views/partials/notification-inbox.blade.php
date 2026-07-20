@php
    use App\Support\NotificationPresenter;
@endphp

<div class="notification-toolbar" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem;">
    <a href="{{ request()->url() }}" class="btn btn-sm {{ ($filter ?? null) !== 'unread' ? 'btn-primary' : 'btn-default' }}">All</a>
    <a href="{{ request()->fullUrlWithQuery(['filter' => 'unread', 'page' => null]) }}" class="btn btn-sm {{ ($filter ?? null) === 'unread' ? 'btn-primary' : 'btn-default' }}">
        Unread @if(($unreadCount ?? 0) > 0)<span class="badge badge-danger">{{ $unreadCount }}</span>@endif
    </a>
    @if(($unreadCount ?? 0) > 0)
        <form method="POST" action="{{ $markAllRoute }}" style="margin-left:auto;">
            @csrf
            <button type="submit" class="btn btn-sm btn-default">Mark all as read</button>
        </form>
    @endif
</div>

<div class="notification-list">
    @forelse($notifications as $item)
        @php
            $link = NotificationPresenter::link($item, $panel);
            $isUnread = is_null($item->read_at);
        @endphp
        <div class="notification-item {{ $isUnread ? 'is-unread' : '' }}">
            <div class="notification-icon">
                <i class="fas {{ NotificationPresenter::icon($item->type) }}"></i>
            </div>
            <div class="notification-body">
                <div class="notification-meta">
                    <span class="badge {{ NotificationPresenter::badgeClass($item->type) }}">{{ NotificationPresenter::typeLabel($item->type) }}</span>
                    <span class="notification-time">{{ $item->created_at->diffForHumans() }}</span>
                    @if($isUnread)
                        <span class="badge badge-danger">New</span>
                    @endif
                </div>
                <h4 class="notification-title">{{ $item->title }}</h4>
                <p class="notification-text">{{ $item->body }}</p>
                <div class="notification-actions">
                    @if($link)
                        <a href="{{ $link }}" class="btn btn-xs btn-info">View details</a>
                    @endif
                    @if($isUnread)
                        <form method="POST" action="{{ route($markReadRouteName, $item) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-default">Mark read</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="notification-empty">
            <i class="far fa-bell-slash"></i>
            <p>No notifications yet.</p>
        </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="mt-3">{{ $notifications->links() }}</div>
@endif
