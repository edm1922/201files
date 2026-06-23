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
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
        });

        // Link employees to their new physical folder unit based on their current grid location
        $employees = DB::table('employees')->whereNotNull('folder_location_id')->get();
        foreach ($employees as $employee) {
            $location = DB::table('folder_locations')->where('id', $employee->folder_location_id)->first();
            if ($location && $location->folder_code) {
                $folder = DB::table('folders')->where('folder_code', $location->folder_code)->first();
                if ($folder) {
                    DB::table('employees')->where('id', $employee->id)->update(['folder_id' => $folder->id]);
                }
            }
        }

        Schema::table('folder_locations', function (Blueprint $table) {
            $table->dropColumn('folder_code');
            $table->dropColumn('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folder_locations', function (Blueprint $table) {
            $table->string('folder_code')->nullable();
            $table->boolean('is_available')->default(true);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropColumn('folder_id');
        });
    }
};
