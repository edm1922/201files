<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Handle existing name-less folders (give them the folder_code as a name)
        DB::table('document_folders')->whereNull('name')->update([
            'name' => DB::raw('folder_code')
        ]);

        // 2. Give 'Untitled Folder' to any that STILL have null name (unlikely but safe)
        DB::table('document_folders')->whereNull('name')->update([
            'name' => 'Untitled Folder'
        ]);

        // 3. Make name column NOT NULL
        Schema::table('document_folders', function (Blueprint $table) {
            $table->string('name', 120)->nullable(false)->change();
        });

        // 4. Restore the unique constraint on (department_id, parent_id, name)
        Schema::table('document_folders', function (Blueprint $table) {
            $table->unique(['department_id', 'parent_id', 'name'], 'doc_folders_dept_parent_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('document_folders', function (Blueprint $table) {
            $table->dropUnique('doc_folders_dept_parent_name_unique');
        });

        Schema::table('document_folders', function (Blueprint $table) {
            $table->string('name', 120)->nullable()->change();
        });
    }
};
