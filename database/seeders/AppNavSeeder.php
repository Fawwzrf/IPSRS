<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppNavSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('app_nav')->insertOrIgnore([
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.04.02', 'nav_parent' => '03.04', 'nav_nm' => 'Laporan Perbaikan', 'nav_url' => 'ipsrs/laporan/perbaikan', 'icon' => 'fas fa-chart-bar'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.04.01', 'nav_parent' => '03.04', 'nav_nm' => 'Laporan Aset', 'nav_url' => 'ipsrs/laporan/aset', 'icon' => 'fas fa-chart-pie'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.04', 'nav_parent' => '03', 'nav_nm' => 'Laporan', 'nav_url' => '#', 'icon' => 'fas fa-chart-bar'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.03.03', 'nav_parent' => '03.03', 'nav_nm' => 'Log Kerja Perbaikan', 'nav_url' => 'ipsrs/log_kerja/perbaikan', 'icon' => 'fas fa-book'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.03.02', 'nav_parent' => '03.03', 'nav_nm' => 'Order Kerja Perbaikan', 'nav_url' => 'ipsrs/order_kerja/perbaikan', 'icon' => 'fas fa-cogs'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.03.01', 'nav_parent' => '03.03', 'nav_nm' => 'Permintaan Komplain', 'nav_url' => 'ipsrs/permintaan_komplain', 'icon' => 'fas fa-exclamation-triangle'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.03', 'nav_parent' => '03', 'nav_nm' => 'Perbaikan', 'nav_url' => '#', 'icon' => 'fas fa-tools'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-02 21:32:02', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_at' => null, 'deleted_by' => null, 'deleted_st' => 0, 'active_st' => 1,
                'nav_id' => '03.02.03', 'nav_parent' => '03.02', 'nav_nm' => 'Histori Pemeliharaan', 'nav_url' => 'ipsrs/log_kerja/pemeliharaan', 'icon' => 'fas fa-history'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-02 21:31:45', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.02.02', 'nav_parent' => '03.02',
                'nav_nm' => 'Order Kerja Pemeliharaan', 'nav_url' => 'ipsrs/order_kerja/pemeliharaan', 'icon' => 'fas fa-tasks'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-02 21:31:24', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.02.01', 'nav_parent' => '03.02',
                'nav_nm' => 'Jadwal Pemeliharaan', 'nav_url' => 'ipsrs/jadwal_pm', 'icon' => 'fas fa-clipboard-list'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.02', 'nav_parent' => '03', 'nav_nm' => 'Pemeliharaan', 'nav_url' => '#', 'icon' => 'fas fa-calendar-alt'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 23:15:37', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.01.05', 'nav_parent' => '03.01',
                'nav_nm' => 'Penerimaan Sparepart', 'nav_url' => 'ipsrs/penerimaansparepart', 'icon' => 'fas fa-dolly'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 18:13:14', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.01.01', 'nav_parent' => '03.01',
                'nav_nm' => 'Data Aset', 'nav_url' => 'ipsrs/asset', 'icon' => 'fas fa-toolbox'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 18:13:31', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.01.04', 'nav_parent' => '03.01',
                'nav_nm' => 'Data Sparepart', 'nav_url' => 'ipsrs/sparepart', 'icon' => 'fas fa-wrench'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 22:30:13', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.01.02', 'nav_parent' => '03.01',
                'nav_nm' => 'Kategori Aset', 'nav_url' => 'ipsrs/kategoriasset', 'icon' => 'fas fa-tags'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 18:13:41', 'updated_by' => 'PEGAWAI TESTER',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.01.03', 'nav_parent' => '03.01',
                'nav_nm' => 'Data Lokasi', 'nav_url' => 'ipsrs/lokasi', 'icon' => 'fas fa-map-marker-alt'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.01', 'nav_parent' => '03', 'nav_nm' => 'Master Data', 'nav_url' => '#', 'icon' => 'fas fa-database'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03', 'nav_parent' => null, 'nav_nm' => 'IPSRS', 'nav_url' => '#', 'icon' => 'fas fa-cogs'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.00', 'nav_parent' => '03', 'nav_nm' => 'Dashboard IPSRS', 'nav_url' => 'ipsrs/dashboard', 'icon' => 'fas fa-tachometer-alt'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.04.03', 'nav_parent' => '03.04',
                'nav_nm' => 'Laporan Pemeliharaan', 'nav_url' => 'ipsrs/laporan/pemeliharaan', 'icon' => 'fas fa-chart-area'
            ],
            [
                'created_at' => '2025-07-03 04:29:38', 'created_by' => 'System', 'updated_at' => '2025-07-03 04:29:38', 'updated_by' => 'System',
                'deleted_st' => 0, 'active_st' => 1, 'nav_id' => '03.04.04', 'nav_parent' => '03.04',
                'nav_nm' => 'Laporan Biaya', 'nav_url' => 'ipsrs/laporan/biaya', 'icon' => 'fas fa-dollar-sign'
            ],
        ]);
    }
}