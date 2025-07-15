<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        //kesalahan penamaan kolom harga harusnya 'price' pada tabel services
        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('harga', 'price');
        });

 
        Schema::table('sale_service_details', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change(); // Pastikan tipe data sesuai
        });
    }

    public function down()
    {

        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('price', 'harga');
        });
    }
};