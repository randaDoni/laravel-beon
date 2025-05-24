<?php

namespace App\Http\Controllers;

use App\Models\Contribution_monthly;
use App\Models\Contribution_payment;
use App\Models\House_resident;
use App\Models\MasterDataMonthlyPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContributionMonthlyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
       // GET all


    public function index(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        // Ambil semua rumah yang dihuni di bulan dan tahun tersebut
        $residents = House_resident::with('house', 'resident')
            ->whereMonth('date', '<=', $month)
            ->whereYear('date', '<=', $year)
            ->get();

        // Ambil semua house_id yang sudah membayar bulan ini
        $paidHouseIds = Contribution_monthly::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->pluck('house_id')
            ->toArray();

        // Susun response
        $data = $residents->map(function ($res) use ($paidHouseIds) {
            return [
                'id' => $res->house_id,
                'alamat' => $res->house->address ?? '-',
                'resident_name' => $res->resident->name ?? '-',
                'pay_this_month' => in_array($res->house_id, $paidHouseIds) ? 'Sudah' : 'Belum',
            ];
        });

        return response()->json($data);
    }


    // GET one
    public function show($id)
    {
        $data = Contribution_monthly::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return $data;
    }

    // POST create

    public function store(Request $request)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'master_data_monthly_payment_id' => 'required|exists:master_data_monthly_payments,id',
            'payment_status' => 'required|string',
            'date' => 'required|date',
        ]);

        // Simpan ke Contribution_monthly
        $monthly = Contribution_monthly::create([
            'house_id' => $request->house_id,
            'master_data_monthly_payment_id' => $request->master_data_monthly_payment_id,
            'payment_status' => $request->payment_status,
            'date' => $request->date,
        ]);

        // Ambil data master untuk histori
        $master = MasterDataMonthlyPayment::find($request->master_data_monthly_payment_id);
        $notes = "sudah membayar {$master->name} pada " . Carbon::parse($request->date)->format('Y-m-d H:i:s');
        $total = $master->price;

        // Simpan juga ke Contribution_payment
        Contribution_payment::create([
            'house_id' => $request->house_id,
            'payment_type' => 'monthly',
            'payment_status' => $request->payment_status,
            'notes' => $notes,
            'date' => $request->date,
            'total' => $total,
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil dicatat.',
            'data' => $monthly
        ], 201);
    }


    // PUT update
    public function update(Request $request, $id)
    {
        $data = Contribution_monthly::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validated = $request->validate([
            'house_id' => 'sometimes|integer|exists:houses,id',
            'date' => 'sometimes|date',
            'payment_type' => 'sometimes|string',
            'payment_status' => 'sometimes|string',
            'contribution_total' => 'sometimes|numeric',
        ]);

        $data->update($validated);
        return response()->json($data);
    }

    // DELETE
    public function destroy($id)
    {
        $data = Contribution_monthly::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $data->delete();
        return response()->json(['message' => 'Data deleted']);
    }
}
