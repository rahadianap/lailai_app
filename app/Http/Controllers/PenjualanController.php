<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PenjualanController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', Penjualan::class);

        $perPage = $request->input('per_page', 10);

        $data = Penjualan::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('Penjualan/Index', [
            'data' => $data,
        ]);
    }
}
