<?php

namespace App\Http\Controllers;

use App\Models\AccidentialDataMonthlyPayment;
use Illuminate\Http\Request;

class AccidentialDataMonthlyPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = AccidentialDataMonthlyPayment::all();
        return response()->json($data);
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
         $validated = $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $payment = AccidentialDataMonthlyPayment::create($validated);
        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AccidentialDataMonthlyPayment $accidentialDataMonthlyPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccidentialDataMonthlyPayment $accidentialDataMonthlyPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = AccidentialDataMonthlyPayment::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validated = $request->validate([
                       'bulan' => 'sometimes|required|string|max:255',
            'tahun' => 'sometimes|required|numeric|min:0',
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $data->update($validated);
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = AccidentialDataMonthlyPayment::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $data->delete();
        return response()->json(['message' => 'Data deleted.']);
    }
}
