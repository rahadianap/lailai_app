<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UnitController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $this->authorize('view', Unit::class);

        $perPage = $request->input('per_page', 10);

        $data = Unit::paginate(perPage: $perPage);

        return Inertia::render('Units/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Unit::class);

        $validator = Validator::make($request->all(), [
            'nama_satuan' => 'required|unique:mst_satuan_barang,nama_satuan',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            Unit::create([
                'kode_satuan' => $this->getKodeSatuan(),
                'nama_satuan' => $request->nama_satuan,
                'created_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Unit Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            // return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
            return Inertia::render('Error', ['errors' => $e->errors()])
                ->toResponse($request);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the unit', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);

        return response()->json(['data' => $unit]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Unit::class);

        $validator = Validator::make($request->all(), [
            'nama_satuan' => 'required|unique:mst_satuan_barang,nama_satuan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $unit = Unit::findOrFail($id);
            $unit->update([
                'nama_satuan' => $request->nama_satuan,
                'updated_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Unit Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the unit',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', Unit::class);

        DB::beginTransaction();

        try {
            $unit = Unit::findOrFail($id);
            $unit->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $unit->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Unit Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the unit', 'error' => $e->getMessage()], 500);
        }
    }

    public function getKodeSatuan()
    {
        try {
            $id = 'UQC-001';
            $maxId = Unit::withTrashed()->where('kode_satuan', 'LIKE', 'UQC-%')->max('kode_satuan');
            if (!$maxId) {
                $id = 'UQC-001';
            } else {
                $maxId = str_replace('UQC-', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'UQC-00' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'UQC-0' . $count;
                } else {
                    $id = 'UQC-' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'UQC-' . Str::uuid()->toString();
        }
    }
}
