<?php

namespace App\Http\Controllers;

use App\Models\DetailPurchaseOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade as Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', PurchaseOrder::class);

        $perPage = $request->input('per_page', 10);

        $data = PurchaseOrder::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('PurchaseOrder/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty' => 'required|numeric|min:0',
            'details.*.nama_satuan' => 'required|string|max:255',
            'details.*.isi_barang' => 'required|numeric|min:0',
            // 'details.*.harga' => 'required|numeric|min:0',
            // 'details.*.diskon' => 'required|numeric|min:0',
            // 'details.*.diskon_global' => 'required|numeric|min:0',
            // 'details.*.jumlah' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::create([
                'kode_po' => $this->getKodePO(),
                'nama_supplier' => $request->nama_supplier,
                'status' => 'CREATED',
                'keterangan' => $request->keterangan,
                'created_by' => Auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                $po->details()->create([
                    'purchase_order_id' => $po->id,
                    'kode_po' => $po->kode_po,
                    'nomor_faktur' => $this->getNoFaktur(),
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty' => $detail['qty'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'isi' => $detail['isi_barang'],
                    // 'harga' => $detail['harga'],
                    // 'harga_satuan_kecil' => $detail['harga'] / $detail['isi_barang'],
                    // 'jumlah' => $detail['jumlah'],
                    // 'diskon' => $detail['diskon'],
                    // 'diskon_global' => $detail['diskon_global'],
                    'created_by' => Auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'PurchaseOrder Create Successfully.');
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
        $po = PurchaseOrder::with('details')->findOrFail($id);

        return response()->json(['data' => $po]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', PurchaseOrder::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty' => 'required|numeric|min:0',
            'details.*.nama_satuan' => 'required|string|max:255',
            'details.*.isi_barang' => 'required|numeric|min:0',
            // 'details.*.harga' => 'required|numeric|min:0',
            // 'details.*.diskon' => 'required|numeric|min:0',
            // 'details.*.diskon_global' => 'required|numeric|min:0',
            // 'details.*.jumlah' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::findOrFail($id);
            $po->update([
                'nama_supplier' => $request->nama_supplier,
                'keterangan' => $request->keterangan,
                'updated_by' => auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailPurchaseOrder::where('purchase_order_id', $po->id)->update([
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty' => $detail['qty'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'isi' => $detail['isi_barang'],
                    // 'harga' => $detail['harga'],
                    // 'harga_satuan_kecil' => $detail['harga'] / $detail['isi_barang'],
                    // 'jumlah' => $detail['jumlah'],
                    // 'diskon' => $detail['diskon'],
                    // 'diskon_global' => $detail['diskon_global'],
                    'updated_by' => auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'PurchaseOrder Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the po',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', PurchaseOrder::class);

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::findOrFail($id);
            $po->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $po->details()->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $po->details()->delete();
            $po->delete();

            DB::commit();

            return redirect()->back()->with('success', 'PurchaseOrder Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        $this->authorize('approve', PurchaseOrder::class);

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::findOrFail($id);
            $po->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'PurchaseOrder Approved Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while approving the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function print($id)
    {
        $data = PurchaseOrder::with('details')->where('id', $id)->first();
        $supplier = Supplier::where('nama_supplier', $data->nama_supplier)->first();
        $pdf = Pdf::loadView('po', ['data' => $data, 'supplier' => $supplier]);

        return $pdf->setPaper('a4')->stream();
    }

    public function getSuppliers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Supplier::where('kode_supplier', 'like', "%{$search}%")
            ->orWhere('nama_supplier', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getProducts(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Product::where('kode_barcode', 'like', "%{$search}%")
            ->orWhere('nama_barang', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function fetchDetails($id)
    {
        $data = Product::where('kode_barcode', $id)->first();

        return response()->json($data);
    }

    public function getKodePO()
    {
        try {
            $id = 'PO' . '/' . date('Ymd') . '/' . '000001';
            $maxId = PurchaseOrder::withTrashed()->where('kode_po', 'LIKE', 'PO' . '/' . date('Ymd') . '/%')->max('kode_po');
            if (!$maxId) {
                $id = 'PO' . '/' . date('Ymd') . '/' . '000001';
            } else {
                $maxId = str_replace('PO' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'PO' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'PO' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'PO' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'PO' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'PO' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'PO' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'PO/' . Str::uuid()->toString();
        }
    }

    public function getNoFaktur()
    {
        try {
            $id = 'FKT-PO' . '/' . date('Ymd') . '/' . '000001';
            $maxId = DetailPurchaseOrder::withTrashed()->where('nomor_faktur', 'LIKE', 'FKT-PO' . '/' . date('Ymd') . '/')->max('nomor_faktur');
            if (!$maxId) {
                $id = 'FKT-PO' . '/' . date('Ymd') . '/' . '000001';
            } else {
                $maxId = str_replace('FKT-PO' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'FKT-PO' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'FKT-PO' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'FKT-PO' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'FKT-PO' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'FKT-PO' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'FKT-PO' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'FKT-PO/' . Str::uuid()->toString();
        }
    }
}
