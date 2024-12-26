<?php

namespace App\Http\Controllers;

use App\Models\DetailProduct;
use App\Models\DetailPurchaseOrder;
use App\Models\DetailPurchasing;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Purchasing;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PurchasingController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);

        $data = Purchasing::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('Purchasing/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateData($request);

            DB::beginTransaction();

            $purchase = Purchasing::create([
                'kode_pembelian' => $this->getKodePembelian(),
                'nama_supplier' => $validatedData['nama_supplier'],
                'kode_po' => $request->kode_po,
                'keterangan' => $request->keterangan,
                'purchase_type' => $validatedData['purchase_type'],
                'rebate' => $validatedData['rebate'] || 0,
                'diskon_total' => $validatedData['diskon_total'] || 0,
                'dpp_total' => $validatedData['dpp_total'] || 0,
                'ppn_total' => $validatedData['ppn_total'] || 0,
                'total' => $validatedData['total'],
                'created_by' => Auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                $detailProduct = Product::join('mst_detail_barang', 'mst_barang.id', '=', 'mst_detail_barang.barang_id')->where('kode_barcode', $detail['kode_barcode'])->first();
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
                    'diskon_global' => $detail['diskon_global'],
                    'harga_satuan_kecil' => $detail['harga'] / $detail['isi_barang'],
                    'current_hpp_satuan_besar' => $detailProduct->hpp_avg_karton,
                    'current_hpp_satuan_kecil' => $detailProduct->hpp_avg_eceran,
                    'nilai_dpp' => $detail['dpp'] || 0,
                    'nilai_ppn' => $detail['ppn'] || 0,
                    'harga_jual' => $detail['harga_jual'],
                    'exp_date' => $detail['exp_date'],
                    'is_taxable' => $detail['taxable'],
                    'created_by' => Auth()->user()->name,
                ]);
            }


            DB::commit();
            return Inertia::render('Purchasing/Index')->with('message', 'Purchase created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while saving the purchase. Please try again.']);
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

    public function getPO(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = PurchaseOrder::where('kode_po', 'like', "%{$search}%")
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

    private function validateData(Request $request)
    {
        return $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'purchase_type' => 'required|in:ppn,inc_ppn,no_ppn',
            'rebate' => 'required|numeric|min:0',
            'diskon_total' => 'required|numeric|min:0',
            'dpp_total' => 'required|numeric|min:0',
            'ppn_total' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.qty' => 'required|numeric|min:1',
            'details.*.nama_satuan' => 'required|string|max:255',
            'details.*.isi_barang' => 'required|numeric|min:1',
            'details.*.harga' => 'required|numeric|min:0',
            'details.*.diskon' => 'required|numeric|min:0',
            'details.*.diskon_global' => 'required|numeric|min:0',
            'details.*.jumlah' => 'required|numeric|min:0',
            'details.*.exp_date' => 'required|date',
            'details.*.is_taxable' => 'required|boolean',
        ]);
    }
}
