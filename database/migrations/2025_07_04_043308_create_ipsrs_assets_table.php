<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpsrsAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('created_by', 128)->nullable();
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('updated_by', 128)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 128)->nullable();
            $table->tinyInteger('deleted_st')->default(0);
            $table->tinyInteger('active_st')->default(1);
            $table->string('asset_id', 12)->primary();
            $table->string('asset_nm', 255);
            $table->string('jenis', 255)->nullable();
            $table->string('no_seri', 64)->nullable()->unique(); // Unique untuk barcode
            $table->string('merk', 64)->nullable();
            $table->string('model', 128)->nullable();
            $table->string('perolehan_tipe', 64)->nullable();
            $table->date('perolehan_tgl')->nullable();
            $table->integer('umur_tahun')->nullable();
            $table->enum('status', ['aktif', 'perbaikan', 'nonaktif', 'dihapus'])->default('aktif');
            $table->date('pm_berikutnya')->nullable();
            $table->string('lokasi_id', 12)->nullable(); // Foreign Key
            $table->string('kategori_asset_id', 12)->nullable(); // Foreign Key

            // Foreign Keys (akan diabaikan oleh MyISAM, tapi didefinisikan untuk kejelasan skema)
            $table->foreign('lokasi_id')->references('lokasi_id')->on('mst_lokasi')->onDelete('set null');
            $table->foreign('kategori_asset_id')->references('kategori_asset_id')->on('mst_kategori_asset')->onDelete('set null');

            // Set engine dan charset (penting untuk MyISAM)
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset');
    }
}
