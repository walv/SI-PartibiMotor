<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     //implementasi untuk memperbaiki bug dimana detail jam pada detail pembelian
     //atau stok barang tidak bekerja sesuai harapan
    public function up()
{
    Schema::table('purchases', function (Blueprint $table) {
        $table->dateTime('date')->change();
    });
}

public function down()
{
    Schema::table('purchases', function (Blueprint $table) {
        $table->date('date')->change();
    });
}

};
