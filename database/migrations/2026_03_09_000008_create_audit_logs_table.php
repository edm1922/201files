<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action', 50)->comment('view, download, upload, edit, delete, restore, login, logout');
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('changes')->nullable()->comment('Before/after for edits');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['document_id', 'action']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
