<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('audit_logs', 'model_type')) {
                $table->string('model_type')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'model_id')) {
                $table->unsignedBigInteger('model_id')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->string('user_agent')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Migrate existing document_id to model_id
        DB::table('audit_logs')
            ->whereNotNull('document_id')
            ->whereNull('model_id')
            ->update([
                'model_id' => DB::raw('document_id'),
                'model_type' => 'App\Models\Document'
            ]);
            
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['model_type', 'model_id']);
            $table->dropColumn(['model_type', 'model_id', 'user_agent', 'updated_at']);
        });
    }
};
