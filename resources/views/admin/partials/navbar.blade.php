<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <span class="nav-link text-muted">@yield('title', 'Dashboard')</span>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
        @php
            $navUnread = \App\Models\AppNotification::query()
                ->where('user_id', auth()->id())
                ->whereNull('read_at')
                ->count();
            $navLatest = \App\Models\AppNotification::query()
                ->where('user_id', auth()->id())
                ->latest()
                ->limit(5)
                ->get();
        @endphp
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" title="Notifications">
                <i class="far fa-bell"></i>
                @if($navUnread > 0)
                    <span class="badge badge-danger navbar-badge">{{ $navUnread > 9 ? '9+' : $navUnread }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $navUnread }} unread notification{{ $navUnread === 1 ? '' : 's' }}</span>
                <div class="dropdown-divider"></div>
                @forelse($navLatest as $n)
                    <a href="{{ route('admin.notifications.index', ['tab' => 'inbox']) }}" class="dropdown-item">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="dropdown-item-title text-sm {{ $n->read_at ? '' : 'font-weight-bold' }}">
                                    {{ \Illuminate\Support\Str::limit($n->title, 40) }}
                                    @unless($n->read_at)<span class="float-right text-sm text-danger"><i class="fas fa-circle" style="font-size:.55rem;"></i></span>@endunless
                                </h3>
                                <p class="text-sm text-muted mb-0">{{ $n->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                @empty
                    <span class="dropdown-item text-muted text-sm">No notifications yet</span>
                    <div class="dropdown-divider"></div>
                @endforelse
                <a href="{{ route('admin.notifications.index', ['tab' => 'inbox']) }}" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>
        <li class="nav-item d-none d-md-inline-block mr-2">
            <span class="nav-link text-muted"><i class="far fa-clock mr-1"></i>{{ now()->format('D, M j Y') }}</span>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-user"></i> {{ auth()->user()->name }}
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('admin.profile.edit') }}" class="dropdown-item"><i class="fas fa-user-cog mr-2"></i> My Profile</a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
