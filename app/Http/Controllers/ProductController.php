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

class ProductController extends Controller
{
    public function index(): Response
    {
        $data = Product::where('is_aktif', 1)->get();
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
        $validator = Validator::make($request->all(), [
            'kode_barcode' => 'required',
            'nama_barang' => 'required',
            'satuan' => 'required',
            'kategori' => 'required',
            'isi_barang' => 'required',
            'is_taxable' => 'required',
            'details.saldo_awal' => 'required',
            'details.harga_jual_karton' => 'required',
            'details.harga_jual_eceran' => 'required',
            'details.harga_beli_karton' => 'required',
            'details.harga_beli_eceran' => 'required',
            'details.hpp_avg_karton' => 'required',
            'details.hpp_avg_eceran' => 'required',
            'details.current_stock' => 'required',
            'details.nilai_akhir' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $product = new Product();
            $product->kode_barang = $this->getKodeBarang();
            $product->kode_barcode = $request->input('kode_barcode');
            $product->nama_barang = $request->input('nama_barang');
            $product->satuan = $request->input('satuan');
            $product->kategori = $request->input('kategori');
            $product->isi_barang = $request->input('isi_barang');
            $product->is_taxable = $request->input('is_taxable');
            $product->is_aktif = 1;
            $product->created_by = Auth()->user()->name;
            $product->save();

            $detail = new DetailProduct();
            $detail->nama_barang = $request->input('nama_barang');
            $detail->saldo_awal = $request->details['saldo_awal'];
            $detail->harga_jual_karton = $request->details['harga_jual_karton'];
            $detail->harga_jual_eceran = $request->details['harga_jual_eceran'];
            $detail->harga_beli_karton = $request->details['harga_beli_karton'];
            $detail->harga_beli_eceran = $request->details['harga_beli_eceran'];
            $detail->hpp_avg_karton = $request->details['hpp_avg_karton'];
            $detail->hpp_avg_eceran = $request->details['hpp_avg_eceran'];
            $detail->current_stock = $request->details['current_stock'];
            $detail->nilai_akhir = $request->details['nilai_akhir'];
            $detail->created_by = Auth()->user()->name;
            DB::commit();

            return redirect()->back()->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
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
}
