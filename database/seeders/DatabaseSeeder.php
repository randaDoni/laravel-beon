<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123')
        ]);

         DB::table('residents')->insert([
            [
                'name' => 'Agus Santoso',
                'ktp' => 'ktp_photos/agus.jpg', // path ke file gambar
                'contract_status' => 'kontrak',
                'telp_number' => '081234567890',
                'married_status' => 'sudah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rina Melati',
                'ktp' => 'ktp_photos/rina.jpg',
                'contract_status' => 'tetap',
                'telp_number' => '081298765432',
                'married_status' => 'belum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Hartono',
                'ktp' => 'ktp_photos/budi.jpg', // boleh null sesuai validasi
                'contract_status' => 'kontrak',
                'telp_number' => '085612345678',
                'married_status' => 'sudah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('houses')->insert([
            [
                'alamat' => 'jl.Delima no 17',
                'resident_status' => 'dihuni',
                 'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'alamat' => 'jl.Delima no 19',
                'resident_status' => 'tidak dihuni',
                 'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'alamat' => 'jl.Delima no 20',
                'resident_status' => 'tidak dihuni',
                'created_at' => now(),
                'updated_at' => now(),

            ],
        ]);

        DB::table('house_residents')->insert([
            [
                'bulan' => null,
                'tahun' => null,
                'resident_id' => 2,
                'house_id' => 1,
                'tipe_hunian' => 'Tetap',
                'date_of_entry' => null,
                'exit_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bulan' => 'May',
                'tahun' => '2025',
                'resident_id' => 1,
                'house_id' => 2,
                'tipe_hunian' => 'Kontrak',
                'date_of_entry' => Carbon::create(2025, 1, 15),
                'exit_date' => Carbon::create(2025, 2, 15),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bulan' => 'June',
                'tahun' => '2025',
                'resident_id' => 1,
                'house_id' => 2,
                'tipe_hunian' => 'Kontrak',
                'date_of_entry' => Carbon::create(2025, 1, 15),
                'exit_date' => Carbon::create(2025, 2, 15),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Tambah data lainnya jika diperlukan
        ]);

        DB::table('master_data_monthly_payments')->insert([
            [
                'name' => 'satpam',
                'price' => 100000
            ]
        ]);

        DB::table('accidential_data_monthly_payments')->insert([
            [
                'name' => 'air',
                'price' => 15000
            ]
        ]);
    }
}
