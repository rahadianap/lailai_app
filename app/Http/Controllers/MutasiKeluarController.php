<?php

namespace App\Http\Controllers;

use App\Models\MutasiKeluar;
use App\Models\DetailMutasiKeluar;
use App\Models\Store;
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

class MutasiKeluarController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', MutasiKeluar::class);

        $perPage = $request->input('per_page', 10);

        $data = MutasiKeluar::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('MutasiKeluar/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', MutasiKeluar::class);

        $validator = Validator::make($request->all(), [
            'tujuan_gudang' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty' => 'required|numeric|min:0',
            'details.*.nama_satuan' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $rb = MutasiKeluar::create([
                'kode_mutasi_keluar' => $this->getKodeMutasiKeluar(),
                'asal_gudang' => Auth()->user()->nama_toko,
                'tujuan_gudang' => $request->tujuan_gudang,
                'status' => 'CREATED',
                'keterangan' => $request->keterangan,
                'created_by' => Auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                $rb->details()->create([
                    'mutasi_keluar_id' => $rb->id,
                    'kode_mutasi_keluar' => $rb->kode_mutasi_keluar,
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty' => $detail['qty'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'created_by' => Auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'MutasiKeluar Create Successfully.');
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
        $retur = MutasiKeluar::with('details')->findOrFail($id);

        return response()->json(['data' => $retur]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', MutasiKeluar::class);

        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty' => 'required|numeric|min:0',
            'details.*.nama_satuan' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $retur = MutasiKeluar::findOrFail($id);
            $retur->update([
                'keterangan' => $request->keterangan,
                'updated_by' => auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailMutasiKeluar::where('mutasi_keluar_id', $retur->id)->update([
                    'mutasi_keluar_id' => $retur->id,
                    'kode_mutasi_keluar' => $retur->kode_mutasi_keluar,
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty' => $detail['qty'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'updated_by' => auth()->user()->name,

                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'MutasiKeluar Update Successfully.');
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
        $this->authorize('delete', MutasiKeluar::class);

        DB::beginTransaction();

        try {
            $retur = MutasiKeluar::findOrFail($id);
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

            return redirect()->back()->with('success', 'MutasiKeluar Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the data', 'error' => $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        $this->authorize('approve', MutasiKeluar::class);

        DB::beginTransaction();

        try {
            $po = MutasiKeluar::findOrFail($id);
            $po->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'MutasiKeluar Approved Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while approving the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function print($id)
    {
        $data = MutasiKeluar::with('details')->where('id', $id)->first();
        $supplier = Supplier::where('nama_supplier', $data->nama_supplier)->first();
        $pdf = Pdf::loadView('returbeli', ['data' => $data, 'supplier' => $supplier]);

        return $pdf->setPaper('a4')->stream();
    }

    public function getTujuan(Request $request)
    {
        $user = $request->user;

        $search = $request->input('search', '');
        $perPage = 10;

        $data = Store::where('nama_toko', '!=', $user->nama_toko)->where('nama_toko', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getProducts(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Product::with('details')
            ->where('kode_barcode', 'like', "%{$search}%")
            ->orWhere('nama_barang', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);

        // return Inertia::render('Products', [
        //     'data' => $data->items(),
        //     'current_page' => $data->currentPage(),
        //     'last_page' => $data->lastPage(),
        //     'total' => $data->total(),
        //     'user' => $user, // Include user information in the response
        // ]);
    }

    public function fetchDetails(Request $request, $id)
    {
        $user = $request->user;

        // $data = Product::with('details')->where('mst_detail_barang.nama_toko', $user->nama_toko)->where('kode_barcode', $id)->first();
        $data = Product::join('mst_detail_barang', 'mst_barang.id', '=', 'mst_detail_barang.barang_id')
            ->select('mst_barang.*', 'mst_detail_barang.*')
            ->where('mst_barang.is_aktif', 1)
            ->where('nama_toko', $user->nama_toko)
            ->where('mst_detail_barang.kode_barcode', $id)
            ->first();

        return response()->json($data);
        // return Inertia::render('ProductDetails', [
        //     'data' => $data,
        // ]);
    }

    public function getKodeMutasiKeluar()
    {
        try {
            $id = 'MK' . '/' . date('Ymd') . '/' . '000001';
            $maxId = MutasiKeluar::withTrashed()->where('kode_mutasi_keluar', 'LIKE', 'MK' . '/' . date('Ymd') . '/%')->max('kode_mutasi_keluar');
            if (!$maxId) {
                $id = 'MK' . '/' . date('Ymd') . '/' . '000001';
            } else {
                $maxId = str_replace('MK' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'MK' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'MK' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'MK' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'MK' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'MK' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'MK' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'MK/' . Str::uuid()->toString();
        }
    }
}
