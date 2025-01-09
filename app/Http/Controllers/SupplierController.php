<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Supplier;

class SupplierController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $this->authorize('view', Supplier::class);

        $perPage = $request->input('per_page', 10);

        $data = Supplier::paginate(perPage: $perPage);

        return Inertia::render('Suppliers/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Supplier::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|unique:mst_supplier,nama_supplier'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            Supplier::create([
                'kode_supplier' => $this->getKodeSupplier(),
                'no_ktp' => $request->no_ktp,
                'npwp' => $request->npwp,
                'nama_supplier' => $request->nama_supplier,
                'alamat' => $request->alamat,
                'tgl_lahir' => $request->tgl_lahir,
                'no_hp1' => $request->no_hp1,
                'no_hp2' => $request->no_hp2,
                'email' => $request->email,
                'keterangan' => $request->keterangan,
                'is_retur' => $request->is_retur,
                'created_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Supplier Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            // return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
            return Inertia::render('Error', ['errors' => $e->errors()])
                ->toResponse($request);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the supplier', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);

        return response()->json(['data' => $supplier]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Supplier::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|unique:mst_supplier,nama_supplier',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->update([
                'no_ktp' => $request->no_ktp,
                'npwp' => $request->npwp,
                'nama_supplier' => $request->nama_supplier,
                'alamat' => $request->alamat,
                'tgl_lahir' => $request->tgl_lahir,
                'no_hp1' => $request->no_hp1,
                'no_hp2' => $request->no_hp2,
                'email' => $request->email,
                'keterangan' => $request->keterangan,
                'is_retur' => $request->is_retur,
                'updated_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Supplier Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the supplier',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', Supplier::class);

        DB::beginTransaction();

        try {
            $unit = Supplier::findOrFail($id);
            $unit->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $unit->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Supplier Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the unit', 'error' => $e->getMessage()], 500);
        }
    }

    public function getKodeSupplier()
    {
        try {
            $id = 'CLG-0001';
            $maxId = Supplier::withTrashed()->where('kode_supplier', 'LIKE', 'CLG-%')->max('kode_supplier');
            if (!$maxId) {
                $id = 'CLG-0001';
            } else {
                $maxId = str_replace('CLG-', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'CLG-000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'CLG-00' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'CLG-0' . $count;
                } else {
                    $id = 'CLG-' . $count;
                }
            }
            return $id;
        } catch (\Exception $e) {
            return 'CLG-' . Str::uuid()->toString();
        }
    }
}
