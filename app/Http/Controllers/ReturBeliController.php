<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use App\Models\ReturBeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Supplier;
use App\Models\Product;

class ReturBeliController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', ReturBeli::class);

        $perPage = $request->input('per_page', 10);

        $data = ReturBeli::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('ReturBeli/Index', [
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

    public function getPurchasing(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Purchasing::where('kode_pembelian', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getProducts(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Product::where('kode_barcode', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function fetchDetails($id)
    {
        $data = Product::where('kode_barcode', $id)->first();

        return response()->json($data);
    }

    public function fetchPurchasingDetails($id)
    {
        $data = Purchasing::join('trx_detail_pembelian', 'trx_pembelian.id', '=', 'trx_detail_pembelian.pembelian_id')->where('pembelian_id', $id)->get();

        return response()->json($data);
    }
}
