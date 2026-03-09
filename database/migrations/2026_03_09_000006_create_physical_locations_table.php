<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_locations', function (Blueprint $table) {
            $table->id();
            $table->string('cabinet_id', 50);
            $table->string('rack_id', 50);
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['cabinet_id', 'rack_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_locations');
    }
};
