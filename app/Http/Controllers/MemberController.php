<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);

        $data = Member::paginate(perPage: $perPage);

        return Inertia::render('Members/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateMemberData($request);

            DB::beginTransaction();

            Member::create([
                'kode_member' => fake()->randomNumber(8),
                'nik' => $request->nik,
                'nama_member' => $validatedData['nama_member'],
                'email' => $request->email,
                'no_hp' => $validatedData['no_hp'],
                'alamat' => $request->alamat,
                'point' => 0,
                'tgl_daftar' => Carbon::now(),
                'exp_date' => new Carbon('first day of November this year'),
                'is_aktif' => true,
                'created_by' => Auth()->user()->name,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Member Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the member', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $member = Member::findOrFail($id);

        return response()->json(['data' => $member]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->validateMemberData($request);

            $member = Member::find($id);

            if (empty($member)) {
                return redirect()->back()->with('error', 'Member not found.');
            }

            DB::beginTransaction();


            $member->nik = $request->nik;
            $member->nama_member = $validatedData['nama_member'];
            $member->email = $request->email;
            $member->no_hp = $validatedData['no_hp'];
            $member->alamat = $request->alamat;
            $member->updated_by = Auth()->user()->name;

            $member->save();

            DB::commit();

            return redirect()->back()->with('success', 'Member Create Successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred while creating the member', 'error' => $e->getMessage()], 500);
        }
    }

    private function validateMemberData(Request $request)
    {
        return $request->validate([
            'nama_member' => 'required|string|max:255',
            'no_hp' => 'required|string|max:255|unique:mst_member,no_hp,' . ($request->id ?? 'NULL') . ',id',
        ]);
    }
}
