<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpsrsKategoriAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mst_kategori_asset')->insertOrIgnore([
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'kategori_asset_id' => 'PM',
                'kategori_asset_nm' => 'Peralatan Medis', 'deskripsi' => 'Semua peralatan yang digunakan langsung untuk pasien'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'kategori_asset_id' => 'IT',
                'kategori_asset_nm' => 'Infrastruktur IT', 'deskripsi' => 'Server, perangkat jaringan, sistem telepon'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'kategori_asset_id' => 'FU',
                'kategori_asset_nm' => 'Fasilitas Umum', 'deskripsi' => 'Sistem HVAC, lift, genset, plumbing'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'kategori_asset_id' => 'AA',
                'kategori_asset_nm' => 'Alat Angkutan', 'deskripsi' => 'Ambulans, kendaraan operasional'
            ],
        ]);
    }
}