<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpsrsKategoriAssetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_kategori_asset', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('created_by', 128)->nullable();
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('updated_by', 128)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 128)->nullable();
            $table->tinyInteger('deleted_st')->default(0);
            $table->tinyInteger('active_st')->default(1);
            $table->string('kategori_asset_id', 12)->primary();
            $table->string('kategori_asset_nm', 255);
            $table->text('deskripsi')->nullable();

            // Unique Key
            $table->unique('kategori_asset_nm', 'uk_kategori_asset_nm');

            // Set engine dan charset
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
        Schema::dropIfExists('mst_kategori_asset');
    }
}