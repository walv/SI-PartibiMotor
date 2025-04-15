<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  // Pastikan DB diimport

class AddPeriodToForecastTables extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom 'period' ke tabel 'forecast_ses' jika belum ada
        Schema::table('forecast_ses', function (Blueprint $table) {
            if (!Schema::hasColumn('forecast_ses', 'period')) {
                $table->date('period')->nullable()->default(DB::raw('CURRENT_DATE'));  // Kolom period dengan nilai default
            }
            // Jika kolom actual_sales ada di tabel forecast_ses, pastikan juga itu ada
            if (!Schema::hasColumn('forecast_ses', 'actual_sales')) {
                $table->decimal('actual_sales', 10, 2)->nullable();
            }
        });

    }

    public function down(): void
    {
        // Menghapus kolom 'period' dan 'actual_sales' jika ada
        Schema::table('forecast_ses', function (Blueprint $table) {
            if (Schema::hasColumn('forecast_ses', 'period')) {
                $table->dropColumn('period');
            }
            if (Schema::hasColumn('forecast_ses', 'actual_sales')) {
                $table->dropColumn('actual_sales');
            }
        });

    
    }
}
