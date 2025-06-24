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
        Schema::dropIfExists('vulnerable_groups');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only drops the table, so we don't need to implement the down method
        // as we can't reliably recreate the table with its original structure
    }
};
