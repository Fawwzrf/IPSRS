<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpsrsLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_lokasi', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('created_by', 128)->nullable();
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('updated_by', 128)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 128)->nullable();
            $table->tinyInteger('deleted_st')->default(0);
            $table->tinyInteger('active_st')->default(1);
            $table->string('lokasi_id', 12)->primary();
            $table->string('parent_lokasi_id', 12)->nullable();
            $table->string('lokasi_nm', 255);
            $table->enum('tipe_lokasi', ['Gedung', 'Lantai', 'Ruangan']);
            $table->text('deskripsi')->nullable();
            $table->text('denah_url')->nullable();

            // Unique Key (pastikan ini sesuai dengan UNIQUE KEY di SQL Anda)
            $table->unique(['parent_lokasi_id', 'tipe_lokasi', 'lokasi_nm'], 'uk_lokasi_parent_tipe_nm');
            
            // Foreign Key (akan diabaikan oleh MyISAM, tapi didefinisikan untuk kejelasan skema)
            // Pastikan ini ditambahkan setelah primary key 'lokasi_id' didefinisikan
            $table->foreign('parent_lokasi_id')->references('lokasi_id')->on('mst_lokasi')->onDelete('set null');

            // Set engine dan charset (penting untuk MyISAM)
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci'; // Default collation for latin1
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_lokasi');
    }
}