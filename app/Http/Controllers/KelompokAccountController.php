<?php

namespace App\Http\Controllers;

use App\Models\KelompokAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class KelompokAccountController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $this->authorize('view', arguments: KelompokAccount::class);

        $perPage = $request->input('per_page', 10);

        $data = KelompokAccount::paginate(perPage: $perPage);

        return Inertia::render('KelompokAccount/Index', [
            'data' => $data,
        ]);
    }
}
