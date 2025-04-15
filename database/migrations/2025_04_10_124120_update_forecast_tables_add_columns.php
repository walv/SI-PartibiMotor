<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForecastTablesAddColumns extends Migration
{
    public function up(): void
    {
        // forecast_ses
        Schema::table('forecast_ses', function (Blueprint $table) {
            if (!Schema::hasColumn('forecast_ses', 'forecast')) {
                $table->float('forecast')->nullable();
            }
            if (!Schema::hasColumn('forecast_ses', 'actual')) {
                $table->integer('actual')->nullable();
            }
        });

    }

    public function down(): void
    {
        Schema::table('forecast_ses', function (Blueprint $table) {
            $table->dropColumn(['forecast', 'actual']);
        });

    }
}
