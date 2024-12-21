<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);

        $data = Product::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('Products/Index', [
            'data' => $data,
        ]);
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
                'nama_satuan' => $validatedData['nama_satuan'],
                'nama_kategori' => $validatedData['nama_kategori'],
                'isi_barang' => $validatedData['isi_barang'],
                'is_taxable' => $validatedData['is_taxable'],
                'created_by' => Auth()->user()->name,
            ]);

            $validatedData['details']['nama_barang'] = $validatedData['nama_barang'];
            $validatedData['details']['created_by'] = Auth()->user()->name;
            $product->details()->create($validatedData['details']);

            DB::commit();

            return redirect()->back()->with('success', 'Product Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
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
        try {
            $validatedData = $this->validateProductData($request);

            $product = Product::find($id);

            if (empty($product)) {
                return redirect()->back()->with('error', 'Product not found.');
            }

            DB::beginTransaction();

            $product->kode_barcode = $validatedData['kode_barcode'];
            $product->nama_barang = $validatedData['nama_barang'];
            $product->nama_satuan = $validatedData['nama_satuan'];
            $product->nama_kategori = $validatedData['nama_kategori'];
            $product->isi_barang = $validatedData['isi_barang'];
            $product->is_taxable = $validatedData['is_taxable'];
            $product->updated_by = Auth()->user()->name;

            $product->details()->update($validatedData['details']);
            $product->details()->update([
                'nama_barang' => $validatedData['nama_barang'],
                'updated_by' => Auth()->user()->name,
            ]);

            $product->save();

            DB::commit();

            return redirect()->back()->with('success', 'Product Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the product', 'error' => $e->getMessage()], 500);
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
            if (! $maxId) {
                $id = 'PRD-000001';
            } else {
                $maxId = str_replace('PRD-', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'PRD-00000'.$count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'PRD-0000'.$count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'PRD-000'.$count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'PRD-00'.$count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'PRD-0'.$count;
                } else {
                    $id = 'PRD-'.$count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'PRD-'.Str::uuid()->toString();
        }
    }

    private function validateProductData(Request $request)
    {
        return $request->validate([
            'kode_barcode' => 'required|string|max:255|unique:mstbarang,kode_barcode,'.($request->id ?? 'NULL').',id',
            'nama_barang' => 'required|string|max:255',
            'nama_satuan' => 'required|exists:mstsatuanbarang,nama_satuan',
            'nama_kategori' => 'required|exists:mstkategoribarang,nama_kategori',
            'isi_barang' => 'required|numeric|min:0',
            'is_taxable' => 'required|boolean',
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
