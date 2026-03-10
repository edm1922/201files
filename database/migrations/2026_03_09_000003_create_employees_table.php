<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('system_id')->unique()->comment('ID from the main cooperative system');
            $table->string('barcode_id')->unique()->nullable()->comment('Physical barcode value');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('status', ['active', 'awol', 'resigned'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['last_name', 'first_name']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
