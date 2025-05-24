<?php

namespace App\Http\Controllers;

use App\Models\Contribution_monthly;
use App\Models\House_resident;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HouseResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    // public function index(Request $request)
    // {
    //     // Mapping nama bulan Indonesia ke angka
    //     $monthMap = [
    //         'Januari' => 1,
    //         'Februari' => 2,
    //         'Maret' => 3,
    //         'April' => 4,
    //         'Mei' => 5,
    //         'Juni' => 6,
    //         'Juli' => 7,
    //         'Agustus' => 8,
    //         'September' => 9,
    //         'Oktober' => 10,
    //         'November' => 11,
    //         'Desember' => 12,
    //     ];

    //     $bulan = $request->query('bulan', 'Januari'); // default Januari
    //     $tahun = $request->query('tahun', date('Y')); // default tahun sekarang

    //     // Ambil angka bulan dari mapping, fallback ke Januari jika tidak valid
    //     $month = $monthMap[$bulan] ?? 1;

    //     // Ambil semua rumah yang sedang dihuni (house_residents)
    //     // Asumsi kolom 'date' di house_residents adalah tanggal mulai/berlaku penghuni
    //     $residents = House_resident::with('house', 'resident')
    //         ->whereMonth('date', '<=', $month)
    //         ->whereYear('date', '<=', $tahun)
    //         ->get();

    //     // Ambil daftar house_id yang sudah membayar pada bulan dan tahun ini
    //     $paidHouseIds = Contribution_monthly::whereMonth('date', $month)
    //         ->whereYear('date', $tahun)
    //         ->pluck('house_id')
    //         ->toArray();

    //     // Susun data response
    //     $data = $residents->map(function ($res) use ($paidHouseIds) {
    //         return [
    //             'id' => $res->house_id,
    //             'alamat' => $res->house->address ?? '-',
    //             'resident_name' => $res->resident->name ?? '-',
    //             'pay_this_month' => in_array($res->house_id, $paidHouseIds) ? 'Sudah' : 'Belum',
    //         ];
    //     });

    //     return response()->json($data);
    // }

    public function index(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        if (!$bulan || !$tahun) {
            return response()->json(['message' => 'Bulan dan Tahun wajib diisi'], 400);
        }

        $monthMap = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
        ];

        if (!isset($monthMap[$bulan])) {
            return response()->json(['message' => 'Bulan tidak valid'], 400);
        }

        $bulanAngka = $monthMap[$bulan];
        $startOfMonth = Carbon::createFromDate($tahun, $bulanAngka, 1)->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        // Ambil id house_residents terbaru per house_id
        $latestHouseResidentsIdPerHouse = DB::table('house_residents')
            ->select(DB::raw('MAX(id) as latest_id'))
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->where('tipe_hunian', 'Tetap')
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('tipe_hunian', 'Kontrak')
                            ->whereDate('date_of_entry', '<=', $endOfMonth)
                            ->whereDate('exit_date', '>=', $startOfMonth);
                    });
            })
            ->groupBy('house_id');

        // Ambil data house_residents berdasarkan ID di atas
        $houseResidents = DB::table('house_residents')
            ->joinSub($latestHouseResidentsIdPerHouse, 'latest', function ($join) {
                $join->on('house_residents.id', '=', 'latest.latest_id');
            });

        // Join dengan houses dan residents
        $result = DB::table('houses')
            ->leftJoinSub($houseResidents, 'house_residents', 'houses.id', '=', 'house_residents.house_id')
            ->leftJoin('residents', 'house_residents.resident_id', '=', 'residents.id')
            ->select(
                'houses.id as id',
                'houses.alamat',
                'house_residents.id as house_resident_id',
                'house_residents.bulan',
                'house_residents.tahun',
                'house_residents.tipe_hunian',
                'house_residents.date_of_entry',
                'house_residents.exit_date',
                'residents.id as resident_id',
                'residents.name as resident_name'
            )
            ->get();

        return response()->json($result);
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
    public function store(Request $request)
    {
        $request->validate([
            'house_id' => 'required|integer',
            'resident_id' => 'required|integer',
            'tipe_hunian' => 'required|in:Tetap,Kontrak',
            'date_of_entry' => 'nullable|date',
            'exit_date' => 'nullable|date|after_or_equal:date_of_entry',
        ]);

        if ($request->tipe_hunian === 'Tetap') {
            // Hapus data sebelumnya untuk rumah ini
            House_resident::where('house_id', $request->house_id)
                ->whereNull('bulan')
                ->whereNull('tahun')
                ->delete();

            House_resident::create([
                'bulan' => null,
                'tahun' => null,
                'house_id' => $request->house_id,
                'resident_id' => $request->resident_id,
                'tipe_hunian' => $request->tipe_hunian,
                'date_of_entry' => null,
                'exit_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Data tetap berhasil disimpan.']);
        }

        // Jika kontrak
        $request->validate([
            'date_of_entry' => 'required|date',
            'exit_date' => 'required|date|after_or_equal:date_of_entry',
        ]);

        $entry = Carbon::parse($request->date_of_entry)->startOfMonth();
        $exit = Carbon::parse($request->exit_date)->startOfMonth();

        $records = [];

        // Kumpulkan bulan & tahun yang akan dihapus
        $monthsToDelete = [];
        $temp = $entry->copy();
        while ($temp <= $exit) {
            $monthsToDelete[] = [
                'bulan' => $temp->translatedFormat('F'),
                'tahun' => $temp->year,
            ];
            $temp->addMonth();
        }

        // Hapus data lama untuk kombinasi house_id, bulan, dan tahun yang sama
        foreach ($monthsToDelete as $item) {
            House_resident::where('house_id', $request->house_id)
                ->where('bulan', $item['bulan'])
                ->where('tahun', $item['tahun'])
                ->delete();
        }

        // Buat record baru
        while ($entry <= $exit) {
            $records[] = [
                'bulan' => $entry->translatedFormat('F'),
                'tahun' => $entry->year,
                'house_id' => $request->house_id,
                'resident_id' => $request->resident_id,
                'tipe_hunian' => $request->tipe_hunian,
                'date_of_entry' => $request->date_of_entry,
                'exit_date' => $request->exit_date,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $entry->addMonth();
        }

        House_resident::insert($records);

        return response()->json(['message' => 'Data kontrak berhasil disimpan dan diperbarui.']);
    }


    /**
     * Display the specified resource.
     */
    public function show(House_resident $house_resident)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(House_resident $house_resident)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'house_id' => 'required|integer',
            'resident_id' => 'required|integer',
            'tipe_hunian' => 'required|string',
            'date_of_entry' => 'required|date_format:Y-m-d',
            'exit_date' => 'required|date_format:Y-m-d|after_or_equal:date_of_entry',
        ]);

        $residentId = $request->resident_id;
        $houseId = $request->house_id;
        $tipeHunian = $request->tipe_hunian;

        $newStart = Carbon::createFromFormat('Y-m-d', $request->date_of_entry)->startOfMonth();
        $newEnd = Carbon::createFromFormat('Y-m-d', $request->exit_date)->startOfMonth();

        $bulanTahunBaru = [];
        $entry = $newStart->copy();
        while ($entry <= $newEnd) {
            $bulan = $entry->translatedFormat('F');
            $tahun = $entry->year;
            $key = strtolower($bulan) . '-' . $tahun;
            $bulanTahunBaru[$key] = ['bulan' => $bulan, 'tahun' => $tahun];
            $entry->addMonth();
        }

        // Ambil semua data house_residents untuk rumah yang sama
        $existingRecords = House_resident::where('house_id', $houseId)->get();

        $existingMap = [];
        foreach ($existingRecords as $record) {
            $key = strtolower($record->bulan) . '-' . $record->tahun;
            $existingMap[$key][] = $record;
        }

        $toUpdate = [];
        $toInsert = [];

        foreach ($bulanTahunBaru as $key => $bt) {
            if (isset($existingMap[$key])) {
                foreach ($existingMap[$key] as $record) {
                    $toUpdate[] = [
                        'id' => $record->id,
                        'bulan' => $bt['bulan'],
                        'tahun' => $bt['tahun'],
                        'house_id' => $houseId,
                        'resident_id' => $residentId,
                        'tipe_hunian' => $tipeHunian,
                        'date_of_entry' => $request->date_of_entry,
                        'exit_date' => $request->exit_date,
                        'updated_at' => now(),
                    ];
                }
            } else {
                $toInsert[] = [
                    'bulan' => $bt['bulan'],
                    'tahun' => $bt['tahun'],
                    'house_id' => $houseId,
                    'resident_id' => $residentId,
                    'tipe_hunian' => $tipeHunian,
                    'date_of_entry' => $request->date_of_entry,
                    'exit_date' => $request->exit_date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Hapus entri lama dari resident_id ini di bulan-tahun yang sudah tidak termasuk
        $allowedKeys = array_keys($bulanTahunBaru);
        House_resident::where('resident_id', $residentId)->get()
            ->filter(function ($data) use ($allowedKeys) {
                $key = strtolower($data->bulan) . '-' . $data->tahun;
                return !in_array($key, $allowedKeys);
            })
            ->each(function ($data) {
                $data->delete();
            });

        // Update data
        foreach ($toUpdate as $updateData) {
            House_resident::where('id', $updateData['id'])->update($updateData);
        }

        // Insert data baru
        if (!empty($toInsert)) {
            House_resident::insert($toInsert);
        }

        return response()->json(['message' => 'Data berhasil diperbarui dan disinkronisasi.']);
    }







    /**
     * Remove the specified resource from storage.
     */


    public function destroy($id)
    {
        // Fungsi helper konversi bulan (ada di dalam destroy supaya gak error)
        $bulanKeAngka = function(string $bulan): int {
            if (is_numeric($bulan)) {
                return (int)$bulan;
            }
            $mapping = [
                'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
                'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
                'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
            ];
            $bulanUcFirst = ucfirst(strtolower($bulan));
            return $mapping[$bulanUcFirst] ?? 0;
        };

        $data = House_resident::findOrFail($id);

        $houseId = $data->house_id;
        $residentId = $data->resident_id;
        $entryDate = Carbon::parse($data->date_of_entry);
        $exitDate = Carbon::parse($data->exit_date);
        $deletedMonth = $bulanKeAngka($data->bulan);
        $deletedYear = (int) $data->tahun;
// dd($deletedMonth);
        $relatedEntries = House_resident::where('house_id', $houseId)
            ->where('resident_id', $residentId)
            ->where('date_of_entry', $entryDate->toDateString())
            ->where('exit_date', $exitDate->toDateString())
            ->where('id', '!=', $id)
            ->get();

        foreach ($relatedEntries as $entry) {
            $entryMonth = $bulanKeAngka($entry->bulan);
            $entryYear = (int) $entry->tahun;

            if ($entryYear < $deletedYear || ($entryYear === $deletedYear && $entryMonth < $deletedMonth)) {
                // Entri sebelum bulan dihapus → ubah exit_date jadi exit_date - 1 bulan
                $newExit = Carbon::parse($entry->exit_date)->subMonth()->endOfMonth()->toDateString();
                $entry->exit_date = $newExit;
                $entry->save();
            } elseif ($entryYear > $deletedYear || ($entryYear === $deletedYear && $entryMonth > $deletedMonth)) {
                // Entri setelah bulan dihapus → ubah date_of_entry jadi date_of_entry + 1 bulan
                $newEntry = Carbon::parse($entry->date_of_entry)->addMonth()->startOfMonth()->toDateString();
                $entry->date_of_entry = $newEntry;
                $entry->save();
            }
        }

        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus dan entri lainnya diperbarui.']);
    }









}
