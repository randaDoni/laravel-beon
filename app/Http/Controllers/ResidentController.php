<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ResidentController extends Controller
{
    public function ResidentName(Request $request)
    {
        $status = $request->query('status');

        $query = Resident::select('id', 'name');

        // Jika status diberikan, filter berdasarkan status
        if ($status) {
            $query->where('contract_status', $status);
        }

        $residents = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $residents
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $amount = $request->get('amount',10);
        $residents = Resident::all();

        return response()->json($residents);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(),[
    //         'name' => 'required|string|max:255',
    //         'ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //         'contract_status' => 'required|in:kontrak,tetap',
    //         'telp_number' => 'required|string|max:20',
    //         'married_status' => 'required|in:sudah,belum',
    //     ]);

    //     if ($validator->fails()){
    //         return response()->json(['errors' => $validator]);
    //     }

    //     $resident = new Resident();
    //     $resident->name = $request->name;
    //     $resident->contract_status = $request->contract_status;
    //     $resident->telp_number = $request->telp_number;
    //     $resident->married_status = $request->married_status;

    //     // Simpan foto KTP jika ada
    //     if ($request->hasFile('ktp')) {
    //         $ktpPath = $request->file('ktp')->store('ktp', 'public');
    //         $resident->ktp = $ktpPath; // simpan ke model
    //     }


    //     $resident->save();

    //     return response()->json($resident, 201);
    // }
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'contract_status' => 'required|in:kontrak,tetap',
                'telp_number' => 'required|string|max:20',
                'married_status' => 'required|in:sudah,belum',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simpan data baru
            $resident = new Resident();
            $resident->name = $request->name;
            $resident->contract_status = $request->contract_status;
            $resident->telp_number = $request->telp_number;
            $resident->married_status = $request->married_status;

            if ($request->hasFile('ktp')) {
                $ktpPath = $request->file('ktp')->store('ktp', 'public');
                $resident->ktp = $ktpPath;
            }

            $resident->save();

            // Kembalikan data yang baru disimpan dengan status 201 Created
            return response()->json($resident, 201);

        } catch (\Exception $e) {
            // Tangani error tak terduga dan berikan response JSON dengan kode 500
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $resident = Resident::find($id);

        if (!$resident) {
            return response()->json(['message' => 'Data penghuni tidak ditemukan'], 404);
        }

        return response()->json($resident, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resident $resident)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all(), $request->file('ktp'));

        $resident = Resident::find($id);

        if (!$resident) {
            return response()->json(['message' => 'Data penghuni tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'contract_status' => 'required|in:kontrak,tetap',
            'telp_number' => 'required|string|max:20',
            'married_status' => 'required|in:sudah,belum',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $resident->fill($request->only(['name', 'contract_status', 'telp_number', 'married_status']));

        // Simpan foto KTP jika ada
        if ($request->hasFile('ktp')) {
            // hapus file lama kalau ada
            if ($resident->ktp && Storage::disk('public')->exists($resident->ktp)) {
                Storage::disk('public')->delete($resident->ktp);
            }
            $ktpPath = $request->file('ktp')->store('ktp', 'public');
            $resident->ktp = $ktpPath;
        }


        $resident->save();

        return response()->json($resident, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $resident = Resident::find($id);

        if (!$resident) {
            return response()->json(['message' => 'Data penghuni tidak ditemukan'], 404);
        }

        $resident->delete();

        return response()->json(['message' => 'Data penghuni berhasil dihapus'], 200);
    }
}
