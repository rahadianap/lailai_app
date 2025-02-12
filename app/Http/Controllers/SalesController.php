<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Voucher;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SalesController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        return Inertia::render('Sales/Index');
    }

    public function store(Request $request)
    {
        // $this->authorize('create', Sales::class);

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|max:255',
            'customer_type' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'cash_received' => 'required|numeric|min:0',
            'change' => 'required|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.kode_barcode' => 'required|string|max:255',
            'details.*.nama_barang' => 'required|string|max:255',
            'details.*.quantity' => 'required|numeric|min:0',
            'details.*.harga_jual_eceran' => 'required|numeric|min:0',
            'details.*.diskon' => 'required|numeric|min:0',
            'details.*.dpp' => 'required|numeric|min:0',
            'details.*.ppn' => 'required|numeric|min:0',
            'details.*.total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $rb = Sales::create([
                'kode_penjualan' => $this->getKodePenjualan(),
                'kode_voucher' => $request->kode_voucher,
                'kode_member' => $request->kode_member,
                'payment_method' => $request->payment_method,
                'customer_type' => $request->customer_type,
                'card_number' => $request->card_number,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'total' => $request->total,
                'cash_received' => $request->cash_received,
                'change' => $request->change,
                'applied_points' => $request->applied_points,
                'diskon_global' => $request->diskon_global,
                'created_by' => Auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                $rb->details()->create([
                    'penjualan_id' => $rb->id,
                    'kode_penjualan' => $rb->kode_penjualan,
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'qty' => $detail['quantity'],
                    'harga' => $detail['harga_jual_eceran'],
                    'diskon' => $detail['diskon'],
                    'dpp' => $detail['dpp'],
                    'ppn' => $detail['ppn'],
                    'subtotal' => $detail['total'],
                    'created_by' => Auth()->user()->name,
                ]);
            }

            $voucher = Voucher::where('kode_voucher', $request->kode_voucher)->first();
            if ($voucher) {
                $voucher->update([
                    'status' => 'USED',
                    'updated_by' => Auth()->user()->name,
                ]);
            }

            $member = Member::where('kode_member', $request->kode_member)->first();
            if ($member && $request->applied_points > 0) {
                $member->update([
                    'points' => $member->points - $request->applied_points,
                    'updated_by' => Auth()->user()->name,
                ]);
            } else if ($member) {
                $member->update([
                    'points' => $member->points + $request->applied_points,
                    'updated_by' => Auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Sales Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function getKodePenjualan()
    {
        try {
            $id = 'S' . '/' . date('Ymd') . '/' . '000000001';
            $maxId = Sales::withTrashed()->where('kode_penjualan', 'LIKE', 'S' . '/' . date('Ymd') . '/%')->max('kode_penjualan');
            if (!$maxId) {
                $id = 'S' . '/' . date('Ymd') . '/' . '000000001';
            } else {
                $maxId = str_replace('S' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '00000000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '0000000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '000000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 10000 && $count < 1000000) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 10000 && $count < 1000000) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 1000000) {
                    $id = 'S' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'S' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'S/' . Str::uuid()->toString();
        }
    }

    public function search(Request $request)
    {
        $user = $request->user;

        $query = Product::join('mst_detail_barang', 'mst_barang.id', '=', 'mst_detail_barang.barang_id')
            ->select('mst_barang.*', 'mst_detail_barang.*')
            ->where('mst_barang.is_aktif', 1)
            ->where('kode_toko', $user->kode_toko);

        if ($request->has('search')) {
            $searchParams = $request->input('search');

            $query->where(function ($q) use ($searchParams) {
                foreach ($searchParams as $field => $value) {
                    $q->orWhere('mst_barang.' . $field, 'LIKE', "%{$value}%");
                }
            });
        }

        $perPage = $request->input('per_page', 1);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function getByBarcode($barcode)
    {
        $product = Product::join('mst_detail_barang', 'mst_barang.id', '=', 'mst_detail_barang.barang_id')->where('mst_barang.kode_barcode', $barcode)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function getVouchers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Voucher::where('kode_voucher', 'like', "%{$search}%")
            ->where('status', 'AVAILABLE')
            ->paginate($perPage);

        return response()->json($data);
    }

    public function getMembers(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = Member::where('kode_member', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }
}
