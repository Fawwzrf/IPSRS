<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpsrsLokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mst_lokasi')->insertOrIgnore([
            // Gedung (Level 1)
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '01', 'parent_lokasi_id' => null,
                'lokasi_nm' => 'Gedung Utama RS', 'tipe_lokasi' => 'Gedung', 'deskripsi' => 'Gedung utama rumah sakit', 'denah_url' => 'url/denah/gedung_utama.png'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '02', 'parent_lokasi_id' => null,
                'lokasi_nm' => 'Gedung IGD/UGD', 'tipe_lokasi' => 'Gedung', 'deskripsi' => 'Gedung Instalasi Gawat Darurat', 'denah_url' => 'url/denah/gedung_igd.png'
            ],
            // Lantai (Level 2 - Child dari Gedung)
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '01.01', 'parent_lokasi_id' => '01',
                'lokasi_nm' => 'Lantai 1 Utama', 'tipe_lokasi' => 'Lantai', 'deskripsi' => 'Lantai 1 Gedung Utama', 'denah_url' => 'url/denah/gedung_utama_lantai1.png'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '01.02', 'parent_lokasi_id' => '01',
                'lokasi_nm' => 'Lantai 2 Utama', 'tipe_lokasi' => 'Lantai', 'deskripsi' => 'Lantai 2 Gedung Utama', 'denah_url' => 'url/denah/gedung_utama_lantai2.png'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '02.01', 'parent_lokasi_id' => '02',
                'lokasi_nm' => 'Lantai Dasar IGD', 'tipe_lokasi' => 'Lantai', 'deskripsi' => 'Lantai Dasar Gedung IGD', 'denah_url' => 'url/denah/gedung_igd_lantai_dasar.png'
            ],
            // Ruangan (Level 3 - Child dari Lantai)
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '01.01.01', 'parent_lokasi_id' => '01.01',
                'lokasi_nm' => 'Ruang Admisi Pasien', 'tipe_lokasi' => 'Ruangan', 'deskripsi' => 'Ruang Pendaftaran Pasien', 'denah_url' => 'url/denah/ruang_admisi.png'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '01.01.02', 'parent_lokasi_id' => '01.01',
                'lokasi_nm' => 'Ruang Tunggu Poli', 'tipe_lokasi' => 'Ruangan', 'deskripsi' => 'Area tunggu pasien di poli', 'denah_url' => 'url/denah/ruang_tunggu.png'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '01.02.01', 'parent_lokasi_id' => '01.02',
                'lokasi_nm' => 'Ruang Operasi A', 'tipe_lokasi' => 'Ruangan', 'deskripsi' => 'Ruang Operasi Bedah Utama A', 'denah_url' => 'url/denah/ruang_operasi_a.png'
            ],
            [
                'created_at' => now(), 'created_by' => 'Seeder', 'updated_at' => now(), 'updated_by' => 'Seeder',
                'deleted_st' => 0, 'active_st' => 1, 'lokasi_id' => '02.01.01', 'parent_lokasi_id' => '02.01',
                'lokasi_nm' => 'Ruang Resusitasi IGD', 'tipe_lokasi' => 'Ruangan', 'deskripsi' => 'Ruang tindakan resusitasi di IGD', 'denah_url' => 'url/denah/ruang_resusitasi_igd.png'
            ],
        ]);
    }
}