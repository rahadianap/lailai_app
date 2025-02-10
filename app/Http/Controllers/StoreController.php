<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StoreController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', Store::class);

        $perPage = $request->input('per_page', 10);

        $data = Store::paginate(perPage: $perPage);

        return Inertia::render('Stores/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Store::class);

        $validator = Validator::make($request->all(), [
            'nama_toko' => 'required|unique:mst_store,nama_toko',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            Store::create([
                'kode_toko' => $this->getKodeToko(),
                'nama_toko' => $request->nama_toko,
                'alamat_toko' => $request->alamat_toko,
                'created_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Store Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            // return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
            return Inertia::render('Error', ['errors' => $e->errors()])
                ->toResponse($request);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the category', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $store = Store::findOrFail($id);

        return response()->json(['data' => $store]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Store::class);

        $validator = Validator::make($request->all(), [
            'nama_toko' => 'required|unique:mst_store,nama_toko',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $store = Store::findOrFail($id);
            $store->update([
                'nama_toko' => $request->nama_toko,
                'updated_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Store Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', Store::class);

        DB::beginTransaction();

        try {
            $store = Store::findOrFail($id);
            $store->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $store->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Store Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the category', 'error' => $e->getMessage()], 500);
        }
    }

    public function getKodeToko()
    {
        try {
            $id = 'STR-001';
            $maxId = Store::withTrashed()->where('kode_toko', 'LIKE', 'STR-%')->max('kode_toko');
            if (!$maxId) {
                $id = 'STR-001';
            } else {
                $maxId = str_replace('STR-', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'STR-00' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'STR-0' . $count;
                } else {
                    $id = 'STR-' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'STR-' . Str::uuid()->toString();
        }
    }
}
