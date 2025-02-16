<?php

namespace App\Http\Controllers;

use App\Models\MutasiKeluar;
use App\Models\MutasiMasuk;
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

class MutasiMasukController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', MutasiMasuk::class);

        $perPage = $request->input('per_page', 10);

        $data = MutasiMasuk::with('details')->where('is_aktif', 1)->paginate(perPage: $perPage);

        return Inertia::render('MutasiMasuk/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', MutasiMasuk::class);

        $validator = Validator::make($request->all(), [
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

        $data = MutasiKeluar::where('tujuan_gudang', auth()->user()->nama_toko)->where('status', 'APPROVED')->first();

        DB::beginTransaction();

        try {
            $rb = MutasiMasuk::create([
                'kode_mutasi_masuk' => $this->getKodeMutasiMasuk(),
                'asal_gudang' => $data->asal_gudang,
                'tujuan_gudang' => Auth()->user()->nama_toko,
                'status' => 'CREATED',
                'keterangan' => $request->keterangan,
                'created_by' => Auth()->user()->name,
            ]);

            foreach ($request->details as $detail) {
                $rb->details()->create([
                    'mutasi_masuk_id' => $rb->id,
                    'kode_mutasi_masuk' => $rb->kode_mutasi_masuk,
                    'kode_barcode' => $detail['kode_barcode'],
                    'nama_barang' => $detail['nama_barang'],
                    'qty' => $detail['qty'],
                    'nama_satuan' => $detail['nama_satuan'],
                    'created_by' => Auth()->user()->name,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'MutasiMasuk Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the po', 'error' => $e->getMessage()], 500);
        }
    }

    public function getMutasiKeluar(Request $request)
    {
        $user = $request->user;

        $search = $request->input('search', '');
        $perPage = 10;

        $data = MutasiKeluar::where('tujuan_gudang', $user->nama_toko)->where('status', 'APPROVED')->where('tujuan_gudang', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }

    public function fetchMutasiKeluarDetails($id)
    {
        $data = MutasiKeluar::join('trx_detail_mutasi_keluar', 'trx_mutasi_keluar.id', '=', 'trx_detail_mutasi_keluar.mutasi_keluar_id')->where('mutasi_keluar_id', $id)->get();

        return response()->json($data);
    }

    public function getKodeMutasiMasuk()
    {
        try {
            $id = 'MM' . '/' . date('Ymd') . '/' . '000001';
            $maxId = MutasiMasuk::withTrashed()->where('kode_mutasi_masuk', 'LIKE', 'MM' . '/' . date('Ymd') . '/%')->max('kode_mutasi_masuk');
            if (!$maxId) {
                $id = 'MM' . '/' . date('Ymd') . '/' . '000001';
            } else {
                $maxId = str_replace('MM' . '/' . date('Ymd') . '/', '', $maxId);
                $count = $maxId + 1;
                if ($count < 10) {
                    $id = 'MM' . '/' . date('Ymd') . '/' . '00000' . $count;
                } elseif ($count >= 10 && $count < 100) {
                    $id = 'MM' . '/' . date('Ymd') . '/' . '0000' . $count;
                } elseif ($count >= 100 && $count < 1000) {
                    $id = 'MM' . '/' . date('Ymd') . '/' . '000' . $count;
                } elseif ($count >= 1000 && $count < 10000) {
                    $id = 'MM' . '/' . date('Ymd') . '/' . '00' . $count;
                } elseif ($count >= 10000 && $count < 100000) {
                    $id = 'MM' . '/' . date('Ymd') . '/' . '0' . $count;
                } else {
                    $id = 'MM' . '/' . date('Ymd') . '/' . $count;
                }
            }

            return $id;
        } catch (\Exception $e) {
            return 'MM/' . Str::uuid()->toString();
        }
    }
}
