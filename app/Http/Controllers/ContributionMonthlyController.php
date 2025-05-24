<?php

namespace App\Http\Controllers;

use App\Models\Contribution_monthly;
use App\Models\Contribution_payment;
use App\Models\House_resident;
use App\Models\MasterDataMonthlyPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ContributionMonthlyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
       // GET all
    public function Item(Request $request)
    {


        $data = MasterDataMonthlyPayment::all();


        // dd($data);

        return response()->json($data);
    }

    public function summary(Request $request)
    {
        $tahun = $request->tahun ?? now()->year;
        $bulanList = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $result = [];

        foreach ($bulanList as $bulan) {

            $pemasukan = Contribution_monthly::where('bulan', $bulan)
                ->where('tahun', $tahun)

                ->sum('contribution_total');

            $pengeluaran = MasterDataMonthlyPayment::where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->sum('price');

            $result[] = [
                'bulan' => $bulan,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'saldo' => $pemasukan - $pengeluaran
            ];
        }

        return response()->json($result);
    }
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'bulan' => 'required|string',
            'tahun' => 'required|integer',
            'item_id' => 'required|exists:master_data_monthly_payments,id',
            'tipe_pembayaran' => 'required|in:bulanan,tahunan',
            // 'contribution_total' dihapus dari validasi karena otomatis
        ]);

        // Cek pembayaran yang sudah ada
        $existing = Contribution_monthly::where([
            'house_id' => $validated['house_id'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'item_id' => $validated['item_id'],
        ])->first();

        if ($existing) {
            return response()->json(['message' => 'Pembayaran sudah ada untuk bulan dan item ini.'], 400);
        }

        // Ambil price dari item_id di tabel master_data_monthly_payments
        $item = MasterDataMonthlyPayment::find($validated['item_id']);
        if (!$item) {
            return response()->json(['message' => 'Item pembayaran tidak ditemukan'], 404);
        }

        // Simpan data pembayaran, contribution_total otomatis dari price
        $payment = Contribution_monthly::create([
            'house_id' => $validated['house_id'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'item_id' => $validated['item_id'],
            'tipe_pembayaran' => $validated['tipe_pembayaran'],
            'status_pembayaran' => 'Sudah',
            'contribution_total' => $item->price, // otomatis dari harga item
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil disimpan.',
            'data' => $payment
        ], 201);
    }

public function index(Request $request)
{
    $bulanNama = $request->query('bulan', date('F')); // contoh: "May"
    $tahun = $request->query('tahun', date('Y'));     // contoh: "2025"

    $residents = DB::table('house_residents as hr')
        ->leftJoin('houses as h', 'hr.house_id', '=', 'h.id')
        ->leftJoin('residents as r', 'hr.resident_id', '=', 'r.id')
        ->leftJoin('contribution_monthlies as cm', function ($join) use ($bulanNama, $tahun) {
            $join->on('hr.house_id', '=', 'cm.house_id')
                ->where('cm.bulan', '=', $bulanNama)
                ->where('cm.tahun', '=', $tahun);
        })
        ->leftJoin('master_data_monthly_payments as mdmp', 'cm.item_id', '=', 'mdmp.id')
        ->where(function ($query) use ($bulanNama, $tahun) {
            $query->where('hr.tipe_hunian', 'Tetap')
                ->orWhere(function ($sub) use ($bulanNama, $tahun) {
                    $sub->where('hr.bulan', $bulanNama)
                        ->where('hr.tahun', $tahun);
                });
        })
        ->select(
            'h.alamat as alamat',
            'r.name as resident_name',
            'cm.id as contribution_monthly_id',
            'mdmp.name as item_name',
            DB::raw('CASE WHEN cm.house_id IS NULL THEN "Belum" ELSE "Sudah" END as pay_this_month')
        )
        ->distinct()
        ->get();

    // Tambahkan iterasi id manual
    $residents = $residents->map(function ($item, $index) {
        $item->id = $index + 1;
        return $item;
    });

    return response()->json($residents);
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

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'house_id' => 'required|exists:houses,id',
    //         'master_data_monthly_payment_id' => 'required|exists:master_data_monthly_payments,id',
    //         'payment_status' => 'required|string',
    //         'date' => 'required|date',
    //     ]);

    //     // Simpan ke Contribution_monthly
    //     $monthly = Contribution_monthly::create([
    //         'house_id' => $request->house_id,
    //         'master_data_monthly_payment_id' => $request->master_data_monthly_payment_id,
    //         'payment_status' => $request->payment_status,
    //         'date' => $request->date,
    //     ]);

    //     // Ambil data master untuk histori
    //     $master = MasterDataMonthlyPayment::find($request->master_data_monthly_payment_id);
    //     $notes = "sudah membayar {$master->name} pada " . Carbon::parse($request->date)->format('Y-m-d H:i:s');
    //     $total = $master->price;

    //     // Simpan juga ke Contribution_payment
    //     Contribution_payment::create([
    //         'house_id' => $request->house_id,
    //         'payment_type' => 'monthly',
    //         'payment_status' => $request->payment_status,
    //         'notes' => $notes,
    //         'date' => $request->date,
    //         'total' => $total,
    //     ]);

    //     return response()->json([
    //         'message' => 'Pembayaran berhasil dicatat.',
    //         'data' => $monthly
    //     ], 201);
    // }


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
        $data = Contribution_monthly::where('id',$id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $data->delete();
        return response()->json(['message' => 'Data deleted']);
    }
}
