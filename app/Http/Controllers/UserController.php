<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roles = ['user', 'manager', 'admin']; // Add or modify roles as needed
        $permissions = [
            'products_view' => true,
            'products_create' => true,
            'products_edit' => true,
            'products_delete' => true,
            'pos_view' => true,
            'pos_create' => true,
            'pos_edit' => true,
            'pos_delete' => true,
            'po_view' => true,
            'po_create' => true,
            'po_edit' => true,
            'po_delete' => true,
            'purchasing_view' => true,
            'purchasing_create' => true,
            'purchasing_edit' => true,
            'purchasing_delete' => true,
            'vouchers_view' => true,
            'vouchers_create' => true,
            'vouchers_edit' => true,
            'vouchers_delete' => true,
            'members_view' => true,
            'members_create' => true,
            'members_edit' => true,
            'members_delete' => true,
        ];

        return Inertia::render('Users/Index', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['user', 'manager', 'admin'])],
            'permissions' => ['array'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // Update permissions
        $user->permissions = $validated['permissions'];
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