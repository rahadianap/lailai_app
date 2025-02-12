<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf as Pdf;
use App\Models\ReturJual;
use App\Models\Sales;

class ReturJualController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', ReturJual::class);

        $perPage = $request->input('per_page', 10);

        $data = ReturJual::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('ReturJual/Index', [
            'data' => $data,
        ]);
    }

    public function getSales(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Sales::where('kode_penjualan', 'like', "%{$search}%")
            // ->where('status', 'APPROVED')
            ->paginate($perPage);

        return response()->json($data);
    }

    public function fetchSalesDetails($id)
    {
        $data = Sales::join('trx_detail_penjualan', 'trx_penjualan.id', '=', 'trx_detail_penjualan.penjualan_id')->where('penjualan_id', $id)->get();

        return response()->json($data);
    }
}
