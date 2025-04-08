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
        Schema::create('forecast_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->enum('forecast_type', ['SES', 'DES', 'TES']);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('mape', 10, 2);
            $table->decimal('mae', 10, 2);
            $table->decimal('mse', 10, 2);
            $table->decimal('rmse', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_evaluations');
    }
};
