<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the unique constraint on (department_id, parent_id, name)
        Schema::table('document_folders', function (Blueprint $table) {
            $table->dropUnique('doc_folders_dept_parent_name_unique');
        });

        // Make name nullable (folders can be identified by code alone)
        Schema::table('document_folders', function (Blueprint $table) {
            $table->string('name', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('document_folders', function (Blueprint $table) {
            $table->string('name', 120)->nullable(false)->change();
        });

        Schema::table('document_folders', function (Blueprint $table) {
            $table->unique(['department_id', 'parent_id', 'name'], 'doc_folders_dept_parent_name_unique');
        });
    }
};
