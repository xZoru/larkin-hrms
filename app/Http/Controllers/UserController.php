<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = auth()->user()->company_id;
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $query = User::with(['roles', 'companies']);
        
        // ✅ If Super Admin, show ALL users (no company filter)
        if (!$user->isSuperAdmin()) {
            $query->whereHas('companies', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        
        // FILTER: Employee Type (All, National, Expatriate)
        if ($request->employee_type && $request->employee_type !== 'all') {
            if ($request->employee_type === 'none') {
                $query->whereNull('employee_id');
            } else {
                $query->whereHas('employee', function($q) use ($request) {
                    $q->where('employee_type', $request->employee_type);
                });
            }
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->role) {
            $query->role($request->role);
        }
        
        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $users = $query->orderBy('name')->paginate(20);
        
        $roles = Role::all();
        $allCompanies = Company::where('is_active', true)->get();
        
        // ✅ Counts for Super Admin should show ALL users
        $totalUsers = $user->isSuperAdmin() 
            ? User::count() 
            : User::whereHas('companies', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->count();
        
        $activeUsers = $user->isSuperAdmin()
            ? User::where('is_active', true)->count()
            : User::whereHas('companies', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('is_active', true)->count();
        
        $inactiveUsers = $totalUsers - $activeUsers;
        
        return view('users.index', compact(
            'users',
            'roles',
            'allCompanies',
            'totalUsers',
            'activeUsers',
            'inactiveUsers'
        ));
    }

    public function create()
    {
        $roles = Role::all();
        $companies = Company::where('is_active', true)->get();
        
        return view('users.create', compact('roles', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:national,expatriate,all',
            'companies' => 'required|array|min:1',
            'companies.*' => 'exists:companies,id',
            'roles' => 'array',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'is_active' => $request->has('is_active'),
        ]);
        
        // ✅ Attach companies
        $companies = $request->companies;
        $defaultCompany = $request->default_company ?? $companies[0];
        
        $attachData = [];
        foreach ($companies as $companyId) {
            $attachData[$companyId] = ['is_default' => $companyId == $defaultCompany];
        }
        $user->companies()->attach($attachData);
        
        if ($request->roles) {
            $user->syncRoles($request->roles);
        }
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $this->authorizeUser($user);
        $user->load(['roles', 'companies']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorizeUser($user);
        
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        $companies = Company::where('is_active', true)->get();
        $userCompanies = $user->companies->pluck('id')->toArray();
        $defaultCompany = $user->companies()->wherePivot('is_default', true)->first();
        
        return view('users.edit', compact(
            'user', 
            'roles', 
            'userRoles', 
            'companies', 
            'userCompanies',
            'defaultCompany'
        ));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUser($user);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'user_type' => 'required|in:national,expatriate,all',
            'companies' => 'required|array|min:1',
            'companies.*' => 'exists:companies,id',
            'roles' => 'array',
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'is_active' => $request->has('is_active'),
        ]);
        
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        
        // ✅ Sync companies
        $companies = $request->companies;
        $defaultCompany = $request->default_company ?? $companies[0];
        
        $syncData = [];
        foreach ($companies as $companyId) {
            $syncData[$companyId] = ['is_default' => $companyId == $defaultCompany];
        }
        $user->companies()->sync($syncData);
        
        if ($request->roles) {
            $user->syncRoles($request->roles);
        }
        
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeUser($user);
        
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        $user->companies()->detach();
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleActive(User $user)
    {
        $this->authorizeUser($user);
        
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }
        
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('users.index')
            ->with('success', "User {$status} successfully.");
    }

private function authorizeUser($user)
{
    // Super Admin can access any user
    if (auth()->user()->isSuperAdmin()) {
        return;
    }
    
    // Non-Super Admin must belong to the same company
    $companyId = auth()->user()->company_id;
    if (!$user->belongsToCompany($companyId)) {
        abort(403, 'Unauthorized access to this user.');
    }
}
}