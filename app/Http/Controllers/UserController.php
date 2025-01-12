<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    private $allPermissions = [
        'products_view',
        'products_create',
        'products_edit',
        'products_delete',
        'pos_view',
        'pos_create',
        'pos_edit',
        'pos_delete',
        'po_view',
        'po_create',
        'po_edit',
        'po_delete',
        'purchasing_view',
        'purchasing_create',
        'purchasing_edit',
        'purchasing_delete',
        'vouchers_view',
        'vouchers_create',
        'vouchers_edit',
        'vouchers_delete',
        'members_view',
        'members_create',
        'members_edit',
        'members_delete',
        'categories_view',
        'categories_create',
        'categories_edit',
        'categories_delete',
        'units_view',
        'units_create',
        'units_edit',
        'units_delete',
        'suppliers_view',
        'suppliers_create',
        'suppliers_edit',
        'suppliers_delete',
        'kelompok_account_view',
        'kelompok_account_create',
        'kelompok_account_edit',
        'kelompok_account_delete',
        'users_view',
        'users_create',
        'users_edit',
        'users_delete',
    ];

    private $rolePermissions = [
        'admin' => [
            'products_view',
            'products_create',
            'products_edit',
            'products_delete',
            'pos_view',
            'pos_create',
            'pos_edit',
            'pos_delete',
            'po_view',
            'po_create',
            'po_edit',
            'po_delete',
            'purchasing_view',
            'purchasing_create',
            'purchasing_edit',
            'purchasing_delete',
            'vouchers_view',
            'vouchers_create',
            'vouchers_edit',
            'vouchers_delete',
            'members_view',
            'members_create',
            'members_edit',
            'members_delete',
            'categories_view',
            'categories_create',
            'categories_edit',
            'categories_delete',
            'units_view',
            'units_create',
            'units_edit',
            'units_delete',
            'suppliers_view',
            'suppliers_create',
            'suppliers_edit',
            'suppliers_delete',
            'kelompok_account_view',
            'kelompok_account_create',
            'kelompok_account_edit',
            'kelompok_account_delete',
            'users_view',
            'users_create',
            'users_edit',
            'users_delete',
        ],
        'manager' => [
            'products_view',
            'products_create',
            'pos_view',
            'pos_create',
            'po_view',
            'po_create',
            'purchasing_view',
            'purchasing_create',
            'vouchers_view',
            'vouchers_create',
            'members_view',
            'members_create',
            'categories_view',
            'categories_create',
            'units_view',
            'units_create',
            'suppliers_view',
            'suppliers_create',
            'kelompok_account_view',
            'kelompok_account_create',
            'users_view',
            'users_create',
        ],
        'kasir' => [
            'pos_view',
        ],
    ];

    public function index()
    {
        $user = auth()->user();
        $roles = ['kasir', 'manager', 'admin'];

        return Inertia::render('Users/Index', [
            'user' => $user,
            'roles' => $roles,
            'allPermissions' => array_fill_keys($this->allPermissions, true),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['kasir', 'manager', 'admin'])],
            'permissions' => ['array'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $request->email,
            'role' => $validated['role'],
        ]);

        // Update permissions based on role and user selection
        $roleBasedPermissions = $this->rolePermissions[$validated['role']];
        $userSelectedPermissions = array_keys(array_filter($validated['permissions']));
        $finalPermissions = array_intersect($roleBasedPermissions, $userSelectedPermissions);

        $user->permissions = array_fill_keys($finalPermissions, true);
        $user->save();

        return redirect()->back()->with('success', 'User settings updated successfully.');
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    public function destroy(User $user)
    {
        // Add any necessary checks before deleting the user
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
