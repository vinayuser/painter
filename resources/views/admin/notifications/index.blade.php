@extends('admin.layout')

@section('title', 'Notifications')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-bell mr-2"></i>Notifications
        @if($unreadCount > 0)
            <span class="badge badge-danger">{{ $unreadCount }} unread</span>
        @endif
    </h1>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'inbox' ? 'active' : '' }}" href="{{ route('admin.notifications.index', ['tab' => 'inbox']) }}">
            <i class="fas fa-inbox mr-1"></i> My Inbox
            @if($unreadCount > 0)<span class="badge badge-danger">{{ $unreadCount }}</span>@endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'broadcast' ? 'active' : '' }}" href="{{ route('admin.notifications.index', ['tab' => 'broadcast']) }}">
            <i class="fas fa-bullhorn mr-1"></i> Broadcast
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'settings' ? 'active' : '' }}" href="{{ route('admin.notifications.index', ['tab' => 'settings']) }}">
            <i class="fas fa-cog mr-1"></i> Chrome Push
        </a>
    </li>
</ul>

@if($tab === 'inbox')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Order & system alerts</h3>
        </div>
        <div class="card-body">
            @include('partials.notification-inbox', [
                'notifications' => $notifications,
                'unreadCount' => $unreadCount,
                'panel' => 'admin',
                'filter' => $filter,
                'markAllRoute' => route('admin.notifications.read-all'),
                'markReadRouteName' => 'admin.notifications.read',
            ])
        </div>
    </div>
@elseif($tab === 'broadcast')
    <div class="row">
        <div class="col-md-5">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> Send Global Broadcast</h3>
                </div>
                <form method="POST" action="{{ route('admin.notifications.broadcast') }}">
                    @csrf
                    <div class="card-body">
                        <p class="text-muted text-sm">Sends to the FCM topic for that role and logs it here.</p>
                        <div class="form-group">
                            <label>Channel</label>
                            <select name="channel" class="form-control" required>
                                @foreach($topics as $topic)
                                    <option value="{{ $topic->value }}">{{ $topic->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" maxlength="120" required>
                        </div>
                        <div class="form-group mb-0">
                            <label>Message</label>
                            <textarea name="body" class="form-control" rows="3" maxlength="500" required></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Recent Broadcasts</h3></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr><th>When</th><th>Channel</th><th>Title</th><th>Message</th></tr>
                        </thead>
                        <tbody>
                            @forelse($broadcasts as $item)
                                <tr>
                                    <td class="text-sm text-muted">{{ $item->created_at->format('d M Y H:i') }}</td>
                                    <td><span class="badge badge-gray">{{ $item->channel }}</span></td>
                                    <td>{{ $item->title }}</td>
                                    <td class="text-sm">{{ $item->body }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No broadcasts yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card" style="max-width:640px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-desktop mr-1"></i> Chrome Notifications</h3>
        </div>
        <div class="card-body">
            @if(! $fcmReady)
                <div class="alert alert-warning">
                    Firebase credentials not configured. Set <code>FIREBASE_CREDENTIALS</code> in <code>.env</code>.
                </div>
            @endif
            <p class="text-sm text-muted mb-3">
                Enable once in this browser. New orders and admin alerts arrive as <strong>Chrome system notifications</strong>
                even when you are on another tab, another site, or this panel is in the background.
                Chrome itself needs to be running on this computer (the admin tab does not need to stay open).
            </p>
            <div class="mb-3">
                <button type="button" id="enable-web-push" class="btn btn-success btn-sm">
                    <i class="fas fa-bell"></i> Enable Chrome Notifications
                </button>
                <span id="push-status" class="ml-2 text-sm text-muted">
                    @if($hasBrowserToken) Token saved for this account @endif
                </span>
            </div>
            <hr>
            <p class="text-sm text-muted mb-2">
                Send a test push to <em>your</em> browser only.
            </p>
            <form method="POST" action="{{ route('admin.notifications.test-self') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm" @disabled(! $hasBrowserToken || ! $fcmReady)>
                    <i class="fas fa-vial"></i> Send test notification to me
                </button>
            </form>
            @if(! $hasBrowserToken)
                <p class="text-sm text-warning mt-2 mb-0">Enable Chrome notifications above before testing.</p>
            @endif
        </div>
    </div>
    @include('partials.web-push-enable', ['endpoint' => route('admin.fcm-token')])
@endif
@endsection
