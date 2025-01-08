<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'user' => [
                'name' => auth()->user()->name ?? '',
                'role' => auth()->user()->role ?? '',
            ],
            'permissions' => $this->getPermissions(),
        ]);
    }

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

    private function getDefaultPermissions($value): array
    {
        return [
            'products_view' => $value,
            'products_create' => $value,
            'products_edit' => $value,
            'products_delete' => $value,
            'pos_view' => $value,
            'pos_create' => $value,
            'pos_edit' => $value,
            'pos_delete' => $value,
            'po_view' => $value,
            'po_create' => $value,
            'po_edit' => $value,
            'po_delete' => $value,
            'purchasing_view' => $value,
            'purchasing_create' => $value,
            'purchasing_edit' => $value,
            'purchasing_delete' => $value,
            'vouchers_view' => $value,
            'vouchers_create' => $value,
            'vouchers_edit' => $value,
            'vouchers_delete' => $value,
            'members_view' => $value,
            'members_create' => $value,
            'members_edit' => $value,
            'members_delete' => $value,
        ];
    }

    private function getManagerPermissions(): array
    {
        $permissions = $this->getDefaultPermissions(true);
        $permissions['products_delete'] = false;
        $permissions['pos_delete'] = false;
        $permissions['po_delete'] = false;
        $permissions['purchasing_delete'] = false;
        $permissions['vouchers_delete'] = false;
        $permissions['members_delete'] = false;
        return $permissions;
    }

    private function getKasirPermissions(): array
    {
        $permissions = $this->getDefaultPermissions(false);
        $permissions['pos_view'] = true;
        return $permissions;
    }

    private function getPermissions(): array
    {
        if (!auth()->check()) {
            return $this->getDefaultPermissions(false);
        }

        $role = auth()->user()->role;

        switch ($role) {
            case 'admin':
                return $this->getDefaultPermissions(true);
            case 'manager':
                return $this->getManagerPermissions();
            case 'kasir':
                return $this->getKasirPermissions();
            default:
                return $this->getDefaultPermissions(false);
        }
    }
}
