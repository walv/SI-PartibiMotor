<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//implementasi untuk perbaikan bug tanggal dan jam diriwayat
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('inventory_movements', function (Blueprint $table) {
        $table->dateTime('date')->change();
    });
}

public function down()
{
    Schema::table('inventory_movements', function (Blueprint $table) {
        $table->date('date')->change();
    });
}
};
