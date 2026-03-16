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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder_code')->unique();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // Migrate existing unique folder_code values from folder_locations
        $locations = DB::table('folder_locations')->select('folder_code', 'is_available')->get();
        foreach ($locations as $loc) {
            if (!empty($loc->folder_code)) {
                DB::table('folders')->insertOrIgnore([
                    'folder_code' => $loc->folder_code,
                    'is_available' => $loc->is_available,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
