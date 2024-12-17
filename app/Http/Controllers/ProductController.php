<?php

namespace App\Http\Controllers;

use App\Models\DetailProduct;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);

        $data = Product::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);
        return Inertia::render('Products/Index', [
            'data' => $data
        ]);
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

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateProductData($request);

            $exist = Product::where('kode_barcode', $validatedData['kode_barcode'])->exists();

            if ($exist) {
                return back()->withErrors($exist)->withInput();
            }

            DB::beginTransaction();

            $product = Product::create([
                'kode_barang' => $this->getKodeBarang(),
                'kode_barcode' => $validatedData['kode_barcode'],
                'nama_barang' => $validatedData['nama_barang'],
                'satuan' => $validatedData['satuan'],
                'kategori' => $validatedData['kategori'],
                'isi_barang' => $validatedData['isi_barang'],
                'is_taxable' => $validatedData['is_taxable'],
            ]);

            $product->details()->create($validatedData['details']);

            DB::commit();

            return redirect()->back()->with('success', 'Product Create Successful.');
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the product', 'error' => $e->getMessage()], 500);
        }
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

    private function validateProductData(Request $request)
    {
        return $request->validate([
            'kode_barcode' => 'required|string|max:255|unique:mstbarang,kode_barcode,' . ($request->id ?? 'NULL') . ',id',
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|exists:mstsatuanbarang,id',
            'kategori' => 'required|exists:mstkategoribarang,id',
            'isi_barang' => 'required|numeric|min:0',
            'is_taxable' => 'required|boolean',
            'details.nama_barang' => 'required|string|max:255',
            'details.saldo_awal' => 'required|numeric|min:0',
            'details.harga_jual_karton' => 'required|numeric|min:0',
            'details.harga_jual_eceran' => 'required|numeric|min:0',
            'details.harga_beli_karton' => 'required|numeric|min:0',
            'details.harga_beli_eceran' => 'required|numeric|min:0',
            'details.hpp_avg_karton' => 'required|numeric|min:0',
            'details.hpp_avg_eceran' => 'required|numeric|min:0',
            'details.current_stock' => 'required|numeric|min:0',
            'details.nilai_akhir' => 'required|numeric|min:0',
        ]);
    }
}
