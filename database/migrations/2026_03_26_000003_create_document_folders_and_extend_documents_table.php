<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('document_folders')->nullOnDelete();
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['department_id', 'parent_id', 'name'], 'doc_folders_dept_parent_name_unique');
            $table->index(['department_id', 'name'], 'doc_folders_dept_name_idx');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('document_folder_id')->nullable()->after('folder_location_id')->constrained('document_folders')->nullOnDelete();
            $table->enum('upload_mode', ['standard', 'scan_packet'])->default('standard')->after('mime_type');
            $table->json('source_filenames')->nullable()->after('metadata');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['document_folder_id']);
            $table->dropColumn(['document_folder_id', 'upload_mode', 'source_filenames']);
        });

        Schema::dropIfExists('document_folders');
    }
};
