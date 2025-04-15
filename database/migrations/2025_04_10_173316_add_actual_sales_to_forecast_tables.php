<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActualSalesToForecastTables extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom 'actual_sales' ke 'forecast_ses' jika belum ada
        Schema::table('forecast_ses', function (Blueprint $table) {
            if (!Schema::hasColumn('forecast_ses', 'actual_sales')) {
                $table->decimal('actual_sales', 10, 2)->nullable()->default(0);  // Menambahkan kolom actual_sales dengan default 0
            }
        });
    }

    public function down(): void
    {
        // Menghapus kolom 'actual_sales' jika ada
        Schema::table('forecast_ses', function (Blueprint $table) {
            if (Schema::hasColumn('forecast_ses', 'actual_sales')) {
                $table->dropColumn('actual_sales');
            }
        });

    }
}
