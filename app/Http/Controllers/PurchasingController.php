<?php

namespace App\Http\Controllers;

use App\Models\DetailProduct;
use App\Models\DetailPurchasing;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Purchasing;
use App\Models\Supplier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf as Pdf;

class PurchasingController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', Purchasing::class);

        $perPage = $request->input('per_page', 10);

        $data = Purchasing::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('Purchasing/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Purchasing::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|string|max:255',
            'purchase_type' => 'required|string|max:255',
            'rebate' => 'required|numeric|min:0',
            'diskon_total' => 'required|numeric|min:0',
            'dpp_total' => 'required|numeric|min:0',
            'ppn_total' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty' => 'required|numeric|min:0',
            'details.*.nama_satuan' => 'required|string|max:255',
            'details.*.isi_barang' => 'required|numeric|min:0',
            'details.*.harga' => 'required|numeric|min:0',
            'details.*.diskon' => 'required|numeric|min:0',
            'details.*.jumlah' => 'required|numeric|min:0',
            'details.*.dpp' => 'required|numeric|min:0',
            'details.*.ppn' => 'required|numeric|min:0',
            'details.*.harga_jual' => 'required|numeric|min:0',
            'details.*.exp_date' => 'required|date',
            'details.*.is_taxable' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $purchase = Purchasing::create([
                'kode_pembelian' => $this->getKodePembelian(),
                'nama_supplier' => $request->nama_supplier,
                'kode_po' => $request->kode_po,
                'keterangan' => $request->keterangan,
                'purchase_type' => $request->purchase_type,
                'rebate' => $request->rebate || 0,
                'diskon_total' => $request->diskon_total || 0,
                'dpp_total' => $request->dpp_total,
                'ppn_total' => $request->ppn_total,
                'total' => $request->total,
                'status' => 'CREATED',
                'created_by' => Auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                $purchase->details()->create([
                    'pembelian_id' => $purchase->id,
                    'kode_pembelian' => $purchase->kode_pembelian,
                    'nomor_faktur' => $this->getNoFaktur(),
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty' => $detail['qty'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'isi' => $detail['isi_barang'],
                    'harga' => $detail['harga'],
                    'jumlah' => $detail['jumlah'],
                    'diskon' => $detail['diskon'],
                    'harga_satuan_kecil' => $detail['harga'] / $detail['isi_barang'],
                    'nilai_dpp' => $detail['dpp'] || 0,
                    'nilai_ppn' => $detail['ppn'] || 0,
                    'harga_jual' => $detail['harga_jual'],
                    'exp_date' => $detail['exp_date'],
                    'is_taxable' => $detail['taxable'],
                    'created_by' => Auth()->user()->name,
                ]);
            }

            foreach ($request->details as $item) {
                $detailProduct = Product::join('mst_detail_barang', 'mst_barang.id', '=', 'mst_detail_barang.barang_id')->where('mst_detail_barang.kode_barcode', $item['kode_barcode'])->first();
                DetailPurchasing::where('kode_barcode', $item['kode_barcode'])->where('pembelian_id', $purchase->id)->update([
                    'current_hpp_satuan_besar' => $detailProduct['hpp_avg_karton'],
                    'current_hpp_satuan_kecil' => $detailProduct['hpp_avg_eceran'],
                ]);

                // Update stock di tabel mst_detail_barang & HPP
                DetailProduct::where('kode_barcode', $item['kode_barcode'])->update([
                    'harga_beli_karton' => (float) $item['harga'],
                    'harga_beli_eceran' => (float) $item['harga'] / $item['isi_barang'],
                    'harga_jual_karton' => $item['harga_jual'],
                    'harga_jual_eceran' => $item['harga_jual'] / $item['isi_barang'],
                    'hpp_avg_karton' => ($detailProduct->nilai_akhir + ($item['harga'] * $item['qty'] - ($item['diskon']))) / (($detailProduct->current_stock / $item['isi_barang']) + $item['qty']),
                    'hpp_avg_eceran' => (($detailProduct->nilai_akhir + ($item['harga'] * $item['qty'] - ($item['diskon']))) / (($detailProduct->current_stock / $item['isi_barang']) + $item['qty'])) / $item['isi_barang'],
                    'current_stock' => $item['qty'] * $item['isi_barang'] + $detailProduct->current_stock,
                    'nilai_akhir' => ((($detailProduct->nilai_akhir + ($item['harga'] * $item['qty'] - ($item['diskon']))) / (($detailProduct->current_stock / $item['isi_barang']) + $item['qty'])) / $item['isi_barang']) * ($item['qty'] * $item['isi_barang'] + $detailProduct->current_stock),
                ]);
            }

            DB::commit();

            return Inertia::render('Purchasing/Index')->with('message', 'Purchase created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $purchase = Purchasing::with('details')->findOrFail($id);

        return response()->json(['data' => $purchase]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Purchasing::class);

        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|string|max:255',
            'purchase_type' => 'required|string|max:255',
            'rebate' => 'required|numeric|min:0',
            'diskon_total' => 'required|numeric|min:0',
            'dpp_total' => 'required|numeric|min:0',
            'ppn_total' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty' => 'required|numeric|min:0',
            'details.*.nama_satuan' => 'required|string|max:255',
            'details.*.isi_barang' => 'required|numeric|min:0',
            'details.*.harga' => 'required|numeric|min:0',
            'details.*.diskon' => 'required|numeric|min:0',
            'details.*.diskon_global' => 'required|numeric|min:0',
            'details.*.jumlah' => 'required|numeric|min:0',
            'details.*.dpp' => 'required|numeric|min:0',
            'details.*.ppn' => 'required|numeric|min:0',
            'details.*.harga_jual' => 'required|numeric|min:0',
            'details.*.exp_date' => 'required|date',
            'details.*.is_taxable' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $po = Purchasing::findOrFail($id);
            $po->update([
                'nama_supplier' => $request->nama_supplier,
                'kode_po' => $request->kode_po,
                'keterangan' => $request->keterangan,
                'purchase_type' => $request->purchase_type,
                'rebate' => $request->rebate || 0,
                'diskon_total' => $request->diskon_total || 0,
                'dpp_total' => $request->dpp_total || 0,
                'ppn_total' => $request->ppn_total || 0,
                'total' => $request->total,
                'updated_by' => auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailPurchasing::where('pembelian_id', $po->id)->update([
                    'pembelian_id' => $po->id,
                    'kode_pembelian' => $po->kode_pembelian,
                    'nomor_faktur' => $this->getNoFaktur(),
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty' => $detail['qty'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'isi' => $detail['isi_barang'],
                    'harga' => $detail['harga'],
                    'jumlah' => $detail['jumlah'],
                    'diskon' => $detail['diskon'],
                    'diskon_global' => $detail['diskon_global'],
                    'harga_satuan_kecil' => $detail['harga'] / $detail['isi_barang'],
                    'nilai_dpp' => $detail['dpp'] || 0,
                    'nilai_ppn' => $detail['ppn'] || 0,
                    'harga_jual' => $detail['harga_jual'],
                    'exp_date' => $detail['exp_date'],
                    'is_taxable' => $detail['is_taxable'],
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
        $this->authorize('delete', Purchasing::class);

        DB::beginTransaction();

        try {
            $po = Purchasing::findOrFail($id);
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

            return redirect()->back()->with('success', 'Purchasing Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        $this->authorize('approve', Purchasing::class);

        DB::beginTransaction();

        try {
            $po = Purchasing::findOrFail($id);
            $po->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Purchasing Approved Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while approving the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function print($id)
    {
        $data = Purchasing::with('details')->where('id', $id)->first();
        $supplier = Supplier::where('nama_supplier', $data->nama_supplier)->first();
        $pdf = Pdf::loadView('purchasing', ['data' => $data, 'supplier' => $supplier]);

        return $pdf->setPaper('a4')->stream();
    }

    public function getSuppliers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Supplier::where('nama_supplier', 'like', "%{$search}%")
            ->paginate($perPage);

        return Inertia::render('Suppliers', [
            'data' => $data->items(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'total' => $data->total(),
        ]);
    }

    public function getPO(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = PurchaseOrder::where('kode_po', 'like', "%{$search}%")
            ->where('status', 'APPROVED')
            ->paginate($perPage);

        return Inertia::render('PurchaseOrder', [
            'data' => $data->items(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'total' => $data->total(),
        ]);
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
            ->where('mst_barang.kode_barcode', $id)
            ->first();

        return response()->json($data);
        // return Inertia::render('ProductDetails', [
        //     'data' => $data,
        // ]);
    }

    public function fetchPODetails($id)
    {
        $data = PurchaseOrder::join('trx_detail_purchase_order', 'trx_purchase_order.id', '=', 'trx_detail_purchase_order.purchase_order_id')->where('purchase_order_id', $id)->get();

        return response()->json($data);
    }

    public function getKodePembelian()
    {
        try {
            $id = 'PR' . '/' . date('Ymd') . '/' . '000001';
            $maxId = Purchasing::withTrashed()->where('kode_pembelian', 'LIKE', 'PR' . '/' . date('Ymd') . '/')->max('kode_pembelian');
            if (!$maxId) {
                $id = 'PR' . '/' . date('Ymd') . '/' . '000001';
            } else {
                $maxId = str_replace('PR' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'PR' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'PR' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'PR' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'PR' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'PR' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'PR' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'PR/' . Str::uuid()->toString();
        }
    }

    public function getNoFaktur()
    {
        try {
            $id = 'FKT-PR' . '/' . date('Ymd') . '/' . '000001';
            $maxId = DetailPurchasing::withTrashed()->where('nomor_faktur', 'LIKE', 'FKT-PR' . '/' . date('Ymd') . '/')->max('nomor_faktur');
            if (!$maxId) {
                $id = 'FKT-PR' . '/' . date('Ymd') . '/' . '000001';
            } else {
                $maxId = str_replace('FKT-PR' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'FKT-PR' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'FKT-PR' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'FKT-PR' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'FKT-PR' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'FKT-PR' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'FKT-PR' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'FKT-PR/' . Str::uuid()->toString();
        }
    }
}
