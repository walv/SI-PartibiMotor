<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
   {
        // Hapus jika ada duplikat username
        $duplicates = DB::table('users')
            ->select('username')
            ->groupBy('username')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('username');

        foreach ($duplicates as $username) {
            $users = DB::table('users')
                ->where('username', $username)
                ->orderBy('id')
                ->get();

            foreach ($users as $index => $user) {
                if ($index > 0) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['username' => $username . '_' . ($index + 1)]);
                }
            }
        }

        //  Tambahkan unique constraint pada username
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 40)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('users', function (Blueprint $table) {
            $table->string('username', 40)->nullable()->change();
        });
    }
};
