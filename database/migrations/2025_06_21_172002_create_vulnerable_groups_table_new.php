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
        Schema::create('vulnerable_groups', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number', 20);
            $table->string('name', 100);
            $table->string('identification_number', 20);
            $table->enum('gender', ['LELAKI', 'PEREMPUAN']);
            $table->text('address');
            $table->string('district', 50);
            $table->string('parliament', 50)->nullable();
            $table->string('dun', 50)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->enum('disability_category', ['TERBARING', 'BERTONGKAT', 'BERKERUSI RODA', 'BOLEH BERJALAN', 'BUTA']);
            $table->enum('client_type', ['JKM', 'HOSPITAL', 'KOMUNITI', 'SUKARELA'])->default('JKM');
            $table->enum('oku_status', ['YA', 'TIDAK'])->default('TIDAK');
            $table->enum('age_group', ['KANAK-KANAK', 'DEWASA', 'WARGA EMAS']);
            $table->string('parliament_dun_code', 20)->nullable();
            $table->string('prb_serial_number', 20)->nullable();
            $table->enum('installation_status', ['BERJAYA', 'TIDAK'])->default('BERJAYA');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->date('installation_date')->nullable();
            $table->timestamps();
            
            // Add indexes
            $table->index('district', 'idx_district');
            $table->index('disability_category', 'idx_disability');
            $table->index('age_group', 'idx_age_group');
            $table->index('oku_status', 'idx_oku_status');
            $table->unique('identification_number', 'idx_identification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vulnerable_groups');
    }
};
