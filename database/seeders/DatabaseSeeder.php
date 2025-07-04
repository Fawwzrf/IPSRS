<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Panggil seeder IPSRS Anda di sini
        $this->call([

            IpsrsLokasiSeeder::class,
            IpsrsKategoriAssetSeeder::class,
            AppNavSeeder::class,
            IpsrsKategoriAssetSeeder::class
        ]);
    }
}