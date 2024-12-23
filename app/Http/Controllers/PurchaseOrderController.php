<?php

namespace App\Http\Controllers;

use App\Models\DetailPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);

        $data = PurchaseOrder::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('PurchaseOrder/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateData($request);

            DB::beginTransaction();

            $po = PurchaseOrder::create([
                'kode_po' => $this->getKodePO(),
                'nama_supplier' => $validatedData['nama_supplier'],
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
                    'harga' => $detail['harga'],
                    'harga_satuan_kecil' => $detail['harga'] / $detail['isi_barang'],
                    'jumlah' => $detail['jumlah'],
                    'diskon' => $detail['diskon'],
                    'diskon_global' => $detail['jumlah'],
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

    public function getSuppliers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Supplier::where('nama_supplier', 'like', "%{$search}%")
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

    public function getKodePO()
    {
        try {
            $id = 'PO' . '/' . date('Ymd') . '/' . '000001';
            $maxId = PurchaseOrder::withTrashed()->where('kode_po', 'LIKE', 'PO' . '/' . date('Ymd') . '/')->max('kode_po');
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

    private function validateData(Request $request)
    {
        return $request->validate([
            'nama_supplier' => 'required|string|max:255',
            // 'details.saldo_awal' => 'required|numeric|min:0',
            // 'details.harga_jual_karton' => 'required|numeric|min:0',
            // 'details.harga_jual_eceran' => 'required|numeric|min:0',
            // 'details.harga_beli_karton' => 'required|numeric|min:0',
            // 'details.harga_beli_eceran' => 'required|numeric|min:0',
            // 'details.hpp_avg_karton' => 'required|numeric|min:0',
            // 'details.hpp_avg_eceran' => 'required|numeric|min:0',
            // 'details.current_stock' => 'required|numeric|min:0',
            // 'details.nilai_akhir' => 'required|numeric|min:0',
        ]);
    }
}
