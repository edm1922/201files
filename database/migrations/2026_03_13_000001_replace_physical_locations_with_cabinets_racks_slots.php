<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create cabinets table ──
        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // ── 2. Create racks table ──
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained()->cascadeOnDelete();
            $table->string('rack_code', 50);
            $table->timestamps();

            $table->unique(['cabinet_id', 'rack_code']);
        });

        // ── 3. Create slots table ──
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained()->cascadeOnDelete();
            $table->string('folder_code')->unique();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // ── 4. Drop old FK on employees, add slot_id ──
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['physical_location_id']);
            $table->dropColumn('physical_location_id');
            $table->dropColumn('folder_code');
            $table->foreignId('slot_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
        });

        // ── 5. Drop old FK on documents, add slot_id ──
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['physical_location_id']);
            $table->dropColumn('physical_location_id');
            $table->foreignId('slot_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
        });

        // ── 6. Drop old physical_locations table ──
        Schema::dropIfExists('physical_locations');
    }

    public function down(): void
    {
        // ── Reverse: Recreate physical_locations ──
        Schema::create('physical_locations', function (Blueprint $table) {
            $table->id();
            $table->string('cabinet_id', 50);
            $table->string('rack_id', 50);
            $table->string('label')->nullable();
            $table->timestamps();
            $table->unique(['cabinet_id', 'rack_id']);
        });

        // ── Reverse employees ──
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
            $table->foreignId('physical_location_id')->nullable()->constrained('physical_locations')->nullOnDelete();
            $table->string('folder_code')->nullable()->after('status');
        });

        // ── Reverse documents ──
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
            $table->foreignId('physical_location_id')->nullable()->constrained()->onDelete('set null');
        });

        // ── Drop new tables ──
        Schema::dropIfExists('slots');
        Schema::dropIfExists('racks');
        Schema::dropIfExists('cabinets');
    }
};
