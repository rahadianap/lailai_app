<?php

namespace App\Http\Controllers;

use App\Models\MutasiMasuk;
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

class MutasiMasukController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', MutasiMasuk::class);

        $perPage = $request->input('per_page', 10);

        $data = MutasiMasuk::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('MutasiMasuk/Index', [
            'data' => $data,
        ]);
    }
}
