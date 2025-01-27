<?php

namespace App\Http\Controllers;

use App\Models\DetailReturBeli;
use App\Models\Purchasing;
use App\Models\ReturBeli;
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

    public function store(Request $request)
    {
        $this->authorize('create', ReturBeli::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty_beli' => 'required|numeric|min:0',
            'details.*.nama_satuan_beli' => 'required|string|max:255',
            'details.*.qty_retur' => 'required|numeric|min:0',
            'details.*.nama_satuan_retur' => 'required|string|max:255',
            'details.*.harga' => 'required|numeric|min:0',
            'details.*.jumlah' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $rb = ReturBeli::create([
                'kode_retur_beli' => $this->getKodeReturBeli(),
                'kode_pembelian' => $request->kode_pembelian,
                'nama_supplier' => $request->nama_supplier,
                'status' => 'CREATED',
                'keterangan' => $request->keterangan,
                'created_by' => Auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                $rb->details()->create([
                    'retur_beli_id' => $rb->id,
                    'kode_retur_beli' => $rb->kode_retur_beli,
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty_beli' => $detail['qty_beli'],
                    'nama_satuan_beli' => $detail['nama_satuan_beli'],
                    'qty_retur' => $detail['qty_retur'],
                    'nama_satuan_retur' => $detail['nama_satuan_retur'],
                    'harga' => $detail['harga'],
                    'jumlah' => $detail['jumlah'],
                    'created_by' => Auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'ReturBeli Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $retur = ReturBeli::with('details')->findOrFail($id);

        return response()->json(['data' => $retur]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', ReturBeli::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty_beli' => 'required|numeric|min:0',
            'details.*.nama_satuan_beli' => 'required|string|max:255',
            'details.*.qty_retur' => 'required|numeric|min:0',
            'details.*.nama_satuan_retur' => 'required|string|max:255',
            'details.*.harga' => 'required|numeric|min:0',
            'details.*.jumlah' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $retur = ReturBeli::findOrFail($id);
            $retur->update([
                'nama_supplier' => $request->nama_supplier,
                'kode_pembelian' => $request->kode_pembelian,
                'keterangan' => $request->keterangan,
                'updated_by' => auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailReturBeli::where('retur_beli_id', $retur->id)->update([
                    'retur_beli_id' => $retur->id,
                    'kode_retur_beli' => $retur->kode_retur_beli,
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty_beli' => $detail['qty_beli'],
                    'nama_satuan_beli' => $detail['nama_satuan_beli'],
                    'qty_retur' => $detail['qty_retur'],
                    'nama_satuan_retur' => $detail['nama_satuan_retur'],
                    'harga' => $detail['harga'],
                    'jumlah' => $detail['jumlah'],
                    'updated_by' => auth()->user()->name,

                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'ReturBeli Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', ReturBeli::class);

        DB::beginTransaction();

        try {
            $retur = ReturBeli::findOrFail($id);
            $retur->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $retur->details()->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $retur->details()->delete();
            $retur->delete();

            DB::commit();

            return redirect()->back()->with('success', 'ReturBeli Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the data', 'error' => $e->getMessage()], 500);
        }
    }

    public function getSuppliers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Supplier::where('nama_supplier', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getPurchasing(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Purchasing::where('kode_pembelian', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getProducts(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Product::where('kode_barcode', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function fetchDetails($id)
    {
        $data = Product::where('kode_barcode', $id)->first();

        return response()->json($data);
    }

    public function fetchPurchasingDetails($id)
    {
        $data = Purchasing::join('trx_detail_pembelian', 'trx_pembelian.id', '=', 'trx_detail_pembelian.pembelian_id')->where('pembelian_id', $id)->get();

        return response()->json($data);
    }

    public function getKodeReturBeli()
    {
        try {
            $id = 'RB' . '/' . date('Ymd') . '/' . '000001';
            $maxId = ReturBeli::withTrashed()->where('kode_retur_beli', 'LIKE', 'RB' . '/' . date('Ymd') . '/%')->max('kode_retur_beli');
            if (!$maxId) {
                $id = 'RB' . '/' . date('Ymd') . '/' . '000001';
            } else {
                $maxId = str_replace('RB' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'RB' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'RB' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'RB' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'RB' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'RB' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'RB' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'RB/' . Str::uuid()->toString();
        }
    }
}
