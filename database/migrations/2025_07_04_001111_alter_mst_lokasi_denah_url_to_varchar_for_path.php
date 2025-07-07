<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstLokasiDenahUrlToVarcharForPath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_lokasi', function (Blueprint $table) {
            // Mengubah tipe kolom denah_url dari MEDIUMTEXT menjadi VARCHAR(255)
            // Ini cocok untuk menyimpan path/URL relatif ke file gambar di public/assets/denah
            // Jika path bisa lebih panjang dari 255 karakter, pertimbangkan TEXT.
            $table->string('denah_url', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_lokasi', function (Blueprint $table) {
            // Mengembalikan tipe kolom ke MEDIUMTEXT jika migrasi di-rollback
            $table->mediumText('denah_url')->nullable()->change(); // Sesuaikan ke tipe sebelumnya
        });
    }
}