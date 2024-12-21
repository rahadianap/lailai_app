<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchasingController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);

        $data = Purchasing::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('Purchasing/Index', [
            'data' => $data,
        ]);
    }

    public function getSuppliers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Supplier::where('nama_supplier', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }
}
