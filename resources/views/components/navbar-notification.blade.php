    <!-- Single Notification Dropdown -->
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            @if(count($navbar_notifications))
            <span class="badge badge-warning navbar-badge">{{ count($navbar_notifications) }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <div class="dropdown-header">
                Notifications
                <div class="float-right">
                    <a href="#">Mark All As Read</a>
                </div>
            </div>
            @forelse($navbar_notifications as $notif)
            <a href="{{ route('notifications.read', $notif->id) }}" class="dropdown-item">
                <i class="fas fa-envelope mr-2"></i> {{ $notif->data['message'] }}
                <span class="float-right text-muted text-sm">{{ $notif->created_at->diffForHumans() }}</span>
            </a>
            @empty
            <span class="dropdown-item">Tidak ada notifikasi baru</span>
            @endforelse
            <div class="dropdown-footer text-center">
                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </li>