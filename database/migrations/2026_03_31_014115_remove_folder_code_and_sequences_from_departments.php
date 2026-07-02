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
        // 1. Drop the unused sequence table
        Schema::dropIfExists('department_folder_sequences');

        // 2. Cleanup redundant columns in departments table
        Schema::table('departments', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['folder_location_id']);
            
            // Drop unique index
            $table->dropUnique('departments_folder_code_unique');

            // Drop columns
            $table->dropColumn(['folder_code', 'folder_location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create the sequence table if rolled back
        Schema::create('department_folder_sequences', function (Blueprint $table) {
            $table->foreignId('department_id')->primary()->constrained('departments')->cascadeOnDelete();
            $table->unsignedInteger('last_sequence_number')->default(0);
            $table->timestamps();
        });

        // Re-add the original columns
        Schema::table('departments', function (Blueprint $table) {
            $table->string('folder_code')->nullable()->unique();
            $table->foreignId('folder_location_id')->nullable()->constrained('folder_locations')->nullOnDelete();
        });
    }
};
