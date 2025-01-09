<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', Category::class);

        $perPage = $request->input('per_page', 10);

        $data = Category::paginate(perPage: $perPage);

        return Inertia::render('Categories/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Category::class);

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|unique:mst_kategori_barang,nama_kategori',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            Category::create([
                'kode_kategori' => $this->getKodeKategori(),
                'nama_kategori' => $request->nama_kategori,
                'created_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Category Create Successfully.');
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
        $category = Category::findOrFail($id);

        return response()->json(['data' => $category]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Category::class);

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|unique:mst_kategori_barang,nama_kategori',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $category = Category::findOrFail($id);
            $category->update([
                'nama_kategori' => $request->nama_kategori,
                'updated_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Category Update Successfully.');
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
        $this->authorize('delete', Category::class);

        DB::beginTransaction();

        try {
            $category = Category::findOrFail($id);
            $category->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $category->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Category Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the category', 'error' => $e->getMessage()], 500);
        }
    }

    public function getKodeKategori()
    {
        try {
            $id = 'CAT-001';
            $maxId = Category::withTrashed()->where('kode_kategori', 'LIKE', 'CAT-%')->max('kode_kategori');
            if (!$maxId) {
                $id = 'CAT-001';
            } else {
                $maxId = str_replace('CAT-', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'CAT-00' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'CAT-0' . $count;
                } else {
                    $id = 'CAT-' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'CAT-' . Str::uuid()->toString();
        }
    }
}
