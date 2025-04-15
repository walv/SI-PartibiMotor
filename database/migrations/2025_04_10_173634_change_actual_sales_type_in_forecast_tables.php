<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeActualSalesTypeInForecastTables extends Migration
{
    public function up(): void
    {
        // Untuk forecast_ses
        Schema::table('forecast_ses', function (Blueprint $table) {
            $table->decimal('actual', 10, 2)->nullable()->change(); // Sesuaikan dengan nama kolom sebenarnya
            $table->decimal('forecast', 10, 2)->change();
        });
    }

    public function down(): void
    {
        // Revert forecast_ses
        Schema::table('forecast_ses', function (Blueprint $table) {
            $table->integer('actual')->nullable()->change();
            $table->integer('forecast')->change(); // Tambahkan ini
        });
    }
}
