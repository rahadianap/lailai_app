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

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'user' => [
                'name' => auth()->user()->name ?? '',
                'role' => auth()->user()->role ?? '',
            ],
            'permissions' => [
                'products_view' => in_array(auth()->user()->role, ['admin', 'manager']),
                'products_create' => in_array(auth()->user()->role, ['admin', 'manager']),
                'products_edit' => in_array(auth()->user()->role, ['admin']),
                'products_delete' => in_array(auth()->user()->role, ['admin']),
                'pos_view' => in_array(auth()->user()->role, ['kasir', 'admin', 'manager']),
                'pos_create' => in_array(auth()->user()->role, ['kasir', 'admin', 'manager']),
                'pos_edit' => in_array(auth()->user()->role, ['admin']),
                'pos_delete' => in_array(auth()->user()->role, ['admin']),
                'po_view' => in_array(auth()->user()->role, ['admin', 'manager']),
                'po_create' => in_array(auth()->user()->role, ['admin', 'manager']),
                'po_edit' => in_array(auth()->user()->role, ['admin']),
                'po_delete' => in_array(auth()->user()->role, ['admin']),
                'purchasing_view' => in_array(auth()->user()->role, ['admin', 'manager']),
                'purchasing_create' => in_array(auth()->user()->role, ['admin', 'manager']),
                'purchasing_edit' => in_array(auth()->user()->role, ['admin']),
                'purchasing_delete' => in_array(auth()->user()->role, ['admin']),
                'vouchers_view' => in_array(auth()->user()->role, ['admin', 'manager']),
                'vouchers_create' => in_array(auth()->user()->role, ['admin', 'manager']),
                'vouchers_edit' => in_array(auth()->user()->role, ['admin']),
                'vouchers_delete' => in_array(auth()->user()->role, ['admin']),
                'members_view' => in_array(auth()->user()->role, ['admin', 'manager']),
                'members_create' => in_array(auth()->user()->role, ['admin', 'manager']),
                'members_edit' => in_array(auth()->user()->role, ['admin']),
                'members_delete' => in_array(auth()->user()->role, ['admin']),
            ],
        ]);
    }
}
