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
            <div class="sidebar-dropdown" x-data="{ open: {{ request()->routeIs('departments.*') || request()->routeIs('positions.*') || request()->routeIs('tax-tables.*') || request()->routeIs('holidays.*') || request()->routeIs('company-bank-details.*') ? 'true' : 'false' }} }">
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
                    @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('users.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-user-cog"></i>
                        <span>User Management</span>
                    </a>
                    @endif
                    <a href="{{ route('company-bank-details.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('company-bank-details.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-university"></i>
                        <span>Company Bank Details</span>
                    </a>
                    <a href="{{ route('tax-tables.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('tax-tables.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-calculator"></i>
                        <span>Tax Tables</span>
                    </a>
                    <a href="{{ route('holidays.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('holidays.*') ? 'active-sub' : '' }}">
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

        <!-- Attendance - Visible to ALL users -->
        <div class="nav-section">
            <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check"></i>
                <span>Attendance</span>
            </a>
        </div>

        <!-- Payroll Dropdown - Visible to ALL authenticated users -->
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

        <!-- Loan Dropdown - Visible to ALL users -->
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

        <!-- Reports Dropdown - Visible to ALL users -->
        <div class="nav-section">
            <div class="sidebar-dropdown" x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link" :class="{ 'active': open }">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                    <i class="fas fa-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                </button>
                <div x-show="open" x-collapse class="sidebar-submenu">
                    <a href="{{ route('reports.nasfund.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('reports.nasfund.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-university"></i>
                        <span>NASFUND Report</span>
                    </a>
                    <a href="{{ route('reports.swt.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('reports.swt.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        <span>SWT Report</span>
                    </a>
                    <a href="{{ route('reports.earnings.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('reports.earnings.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Summary of Earnings</span>
                    </a>
                    <a href="{{ route('reports.profile.index') }}" class="sidebar-link submenu-link {{ request()->routeIs('reports.profile.*') ? 'active-sub' : '' }}">
                        <i class="fas fa-user"></i>
                        <span>Employee Profile Report</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Leave Management -->
        <div class="nav-section">
            <a href="{{ route('leave.index') }}" class="sidebar-link {{ request()->routeIs('leave.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Leave Management</span>
            </a>
        </div>

        <!-- Backup -->
        <div class="nav-section">
            <a href="{{ route('backup.index') }}" class="sidebar-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                <i class="fas fa-database"></i>
                <span>Backup</span>
            </a>
        </div>
    </nav>

    <!-- Footer Section (Company Switcher + User) -->
    <div class="sidebar-footer">
       <!-- Company Switcher -->
        <div class="sidebar-company" x-data="{ open: false }">
            <button @click="open = !open" class="company-selector" type="button">
                <i class="fas fa-building"></i>
                {{-- 🌟 Updated to use our new smart name helper method --}}
                <span id="currentCompanyName">{{ auth()->user()->getCurrentCompanyName() }}</span>
                <i class="fas fa-chevron-down" :class="{ 'rotated': open }"></i>
            </button>
            
            <div x-show="open" 
                @click.away="open = false" 
                class="company-dropdown">
                @php
                    //  If Super Admin, fetch all companies from the database.
                    // Otherwise, pull only the limited companies explicitly assigned to the user profile.
                    $dropdownCompanies = auth()->user()->isSuperAdmin()
                        ? \App\Models\Company::where('is_active', true)->orderBy('name')->get()
                        : auth()->user()->companies;
                @endphp
                @forelse($dropdownCompanies as $company)
                    @php
                        $companyName = addslashes($company->name);
                    @endphp
                    <a href="#" 
                    class="company-item {{ auth()->user()->getCurrentCompanyId() == $company->id ? 'active' : '' }}" 
                    @click.prevent="open = false; switchCompany({{ $company->id }}, '{{ $companyName }}')">
                        {{ $company->name }}
                        {{--  Wrap pivot check in an if-statement so it doesn't crash on Super Admins --}}
                        @if(!auth()->user()->isSuperAdmin() && $company->pivot?->is_default)
                            <span class="text-xs text-blue-400 ml-1">(Default)</span>
                        @endif
                    </a>
                @empty
                    <span class="company-item text-gray-400">No companies assigned</span>
                @endforelse
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
                    <span class="user-role">
                        @if(auth()->user()->isSuperAdmin())
                            Super Admin
                        @else
                            {{ auth()->user()->roles->first()->name ?? 'User' }}
                        @endif
                    </span>
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

<script>
    function switchCompany(companyId, companyName) {
        // ✅ FIX: Use the full URL with the parameter
        const url = '{{ route("company.switch", ":company") }}'.replace(':company', companyId);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ company_id: companyId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('currentCompanyName').textContent = companyName;
                // Reload the page to refresh data
                window.location.reload();
            } else {
                alert('Failed to switch company: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error switching company:', error);
            alert('An error occurred while switching company.');
        });
    }
</script>