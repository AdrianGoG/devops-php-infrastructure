<nav class="navbar navbar-expand-lg sticky-top app-nav py-2">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">
                <x-icon name="pipeline" :size="19" stroke-width="1.9" />
            </span>
            <span class="brand-text">
                <span>User Dashboard</span>
                <small>VM2 · app-user-dashboard</small>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto ms-lg-4 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
                       href="{{ route('profile.edit') }}">Profile</a>
                </li>
            </ul>

            <div class="dropdown mt-3 mt-lg-0">
                <button class="user-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar">{{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}</span>
                    <span>{{ Auth::user()->name }}</span>
                    <x-icon name="chevron-down" :size="14" />
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header text-truncate">{{ Auth::user()->email }}</li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile settings</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Log out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
