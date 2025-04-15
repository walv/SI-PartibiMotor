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
        Schema::create('forecast_ses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->date('period'); // Ubah menjadi dateTime jika perlu presisi waktu
            $table->decimal('actual', 10, 2)->nullable(); // Tambahkan nullable
            $table->decimal('forecast', 10, 2);
            $table->decimal('alpha', 4, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_ses');
    }
};
