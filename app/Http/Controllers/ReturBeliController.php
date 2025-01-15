<?php

namespace App\Http\Controllers;

use App\Models\ReturBeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
}
