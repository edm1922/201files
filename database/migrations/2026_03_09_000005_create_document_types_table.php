<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->enum('target', ['employee', 'department'])->default('employee');
            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_required')->default(false);
            $table->integer('max_pages')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
