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
            Schema::create('rescue_cases', function (Blueprint $table) {
        $table->id();
        $table->foreignId('victim_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('rescuer_id')->nullable()->constrained('users')->onDelete('set null');
        $table->decimal('lat', 10, 7);
        $table->decimal('lng', 10, 7);
        $table->enum('status', ['new', 'assigned', 'rescued', 'not_found'])->default('new');
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rescue_cases');
    }
};
