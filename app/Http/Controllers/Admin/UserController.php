<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = User::with('country');
        
        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
        
        // Role Filter
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $users = $query->latest()->paginate(10);
        
        // Metrics
        $startOfMonth = now()->startOfMonth();
        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startOfMonth)->count();
        
        $activeUsers = User::where('status', 'Active')->count();
        $newActiveUsers = User::where('status', 'Active')->where('created_at', '>=', $startOfMonth)->count();
        
        $inactiveUsers = User::where('status', 'Inactive')->count();
        $newInactiveUsers = User::where('status', 'Inactive')->where('created_at', '>=', $startOfMonth)->count();
        
        $adminUsers = User::where('role', 'admin')->count();
        $newAdmins = User::where('role', 'admin')->where('created_at', '>=', $startOfMonth)->count();

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'newUsers', 'adminUsers', 'newAdmins',
            'activeUsers', 'newActiveUsers', 'inactiveUsers', 'newInactiveUsers'
        ));
    }

    public function create()
    {
        $countries = \App\Models\Country::orderBy('name')->get();
        return view('admin.users.create', compact('countries'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:Active,Inactive',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
            'country_id' => $request->country_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $countries = \App\Models\Country::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'countries'));
    }

    public function update(\Illuminate\Http\Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:Active,Inactive',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $user->role = $request->role;
        $user->status = $request->status;
        $user->country_id = $request->country_id;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}