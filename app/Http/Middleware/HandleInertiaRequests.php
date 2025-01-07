<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'user' => $this->getUserData(),
            'permissions' => $this->getPermissions(),
        ]);
    }

    /**
     * Get user data if authenticated.
     *
     * @return array|null
     */
    private function getUserData(): ?array
    {
        if (!auth()->check()) {
            return null;
        }

        return [
            'name' => auth()->user()->name,
            'role' => auth()->user()->role,
        ];
    }

    /**
     * Get permissions based on user role.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        if (!auth()->check()) {
            return [
                'products_view' => false,
                'products_create' => false,
                'products_edit' => false,
                'products_delete' => false,
                'pos_view' => false,
                'pos_create' => false,
                'pos_edit' => false,
                'pos_delete' => false,
                'po_view' => false,
                'po_create' => false,
                'po_edit' => false,
                'po_delete' => false,
                'purchasing_view' => false,
                'purchasing_create' => false,
                'purchasing_edit' => false,
                'purchasing_delete' => false,
                'vouchers_view' => false,
                'vouchers_create' => false,
                'vouchers_edit' => false,
                'vouchers_delete' => false,
                'members_view' => false,
                'members_create' => false,
                'members_edit' => false,
                'members_delete' => false,
            ];
        }

        $role = auth()->user()->role;

        return [
            'products_view' => in_array($role, ['admin', 'manager']),
            'products_create' => in_array($role, ['admin', 'manager']),
            'products_edit' => $role === 'admin',
            'products_delete' => $role === 'admin',
            'pos_view' => in_array($role, ['kasir', 'admin', 'manager']),
            'pos_create' => in_array($role, ['kasir', 'admin', 'manager']),
            'pos_edit' => $role === 'admin',
            'pos_delete' => $role === 'admin',
            'po_view' => in_array($role, ['admin', 'manager']),
            'po_create' => in_array($role, ['admin', 'manager']),
            'po_edit' => $role === 'admin',
            'po_delete' => $role === 'admin',
            'purchasing_view' => in_array($role, ['admin', 'manager']),
            'purchasing_create' => in_array($role, ['admin', 'manager']),
            'purchasing_edit' => $role === 'admin',
            'purchasing_delete' => $role === 'admin',
            'vouchers_view' => in_array($role, ['admin', 'manager']),
            'vouchers_create' => in_array($role, ['admin', 'manager']),
            'vouchers_edit' => $role === 'admin',
            'vouchers_delete' => $role === 'admin',
            'members_view' => in_array($role, ['admin', 'manager']),
            'members_create' => in_array($role, ['admin', 'manager']),
            'members_edit' => $role === 'admin',
            'members_delete' => $role === 'admin',
        ];
    }
}
