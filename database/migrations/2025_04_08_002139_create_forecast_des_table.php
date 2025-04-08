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
        Schema::create('forecast_des', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->date('period');
            $table->integer('actual_sales');
            $table->decimal('forecast_value', 10, 2);
            $table->float('alpha');
            $table->float('beta');
            $table->decimal('level', 10, 2);
            $table->decimal('trend', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_des');
    }
};
