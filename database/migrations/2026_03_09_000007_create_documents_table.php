<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); // Creates owner_id and owner_type
            $table->foreignId('document_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('physical_location_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('system_filename')->unique();
            $table->integer('page_count')->default(1);
            $table->integer('file_size_bytes')->default(0);
            $table->string('mime_type', 100)->default('application/pdf');
            $table->string('status', 20)->default('active');
            $table->date('date_received')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('ocr_text')->nullable()->comment('Future: OCR extracted text');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_type', 'owner_id', 'document_type_id'], 'docs_owner_type_idx');
            $table->index('status');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
