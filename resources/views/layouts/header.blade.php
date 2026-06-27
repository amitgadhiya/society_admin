<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container-fluid px-4">

        {{-- Brand --}}
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <span class="navbar-brand-icon d-flex align-items-center justify-content-center rounded-2">
                <i class="bi bi-building"></i>
            </span>
            Society<span class="text-indigo">MS</span>
        </a>

        {{-- Toggler --}}
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            {{-- Nav links --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-grid me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('unit.*','unit-type.*','wing.*','society-summary.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi bi-building me-1"></i>Society
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('unit.*') ? 'active' : '' }}"
                               href="{{ route('unit.index') }}">
                                <i class="bi bi-house-door me-2"></i>Units
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('unit-type.*') ? 'active' : '' }}"
                               href="{{ route('unit-type.index') }}">
                                <i class="bi bi-diagram-3 me-2"></i>Unit Types
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('wing.*') ? 'active' : '' }}"
                               href="{{ route('wing.index') }}">
                                <i class="bi bi-buildings me-2"></i>Wings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('society-summary.*') ? 'active' : '' }}"
                               href="{{ route('society-summary.index') }}">
                                <i class="bi bi-clipboard2-data me-2"></i>Society Summary
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('watchman.*') ? 'active' : '' }}"
                       href="{{ route('watchman.index') }}">
                        <i class="bi bi-person-badge me-1"></i>Watchmen
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('task.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-clipboard-check me-1"></i>Tasks
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('task.index', 'task.create', 'task.show', 'task.edit') ? 'active' : '' }}"
                               href="{{ route('task.index') }}">
                                <i class="bi bi-list-ul me-2"></i>All Tasks
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('task.report') ? 'active' : '' }}"
                               href="{{ route('task.report') }}">
                                <i class="bi bi-bar-chart-line me-2"></i>Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('task.analysis') ? 'active' : '' }}"
                               href="{{ route('task.analysis') }}">
                                <i class="bi bi-funnel me-2"></i>Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('task.log') ? 'active' : '' }}"
                               href="{{ route('task.log') }}">
                                <i class="bi bi-calendar-check me-2"></i>Daily Log
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('visitor.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-lines-fill me-1"></i>Visitors
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('visitor.log') ? 'active' : '' }}"
                               href="{{ route('visitor.log') }}">
                                <i class="bi bi-journal-text me-2"></i>Visitor Log
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('maid.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-people me-1"></i>Maids
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('maid.index', 'maid.create', 'maid.show', 'maid.edit') ? 'active' : '' }}"
                               href="{{ route('maid.index') }}">
                                <i class="bi bi-list-ul me-2"></i>All Maids
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('maid.log') ? 'active' : '' }}"
                               href="{{ route('maid.log') }}">
                                <i class="bi bi-journal-text me-2"></i>Entry Log
                            </a>
                        </li>
                    </ul>
                </li>
                @yield('nav-extra')
            </ul>

            {{-- User info + logout --}}
            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-flex align-items-center gap-2">
                    <div class="navbar-avatar d-flex align-items-center justify-content-center rounded-circle bg-indigo text-white fw-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="lh-sm">
                        <div class="text-white fw-semibold" style="font-size:13px">{{ Auth::user()->name }}</div>
                        <div class="text-white-50" style="font-size:11px">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
