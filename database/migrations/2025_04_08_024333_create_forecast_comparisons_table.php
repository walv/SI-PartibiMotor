<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forecast_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('ses_mape', 10, 2);
            $table->decimal('des_mape', 10, 2);
            $table->decimal('tes_mape', 10, 2);
            $table->enum('best_method', ['SES', 'DES', 'TES']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_comparisons');
    }
};
