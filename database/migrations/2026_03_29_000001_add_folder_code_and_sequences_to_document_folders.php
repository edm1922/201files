<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_folders', function (Blueprint $table) {
            $table->string('folder_code', 40)->nullable();
            $table->index(['department_id', 'folder_code'], 'doc_folders_dept_code_idx');
            $table->unique(['department_id', 'folder_code'], 'doc_folders_dept_folder_code_unique');
        });

        Schema::create('department_folder_sequences', function (Blueprint $table) {
            $table->foreignId('department_id')->primary()->constrained('departments')->cascadeOnDelete();
            $table->unsignedInteger('next_number')->default(1);
            $table->timestamps();
        });

        $departments = DB::table('departments')
            ->select('id', 'code', 'folder_code')
            ->orderBy('id')
            ->get();

        foreach ($departments as $department) {
            $prefix = $this->extractPrefix($department->folder_code, $department->code, (int) $department->id);
            $next = 1;

            $folderIds = DB::table('document_folders')
                ->where('department_id', $department->id)
                ->orderBy('id')
                ->pluck('id');

            foreach ($folderIds as $folderId) {
                DB::table('document_folders')
                    ->where('id', $folderId)
                    ->update([
                        'folder_code' => sprintf('%s-%04d', $prefix, $next),
                        'updated_at' => now(),
                    ]);

                $next++;
            }

            DB::table('department_folder_sequences')->updateOrInsert(
                ['department_id' => $department->id],
                [
                    'next_number' => $next,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('department_folder_sequences');

        Schema::table('document_folders', function (Blueprint $table) {
            $table->dropUnique('doc_folders_dept_folder_code_unique');
            $table->dropIndex('doc_folders_dept_code_idx');
            $table->dropColumn('folder_code');
        });
    }

    private function extractPrefix(?string $departmentFolderCode, ?string $departmentCode, int $departmentId): string
    {
        $departmentFolderCode = (string) $departmentFolderCode;

        if (preg_match('/^(.*)-0+$/', $departmentFolderCode, $matches) === 1) {
            return $matches[1];
        }

        $normalizedDepartmentCode = strtoupper((string) $departmentCode);
        if ($normalizedDepartmentCode !== '') {
            return 'CSC-' . $normalizedDepartmentCode;
        }

        return 'CSC-DEPT' . $departmentId;
    }
};
