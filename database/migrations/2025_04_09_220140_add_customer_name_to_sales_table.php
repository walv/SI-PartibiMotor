<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_name')->nullable(); // Menambahkan kolom customer_name
        });
    }
    
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('customer_name'); // Menghapus kolom customer_name saat rollback
        });
    }
};
