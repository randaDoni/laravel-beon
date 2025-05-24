<?php

namespace App\Http\Controllers;

use App\Models\AccidentialDataMonthlyPayment;
use App\Models\Contribution_accidential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributionAccidentialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function Item(Request $request)
    {
        $data = AccidentialDataMonthlyPayment::where('bulan', $request->bulan)->where('tahun',$request->tahun)->get();
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

            $pemasukan = Contribution_accidential::where('bulan', $bulan)
                ->where('tahun', $tahun)

                ->sum('contribution_total');

            $pengeluaran = AccidentialDataMonthlyPayment::where('bulan', $bulan)
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
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'bulan' => 'required|string',
            'tahun' => 'required|integer',
            'item_id' => 'required|exists:master_data_monthly_payments,id',
            // 'tipe_pembayaran' => 'required|in:bulanan,tahunan',
            // 'contribution_total' dihapus dari validasi karena otomatis
        ]);

        // Cek pembayaran yang sudah ada
        $existing = Contribution_accidential::where([
            'house_id' => $validated['house_id'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'item_id' => $validated['item_id'],

        ])->first();

        if ($existing) {
            return response()->json(['message' => 'Pembayaran sudah ada untuk bulan dan item ini.'], 400);
        }

        // Ambil price dari item_id di tabel master_data_monthly_payments
        $item = AccidentialDataMonthlyPayment::find($validated['item_id']);
        if (!$item) {
            return response()->json(['message' => 'Item pembayaran tidak ditemukan'], 404);
        }

        // Simpan data pembayaran, contribution_total otomatis dari price
        $payment = Contribution_accidential::create([
            'house_id' => $validated['house_id'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'item_id' => $validated['item_id'],
            'tipe_pembayaran' => 'bulanan',
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
            ->leftJoin('contribution_accidentials as cm', function ($join) use ($bulanNama, $tahun) {
                $join->on('hr.house_id', '=', 'cm.house_id')
                    ->where('cm.bulan', '=', $bulanNama)
                    ->where('cm.tahun', '=', $tahun);
            })
            ->leftJoin('accidential_data_monthly_payments as mdmp', 'cm.id', '=', 'mdmp.id')
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


    /**
     * Display the specified resource.
     */
    public function show(Contribution_accidential $contribution_accidential)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contribution_accidential $contribution_accidential)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contribution_accidential $contribution_accidential)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = Contribution_accidential::where('id',$id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $data->delete();
        return response()->json(['message' => 'Data deleted']);
    }
}
