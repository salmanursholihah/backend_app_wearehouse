<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">

        {{-- BRAND --}}
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}">Warehouse</a>
        </div>

        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('dashboard') }}">WH</a>
        </div>

        <ul class="sidebar-menu">

            {{-- DASHBOARD --}}
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fire"></i> <span>Dashboard</span>
                </a>
            </li>

            {{-- USER --}}
            <li class="menu-header">User Management</li>

            <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i> <span>Manajemen User</span>
                </a>
            </li>

            {{-- GUDANG --}}
            <li class="menu-header">Gudang</li>

            <li class="{{ request()->routeIs('product.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('product.index') }}">
                    <i class="fas fa-box"></i> <span>Produk</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('request.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('request.index') }}">
                    <i class="fas fa-clipboard-check"></i> <span>Approval Request</span>
                </a>
            </li>

            {{-- SYSTEM --}}
            <li class="menu-header">System</li>

            <li class="{{ request()->routeIs('logs.activity') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('logs.activity') }}">
                    <i class="fas fa-history"></i> <span>Activity Log</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('chat.index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('chat.index') }}">
                    <i class="fas fa-comments"></i> <span>Chat</span>
                </a>
            </li>

            {{-- LOGOUT --}}
            <li class="mt-4">
                <a class="nav-link text-danger" href="#"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </li>

        </ul>

    </aside>
</div>
