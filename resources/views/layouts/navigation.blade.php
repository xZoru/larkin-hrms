<!-- Sidebar Navigation -->
<div class="sidebar" x-data="{ open: false }">
    <!-- Sidebar Header / Logo -->
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <i class="fas fa-building" style="font-size: 22px;"></i>
            <span>Larkin Enterprises LTD</span>
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <div class="nav-section">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Management Dropdown -->
        <div class="nav-section">
            <div class="sidebar-dropdown" x-data="{ open: {{ request()->routeIs('departments.*') || request()->routeIs('positions.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link" :class="{ 'active': open }">
                    <i class="fas fa-cogs"></i>
                    <span>Management</span>
                    <i class="fas fa-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                </button>
                <div x-show="open" x-collapse class="sidebar-submenu">
                    <a href="{{ route('departments.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('departments.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-sitemap"></i>
                        <span>Departments</span>
                    </a>
                    <a href="{{ route('positions.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('positions.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-briefcase"></i>
                        <span>Positions / Designations</span>
                    </a>
                    <a href="#" class="sidebar-link submenu-link">
                        <i class="fas fa-user-cog"></i>
                        <span>User Management</span>
                    </a>
                    <a href="{{ route('company-bank-details.index') }}" class="sidebar-link submenu-link">
                        <i class="fas fa-university"></i>
                        <span>Company Bank Details</span>
                    </a>
                    <a href="{{ route('tax-tables.index') }}" class="sidebar-link submenu-link">
                        <i class="fas fa-calculator"></i>
                        <span>Tax Tables</span>
                    </a>
                    <a href="{{ route('holidays.index') }}" class="sidebar-link submenu-link">
                        <i class="fas fa-calendar-day"></i>
                        <span>Holidays</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Employees -->
        <div class="nav-section">
            <a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
        </div>

        <!-- Attendance -->
        @can('view attendance')
        <div class="nav-section">
            <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check"></i>
                <span>Attendance</span>
            </a>
        </div>
        @endcan

        <!-- Payroll Dropdown -->
        @can('view payroll')
        <div class="nav-section">
            <div class="sidebar-dropdown" x-data="{ open: {{ request()->routeIs('payroll.*') || request()->routeIs('aba.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link" :class="{ 'active': open }">
                    <i class="fas fa-wallet"></i>
                    <span>Payroll</span>
                    <i class="fas fa-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                </button>
                <div x-show="open" x-collapse class="sidebar-submenu">
                    <a href="{{ route('payroll.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('payroll.index') ? 'active-sub' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        <span>Payroll</span>
                    </a>
                    <a href="{{ route('payroll.summary') }}" class="sidebar-link submenu-link {{ request()->routeIs('payroll.summary') ? 'active-sub' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Payroll Summary</span>
                    </a>
                    <a href="{{ route('aba.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('aba.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-university"></i>
                        <span>ABA Bank File</span>
                    </a>
                    <a href="#" class="sidebar-link submenu-link">
                        <i class="fas fa-file-signature"></i>
                        <span>Final Pay Generator</span>
                    </a>
                    <a href="#" class="sidebar-link submenu-link">
                        <i class="fas fa-calendar-check"></i>
                        <span>Annual Leave Pay Generator</span>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Loan Dropdown -->
        @can('view loans')
        <div class="nav-section">
            <div class="sidebar-dropdown" x-data="{ open: {{ request()->routeIs('loan-requests.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link" :class="{ 'active': open }">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Loan</span>
                    <i class="fas fa-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                </button>
                <div x-show="open" x-collapse class="sidebar-submenu">
                    <a href="{{ route('loan-requests.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('loan-requests.index') ? 'active-sub' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Loan Request</span>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Reports -->
        @can('view reports')
        <div class="nav-section">
            <a href="#" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
        @endcan
    </nav>

    <!-- Footer Section (Company Switcher + User) -->
    <div class="sidebar-footer">
        <!-- Company Switcher - FIXED -->
        <div class="sidebar-company" x-data="{ open: false }">
            <button @click="open = !open" class="company-selector" type="button">
                <i class="fas fa-building"></i>
                <span id="currentCompanyName">{{ session('current_company_name', 'Select Company') }}</span>
                <i class="fas fa-chevron-down" :class="{ 'rotated': open }"></i>
            </button>
            
            <div x-show="open" 
                @click.away="open = false" 
                class="company-dropdown">
                @php
                    $companies = App\Models\Company::all();
                @endphp
                @foreach($companies as $company)
                    @php
                        // Escape single quotes for JavaScript
                        $companyName = addslashes($company->name);
                    @endphp
                    <a href="#" 
                    class="company-item {{ session('current_company_id') == $company->id ? 'active' : '' }}" 
                    @click.prevent="open = false; switchCompany({{ $company->id }}, '{{ $companyName }}')">
                        {{ $company->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- User Dropdown -->
        <div class="sidebar-user" x-data="{ open: false }">
            <button @click="open = !open" class="sidebar-user-btn">
                <div class="user-avatar">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <span class="user-role">Super Admin</span>
                </div>
                <i class="fas fa-chevron-down" :class="{ 'rotated': open }"></i>
            </button>
            <div x-show="open" @click.away="open = false" class="sidebar-user-menu">
                <a href="{{ route('profile.edit') }}" class="user-menu-item">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="user-menu-item logout">
                        <i class="fas fa-sign-out-alt"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>