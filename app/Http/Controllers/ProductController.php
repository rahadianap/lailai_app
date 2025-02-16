<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DetailProduct;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $this->authorize('view', Product::class);

        $perPage = $request->input('per_page', 10);

        $data = Product::join('mst_detail_barang', 'mst_barang.id', '=', 'mst_detail_barang.barang_id')
            ->select('mst_barang.*', 'mst_detail_barang.*')
            ->where('mst_barang.is_aktif', 1)
            ->where('nama_toko', auth()->user()->nama_toko)
            ->paginate($perPage);

        return Inertia::render('Products/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validator = Validator::make($request->all(), [
            'kode_barcode' => 'required|unique:mst_barang,kode_barcode',
            'nama_barang' => 'required|string|max:255',
            'nama_satuan' => 'required|string|max:50',
            'nama_kategori' => 'required|string|max:50',
            'isi_barang' => 'required|integer|min:1',
            'is_taxable' => 'required|boolean',
            'details' => 'required|array|min:1',
            'details.*.saldo_awal' => 'required|numeric|min:0',
            'details.*.harga_jual_karton' => 'required|numeric|min:0',
            'details.*.harga_jual_eceran' => 'required|numeric|min:0',
            'details.*.harga_beli_karton' => 'required|numeric|min:0',
            'details.*.harga_beli_eceran' => 'required|numeric|min:0',
            'details.*.hpp_avg_karton' => 'required|numeric|min:0',
            'details.*.hpp_avg_eceran' => 'required|numeric|min:0',
            'details.*.current_stock' => 'required|numeric|min:0',
            'details.*.nilai_akhir' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $product = Product::create([
                'kode_barang' => $this->getKodeBarang(),
                'kode_barcode' => $request->kode_barcode,
                'nama_barang' => $request->nama_barang,
                'nama_satuan' => $request->nama_satuan,
                'nama_kategori' => $request->nama_kategori,
                'isi_barang' => $request->isi_barang,
                'is_taxable' => $request->is_taxable,
                'created_by' => auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailProduct::create([
                    'barang_id' => $product->id,
                    'nama_toko' => auth()->user()->nama_toko,
                    'kode_barcode' => $product->kode_barcode,
                    'nama_barang' => $product->nama_barang,
                    'saldo_awal' => $detail['saldo_awal'],
                    'harga_jual_karton' => $detail['harga_jual_karton'],
                    'harga_jual_eceran' => $detail['harga_jual_eceran'],
                    'harga_beli_karton' => $detail['harga_beli_karton'],
                    'harga_beli_eceran' => $detail['harga_beli_eceran'],
                    'hpp_avg_karton' => $detail['hpp_avg_karton'],
                    'hpp_avg_eceran' => $detail['hpp_avg_eceran'],
                    'current_stock' => $detail['current_stock'],
                    'nilai_akhir' => $detail['nilai_akhir'],
                    'created_by' => auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Product Create Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the product', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $product = Product::with('details')->findOrFail($id);

        return response()->json(['data' => $product]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Product::class);

        $validator = Validator::make($request->all(), [
            'kode_barcode' => 'required|unique:mst_barang,kode_barcode,' . $id,
            'nama_barang' => 'required|string|max:255',
            'nama_satuan' => 'required|string|max:50',
            'nama_kategori' => 'required|string|max:50',
            'isi_barang' => 'required|integer|min:1',
            'is_taxable' => 'required|boolean',
            'details' => 'required|array|min:1',
            'details.*.saldo_awal' => 'required|numeric|min:0',
            'details.*.harga_jual_karton' => 'required|numeric|min:0',
            'details.*.harga_jual_eceran' => 'required|numeric|min:0',
            'details.*.harga_beli_karton' => 'required|numeric|min:0',
            'details.*.harga_beli_eceran' => 'required|numeric|min:0',
            'details.*.hpp_avg_karton' => 'required|numeric|min:0',
            'details.*.hpp_avg_eceran' => 'required|numeric|min:0',
            'details.*.current_stock' => 'required|numeric|min:0',
            'details.*.nilai_akhir' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);
            $product->update([
                'kode_barcode' => $request->kode_barcode,
                'nama_barang' => $request->nama_barang,
                'nama_satuan' => $request->nama_satuan,
                'nama_kategori' => $request->nama_kategori,
                'isi_barang' => $request->isi_barang,
                'is_taxable' => $request->is_taxable,
                'updated_by' => auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailProduct::where('barang_id', $product->id)->update([
                    'barang_id' => $product->id,
                    'kode_barcode' => $product->kode_barcode,
                    'nama_barang' => $product->nama_barang,
                    'saldo_awal' => $detail['saldo_awal'],
                    'harga_jual_karton' => $detail['harga_jual_karton'],
                    'harga_jual_eceran' => $detail['harga_jual_eceran'],
                    'harga_beli_karton' => $detail['harga_beli_karton'],
                    'harga_beli_eceran' => $detail['harga_beli_eceran'],
                    'hpp_avg_karton' => $detail['hpp_avg_karton'],
                    'hpp_avg_eceran' => $detail['hpp_avg_eceran'],
                    'current_stock' => $detail['current_stock'],
                    'nilai_akhir' => $detail['nilai_akhir'],
                    'created_by' => auth()->user()->name,
                    'updated_by' => auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Product Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', Product::class);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);
            $product->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $product->details()->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $product->details()->delete();
            $product->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Product Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the product', 'error' => $e->getMessage()], 500);
        }
    }

    public function getCategories(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Category::where('nama_kategori', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getUnits(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Unit::where('nama_satuan', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getKodeBarang()
    {
        try {
            $id = 'PRD-000001';
            $maxId = Product::withTrashed()->where('kode_barang', 'LIKE', 'PRD-%')->max('kode_barang');
            if (!$maxId) {
                $id = 'PRD-000001';
            } else {
                $maxId = str_replace('PRD-', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'PRD-00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'PRD-0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'PRD-000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'PRD-00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'PRD-0' . $count;
                } else {
                    $id = 'PRD-' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'PRD-' . Str::uuid()->toString();
        }
    }
}
