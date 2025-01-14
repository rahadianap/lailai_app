<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\KelompokAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AccountController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('view', arguments: Account::class);

        $perPage = $request->input('per_page', 10);

        $data = Account::paginate(perPage: $perPage);

        return Inertia::render('Account/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Account::class);

        $validator = Validator::make($request->all(), [
            'nomor_account' => 'required|unique:mst_account,nomor_account',
            'nama_account' => 'required|unique:mst_account,nama_account',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            Account::create([
                'nomor_account' => $request->nomor_account,
                'nama_account' => $request->nama_account,
                'nama_kelompok_account' => $request->nama_kelompok_account,
                'level' => $request->level,
                'kas_bank' => $request->kas_bank,
                'tipe_account' => $request->tipe_account,
                'saldo_awal' => $request->saldo_awal,
                'created_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Account Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            // return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
            return Inertia::render('Error', ['errors' => $e->errors()])
                ->toResponse($request);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the account', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $account = Account::findOrFail($id);

        return response()->json(['data' => $account]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Account::class);

        $validator = Validator::make($request->all(), [
            'nomor_account' => 'required|unique:mst_account,nomor_account',
            'nama_account' => 'required|unique:mst_account,nama_account',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $account = Account::findOrFail($id);
            $account->update([
                'nomor_account' => $request->nomor_account,
                'nama_account' => $request->nama_account,
                'nama_kelompok_account' => $request->nama_kelompok_account,
                'level' => $request->level,
                'kas_bank' => $request->kas_bank,
                'tipe_account' => $request->tipe_account,
                'saldo_awal' => $request->saldo_awal,
                'updated_by' => auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Account Update Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating the account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', Account::class);

        DB::beginTransaction();

        try {
            $account = Account::findOrFail($id);
            $account->update([
                'is_aktif' => 0,
                'deleted_by' => auth()->user()->name,
            ]);
            $account->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Account Delete Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while deleting the account', 'error' => $e->getMessage()], 500);
        }
    }

    public function getGroups(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $data = KelompokAccount::where('nama_kelompok_account', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json($data);
    }
}
