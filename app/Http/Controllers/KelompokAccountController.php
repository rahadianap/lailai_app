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

    public function store(Request $request)
    {
        $this->authorize('create', KelompokAccount::class);

        $validator = Validator::make($request->all(), [
            'kelompok' => 'required|string|max:50',
            'nama_kelompok_account' => 'required|string|max:255',
            'jenis_kelompok_account' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            KelompokAccount::create([
                'kode_kelompok_account' => $this->getKodeKelompokAccount(),
                'kelompok' => $request->kelompok,
                'nama_kelompok_account' => $request->nama_kelompok_account,
                'jenis_kelompok_account' => $request->jenis_kelompok_account,
                'created_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Kelompok account Create Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the data', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $group = KelompokAccount::findOrFail($id);

        return response()->json(['data' => $group]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', KelompokAccount::class);

        $validator = Validator::make($request->all(), [
            'kelompok' => 'required|string|max:50',
            'nama_kelompok_account' => 'required|string|max:255',
            'jenis_kelompok_account' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = KelompokAccount::findOrFail($id);
            $data->update([
                'kelompok' => $request->kelompok,
                'nama_kelompok_account' => $request->nama_kelompok_account,
                'jenis_kelompok_account' => $request->jenis_kelompok_account,
                'updated_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'KelompokAccount Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getKodeKelompokAccount()
    {
        try {
            $id = 'COAG-001';
            $maxId = KelompokAccount::withTrashed()->where('kode_kelompok_account', 'LIKE', 'COAG-%')->max('kode_kelompok_account');
            if (!$maxId) {
                $id = 'COAG-001';
            } else {
                $maxId = str_replace('COAG-', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'COAG-00' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'COAG-0' . $count;
                } else {
                    $id = 'COAG-' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'COAG-' . Str::uuid()->toString();
        }
    }
}
