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
            'flash' => [
                'error' => fn() => $request->session()->get('error'),
            ],
            'user' => [
                'name' => auth()->user()->name ?? '',
                'role' => auth()->user()->role ?? '',
                'kode_toko' => auth()->user()->kode_toko ?? '',
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
            'kode_toko' => auth()->user()->kode_toko,
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
            'po_approve' => $value,
            'purchasing_view' => $value,
            'purchasing_create' => $value,
            'purchasing_edit' => $value,
            'purchasing_delete' => $value,
            'purchasing_approve' => $value,
            'retur_beli_view' => $value,
            'retur_beli_create' => $value,
            'retur_beli_edit' => $value,
            'retur_beli_delete' => $value,
            'retur_beli_approve' => $value,
            'retur_jual_view' => $value,
            'retur_jual_create' => $value,
            'retur_jual_edit' => $value,
            'retur_jual_delete' => $value,
            'retur_jual_approve' => $value,
            'vouchers_view' => $value,
            'vouchers_create' => $value,
            'vouchers_edit' => $value,
            'vouchers_delete' => $value,
            'members_view' => $value,
            'members_create' => $value,
            'members_edit' => $value,
            'members_delete' => $value,
            'categories_view' => $value,
            'categories_create' => $value,
            'categories_edit' => $value,
            'categories_delete' => $value,
            'stores_view' => $value,
            'stores_create' => $value,
            'stores_edit' => $value,
            'stores_delete' => $value,
            'units_view' => $value,
            'units_create' => $value,
            'units_edit' => $value,
            'units_delete' => $value,
            'suppliers_view' => $value,
            'suppliers_create' => $value,
            'suppliers_edit' => $value,
            'suppliers_delete' => $value,
            'kelompok_account_view' => $value,
            'kelompok_account_create' => $value,
            'kelompok_account_edit' => $value,
            'kelompok_account_delete' => $value,
            'account_view' => $value,
            'account_create' => $value,
            'account_edit' => $value,
            'account_delete' => $value,
            'users_view' => $value,
            'users_create' => $value,
            'users_edit' => $value,
            'users_delete' => $value,
            'penjualan_view' => $value,
            'penjualan_create' => $value,
            'penjualan_edit' => $value,
            'penjualan_delete' => $value,
            'mutasi_keluar_view' => $value,
            'mutasi_keluar_create' => $value,
            'mutasi_keluar_edit' => $value,
            'mutasi_keluar_delete' => $value,
            'mutasi_keluar_approve' => $value,
            'mutasi_masuk_view' => $value,
            'mutasi_masuk_create' => $value,
            'mutasi_masuk_edit' => $value,
            'mutasi_masuk_delete' => $value,
            'mutasi_masuk_approve' => $value,
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
        $permissions['categories_delete'] = false;
        $permissions['stores_delete'] = false;
        $permissions['units_delete'] = false;
        $permissions['suppliers_delete'] = false;
        $permissions['kelompok_account_delete'] = false;
        $permissions['account_delete'] = false;
        $permissions['users_delete'] = false;
        $permissions['retur_beli_delete'] = false;
        $permissions['penjualan_delete'] = false;
        $permissions['retur_jual_delete'] = false;
        $permissions['mutasi_keluar_delete'] = false;
        $permissions['mutasi_masuk_delete'] = false;

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
