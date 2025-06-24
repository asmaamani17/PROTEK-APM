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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('daerah', ['BATU PAHAT', 'SEGAMAT', 'KOTA TINGGI', 'KLUANG'])->nullable()->after('role');
            $table->enum('category', ['OKU', 'WARGA EMAS', 'UMUM'])->default('UMUM')->after('daerah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['daerah', 'category']);
        });
    }
}; 