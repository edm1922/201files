<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable()->comment('Null means currently deployed');
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['employee_id', 'is_current']);
            $table->index(['company_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
