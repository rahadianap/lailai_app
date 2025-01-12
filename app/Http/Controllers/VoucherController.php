<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VoucherController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $this->authorize('view', Voucher::class);

        $perPage = $request->input('per_page', 10);

        $data = Voucher::paginate(perPage: $perPage);

        return Inertia::render('Vouchers/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateVoucherData($request);

            DB::beginTransaction();

            Voucher::create([
                'kode_voucher' => strtoupper(str()->random(10)),
                'nominal' => $validatedData['nominal'],
                'exp_date' => $validatedData['exp_date'],
                'status' => 'AVAILABLE',
                'keterangan' => $validatedData['keterangan'],
                'created_by' => Auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Voucher Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the voucher', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $voucher = Voucher::findOrFail($id);

        return response()->json(['data' => $voucher]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->validateVoucherData($request);

            $voucher = Voucher::find($id);

            if (empty($voucher)) {
                return redirect()->back()->with('error', 'Voucher not found.');
            }

            DB::beginTransaction();


            $voucher->nominal = $validatedData['nominal'];
            $voucher->exp_date = $validatedData['exp_date'];
            $voucher->keterangan = $validatedData['keterangan'];
            $voucher->updated_by = Auth()->user()->name;

            $voucher->save();

            DB::commit();

            return redirect()->back()->with('success', 'Voucher Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the voucher', 'error' => $e->getMessage()], 500);
        }
    }

    private function validateVoucherData(Request $request)
    {
        return $request->validate([
            'nominal' => 'required|numeric|min:0',
            'exp_date' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);
    }
}
