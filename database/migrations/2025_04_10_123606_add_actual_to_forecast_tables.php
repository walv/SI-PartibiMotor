<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActualToForecastTables extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom 'actual' ke 'forecast_ses' jika belum ada
        Schema::table('forecast_ses', function (Blueprint $table) {
            if (!Schema::hasColumn('forecast_ses', 'actual')) {
                $table->integer('actual')->nullable();  // Menambahkan kolom actual
            }
        });

        
    }

    public function down(): void
    {
        // Menghapus kolom 'actual' jika ada
        Schema::table('forecast_ses', function (Blueprint $table) {
            if (Schema::hasColumn('forecast_ses', 'actual')) {
                $table->dropColumn('actual');
            }
        });

      
    }
}
