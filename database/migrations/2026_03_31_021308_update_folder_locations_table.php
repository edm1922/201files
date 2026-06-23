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
        Schema::table('folder_locations', function (Blueprint $table) {
            if (Schema::hasColumn('folder_locations', 'column_code')) {
                $table->dropColumn('column_code');
            }
            if (!Schema::hasColumn('folder_locations', 'max_capacity')) {
                $table->integer('max_capacity')->default(500);
            }
            // Optional: Ensure row_name is unique if it isn't already
            // $table->unique('row_name'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folder_locations', function (Blueprint $table) {
            $table->dropColumn('max_capacity');
            $table->string('column_code')->nullable();
        });
    }
};
