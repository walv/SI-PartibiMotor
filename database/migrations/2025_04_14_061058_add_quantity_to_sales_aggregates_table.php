<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales_aggregates', function (Blueprint $table) {
            $table->integer('quantity')->nullable(); // Menambahkan kolom quantity
        });
    }

    public function down()
    {
        Schema::table('sales_aggregates', function (Blueprint $table) {
            $table->dropColumn('quantity'); // Menghapus kolom quantity jika rollback
        });
    }
};
