<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_folders')) {
            return;
        }

        Schema::table('document_folders', function (Blueprint $table) {
            try {
                $table->dropUnique('doc_folders_dept_folder_code_unique');
            } catch (Throwable $e) {
                // Constraint may already be removed in some environments.
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('document_folders')) {
            return;
        }

        Schema::table('document_folders', function (Blueprint $table) {
            $table->unique(['department_id', 'folder_code'], 'doc_folders_dept_folder_code_unique');
        });
    }
};
