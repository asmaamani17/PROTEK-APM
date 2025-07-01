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
        if (!Schema::hasTable('sos_requests')) {
            Schema::create('sos_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('status')->default('requested_help');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->text('additional_info')->nullable();
                $table->timestamp('requested_at');
                $table->timestamp('responded_at')->nullable();
                $table->unsignedBigInteger('responded_by')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                    
                $table->foreign('responded_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sos_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['responded_by']);
        });
        
        Schema::dropIfExists('sos_requests');
    }};
