<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;

class SalesController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Sales/Index');
    }

    public function search(Request $request)
    {
        $query = Product::with('details')->where('is_aktif', 1);

        if ($request->has('search')) {
            $searchParams = $request->input('search');

            $query->where(function ($q) use ($searchParams) {
                foreach ($searchParams as $field => $value) {
                    $q->orWhere($field, 'LIKE', "%{$value}%");
                }
            });
        }

        $perPage = $request->input('per_page', 1);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function getByBarcode($barcode)
    {
        $product = Product::join('mst_detail_barang', 'mst_barang.id', '=', 'mst_detail_barang.barang_id')->where('mst_barang.kode_barcode', $barcode)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function getVouchers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Voucher::where('kode_voucher', 'like', "%{$search}%")
            ->where('status', 'AVAILABLE')
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getMembers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Member::where('kode_member', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }
}
