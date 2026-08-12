<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ asset('admin/dist/img/AdminLTELogo.png') }}" alt="Pension Fund ERP" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Pension Fund ERP</span>
    </a>

    <div class="sidebar">
        @auth
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ asset('admin/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                </div>

                <div class="info">
                    <a href="{{ route('profile.edit') }}" class="d-block">
                        {{ auth()->user()->fullName() ?: auth()->user()->name }}
                    </a>

                    <small class="text-light">
                        @if(auth()->user()->is_ceo)
                            CEO
                        @elseif(auth()->user()->is_admin)
                            Administrator
                        @elseif(auth()->user()->is_hr)
                            HR
                        @elseif(auth()->user()->is_head_of_department)
                            Head of Department
                        @elseif(auth()->user()->is_head_of_section)
                            Head of Section
                        @else
                            User
                        @endif
                    </small>
                </div>
            </div>
        @endauth

        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">

                <div class="input-group-append">
                    <button class="btn btn-sidebar" type="button">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        @php
            $userManagementOpen =
                request()->routeIs('users.*') ||
                request()->routeIs('departments.*') ||
                request()->routeIs('sections.*');

            $performanceManagementOpen =
                request()->routeIs('performance-targets.*') ||
                request()->routeIs('performance-target-periods.*') ||
                request()->routeIs('performance-rating-scales.*') ||
                request()->routeIs('performance-assessments.*');
        @endphp

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" role="menu" data-accordion="false">

                {{-- ========================================================= --}}
                {{-- DASHBOARD --}}
                {{-- ========================================================= --}}

                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @auth

                    {{-- ===================================================== --}}
                    {{-- HR MANAGEMENT --}}
                    {{-- ===================================================== --}}

                    @if(auth()->user()->is_admin || auth()->user()->is_hr)

                        <li class="nav-header">HR MANAGEMENT</li>

                        <li class="nav-item has-treeview {{ $userManagementOpen ? 'menu-open' : '' }}">

                            <a href="#" class="nav-link sidebar-dropdown-toggle {{ $userManagementOpen ? 'active' : '' }}" data-target="user-management-menu">
                                <i class="nav-icon fas fa-users-cog"></i>

                                <p>
                                    User Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul id="user-management-menu" class="nav nav-treeview" style="{{ $userManagementOpen ? 'display:block;' : 'display:none;' }}">

                                <li class="nav-item">
                                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') || request()->routeIs('users.create') || request()->routeIs('users.edit') || request()->routeIs('users.show') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Users</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('users.deleted') }}" class="nav-link {{ request()->routeIs('users.deleted') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Deleted Users</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Departments</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('sections.index') }}" class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sections</p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                    @endif

                    {{-- ===================================================== --}}
                    {{-- PERFORMANCE MANAGEMENT --}}
                    {{-- ===================================================== --}}

                    <li class="nav-header">PERFORMANCE MANAGEMENT</li>

                    <li class="nav-item has-treeview {{ $performanceManagementOpen ? 'menu-open' : '' }}">

                        <a href="#" class="nav-link sidebar-dropdown-toggle {{ $performanceManagementOpen ? 'active' : '' }}" data-target="performance-management-menu">

                            <i class="nav-icon fas fa-chart-line"></i>

                            <p>
                                Performance
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul id="performance-management-menu" class="nav nav-treeview" style="{{ $performanceManagementOpen ? 'display:block;' : 'display:none;' }}">

                            {{-- PERFORMANCE TARGETS --}}

                            <li class="nav-item">
                                <a href="{{ route('performance-targets.index') }}" class="nav-link {{ request()->routeIs('performance-targets.*') ? 'active' : '' }}">
                                    <i class="fas fa-bullseye nav-icon"></i>
                                    <p>Performance Targets</p>
                                </a>
                            </li>

                            {{-- MY ASSESSMENTS --}}

                            <li class="nav-item">
                                <a href="{{ route('performance-assessments.index', ['filter' => 'my_assessments']) }}" class="nav-link {{ request()->routeIs('performance-assessments.*') && request('filter') === 'my_assessments' ? 'active' : '' }}">
                                    <i class="fas fa-clipboard-check nav-icon"></i>
                                    <p>My Assessments</p>
                                </a>
                            </li>

                            {{-- ALL ASSESSMENTS USER CAN ACCESS --}}

                            <li class="nav-item">
                                <a href="{{ route('performance-assessments.index') }}" class="nav-link {{ request()->routeIs('performance-assessments.index') && empty(request('filter')) ? 'active' : '' }}">
                                    <i class="fas fa-file-signature nav-icon"></i>
                                    <p>Performance Assessments</p>
                                </a>
                            </li>

                            {{-- ASSESSOR WORK --}}

                            <li class="nav-item">
                                <a href="{{ route('performance-assessments.index', ['filter' => 'awaiting_assessor']) }}" class="nav-link {{ request('filter') === 'awaiting_assessor' ? 'active' : '' }}">
                                    <i class="fas fa-user-check nav-icon"></i>
                                    <p>Awaiting My Assessment</p>
                                </a>
                            </li>

                            {{-- REVIEWER WORK --}}

                            <li class="nav-item">
                                <a href="{{ route('performance-assessments.index', ['filter' => 'awaiting_reviewer']) }}" class="nav-link {{ request('filter') === 'awaiting_reviewer' ? 'active' : '' }}">
                                    <i class="fas fa-eye nav-icon"></i>
                                    <p>Awaiting My Review</p>
                                </a>
                            </li>

                            {{-- COMPLETED --}}

                            <li class="nav-item">
                                <a href="{{ route('performance-assessments.index', ['filter' => 'completed']) }}" class="nav-link {{ request('filter') === 'completed' ? 'active' : '' }}">
                                    <i class="fas fa-check-double nav-icon"></i>
                                    <p>Completed Assessments</p>
                                </a>
                            </li>

                            {{-- ADMIN / HR SETTINGS --}}

                            @if(auth()->user()->is_admin || auth()->user()->is_hr)

                                <li class="nav-item">
                                    <a href="{{ route('performance-target-periods.index') }}" class="nav-link {{ request()->routeIs('performance-target-periods.*') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-alt nav-icon"></i>
                                        <p>Performance Periods</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('performance-rating-scales.index') }}" class="nav-link {{ request()->routeIs('performance-rating-scales.*') ? 'active' : '' }}">
                                        <i class="fas fa-star nav-icon"></i>
                                        <p>Rating Scales</p>
                                    </a>
                                </li>

                            @endif

                        </ul>
                    </li>

                    {{-- ===================================================== --}}
                    {{-- MY ACCOUNT --}}
                    {{-- ===================================================== --}}

                    <li class="nav-header">MY ACCOUNT</li>

                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Profile</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('password.force.change') }}" class="nav-link {{ request()->routeIs('password.force.change') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Change Password</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">

                            <i class="nav-icon far fa-bell"></i>

                            <p>
                                Notifications

                                @if(auth()->user()->unreadNotifications->count() > 0)

                                    <span class="right badge badge-warning">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>

                                @endif
                            </p>
                        </a>
                    </li>

                    {{-- ===================================================== --}}
                    {{-- REPORTING LINE --}}
                    {{-- ===================================================== --}}

                    <li class="nav-header">REPORTING LINE</li>

                    <li class="nav-item">

                        <a href="javascript:void(0);" class="nav-link">

                            <i class="nav-icon fas fa-sitemap"></i>

                            <p>
                                @if(auth()->user()->supervisor)

                                    Supervisor:
                                    {{ auth()->user()->supervisor->fullName() ?: auth()->user()->supervisor->name }}

                                @else

                                    Supervisor: Not Assigned

                                @endif
                            </p>

                        </a>
                    </li>

                    <li class="nav-item">

                        <a href="javascript:void(0);" class="nav-link">

                            <i class="nav-icon fas fa-check-circle"></i>

                            <p>
                                @if(auth()->user()->reviewer)

                                    Reviewer:
                                    {{ auth()->user()->reviewer->fullName() ?: auth()->user()->reviewer->name }}

                                @else

                                    Reviewer: Not Assigned

                                @endif
                            </p>

                        </a>
                    </li>

                @endauth

            </ul>
        </nav>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleLinks = document.querySelectorAll('.sidebar-dropdown-toggle');

    toggleLinks.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId = this.getAttribute('data-target');
            const menu = document.getElementById(targetId);
            const parent = this.closest('.nav-item');

            if (!menu || !parent) {
                return;
            }

            const isOpen = parent.classList.contains('menu-open');

            if (isOpen) {
                parent.classList.remove('menu-open');
                this.classList.remove('active');
                menu.style.display = 'none';
            } else {
                parent.classList.add('menu-open');
                this.classList.add('active');
                menu.style.display = 'block';
            }
        });
    });
});
</script>