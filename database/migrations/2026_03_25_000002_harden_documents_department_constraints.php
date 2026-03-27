<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('documents')->whereNull('department_id')->exists()) {
            throw new \RuntimeException('Cannot enforce non-null department_id while null rows exist.');
        }

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->index(['department_id', 'document_type_id', 'status'], 'docs_dept_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('docs_dept_type_status_idx');
            $table->dropForeign(['department_id']);
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
        });
    }
};
