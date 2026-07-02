<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop the old polymorphic index
            $table->dropIndex('docs_owner_type_idx');
            
            // Drop the old morph columns
            $table->dropColumn(['owner_type', 'owner_id']);
            
            // Add the new department_id foreign key
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            
            $table->index(['owner_type', 'owner_id', 'document_type_id'], 'docs_owner_type_idx');
        });
    }
};
