<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HouseController extends Controller
{
    /**
     * Display a listing of the resource. **/
    public function AddressName(Request $request)
    {
        $status = $request->query('status');

        $query = House::select('id', 'alamat');

        if ($status) {
            $query->where('resident_status', $status);
        }
        $house = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $house
        ]);
    }

    public function index()
    {
        $houses = House::all();
        return response()->json($houses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                 'alamat' => 'required|string|max:255',
                'resident_status' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simpan data baru
            $house = new House();
            $house->alamat = $request->alamat;
            $house->resident_status = $request->resident_status;
            $house->save();

            // Kembalikan data yang baru disimpan dengan status 201 Created
            return response()->json($house, 201);

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
        $house = House::find($id);

        if (!$house) {
            return response()->json(['message' => 'Data penghuni tidak ditemukan']);
        }
        return response()->json($house, 200);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(House $house)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
         $house = House::find($id);

        if (!$house) {
            return response()->json(['message' => 'Data penghuni tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'alamat' => 'required|string|max:255',
            'resident_status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $house->fill($request->only(['alamat', 'resident_status']));


        $house->save();

        return response()->json($house, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $house = House::find($id);

        if (!$house) {
            return response()->json(['message' => 'Data penghuni tidak ditemukan'], 404);
        }

        $house->delete();

        return response()->json(['message' => 'Data penghuni berhasil dihapus'], 200);
    }
}
