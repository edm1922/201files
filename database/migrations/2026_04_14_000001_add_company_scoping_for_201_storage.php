<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->unsignedInteger('sequence_number')->nullable();
            $table->unique(['company_id', 'sequence_number'], 'folders_company_sequence_unique');
        });

        Schema::table('folder_locations', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->unsignedInteger('range_start')->nullable();
            $table->unsignedInteger('range_end')->nullable();
            $table->unique(['company_id', 'row_name'], 'folder_locations_company_row_unique');
        });

        Schema::create('company_folder_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained('companies')->cascadeOnDelete();
            $table->unsignedInteger('next_number')->default(1);
            $table->timestamps();
        });

        $folderCompanies = DB::table('employees')
            ->select('folder_id', 'company_id')
            ->whereNotNull('folder_id')
            ->whereNotNull('company_id')
            ->get();

        foreach ($folderCompanies as $assignment) {
            DB::table('folders')
                ->where('id', (int) $assignment->folder_id)
                ->whereNull('company_id')
                ->update(['company_id' => (int) $assignment->company_id]);
        }

        $folders = DB::table('folders')
            ->select('id', 'folder_code')
            ->get();

        foreach ($folders as $folder) {
            if (preg_match('/(\d+)$/', (string) $folder->folder_code, $matches) !== 1) {
                continue;
            }

            DB::table('folders')
                ->where('id', (int) $folder->id)
                ->update(['sequence_number' => (int) $matches[1]]);
        }

        $locations = DB::table('folder_locations')
            ->select('id', 'row_name', 'max_capacity')
            ->get();

        foreach ($locations as $location) {
            $rowIndex = $this->rowNameToIndex((string) $location->row_name);

            if ($rowIndex <= 0) {
                continue;
            }

            $capacity = (int) ($location->max_capacity ?? 500);
            $rangeStart = (($rowIndex - 1) * $capacity) + 1;
            $rangeEnd = $rowIndex * $capacity;

            DB::table('folder_locations')
                ->where('id', (int) $location->id)
                ->update([
                    'range_start' => $rangeStart,
                    'range_end' => $rangeEnd,
                ]);

            $locationCompanyIds = DB::table('employees')
                ->where('folder_location_id', (int) $location->id)
                ->whereNotNull('company_id')
                ->distinct()
                ->pluck('company_id');

            if ($locationCompanyIds->count() === 1) {
                DB::table('folder_locations')
                    ->where('id', (int) $location->id)
                    ->update(['company_id' => (int) $locationCompanyIds->first()]);
            }
        }

        $fallbackCompanyId = DB::table('companies')->min('id');
        if ($fallbackCompanyId) {
            DB::table('folder_locations')
                ->whereNull('company_id')
                ->update(['company_id' => (int) $fallbackCompanyId]);
        }

        $companies = DB::table('companies')->select('id', 'code')->get();
        $timestamp = now();

        foreach ($companies as $company) {
            $prefix = 'CSC-'.strtoupper(trim((string) $company->code)).'-';

            $maxSequence = DB::table('folders')
                ->where('folder_code', 'like', $prefix.'%')
                ->max('sequence_number');

            DB::table('company_folder_sequences')->insert([
                'company_id' => (int) $company->id,
                'next_number' => ((int) $maxSequence) > 0 ? ((int) $maxSequence) + 1 : 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        if ($fallbackCompanyId) {
            DB::table('folders')
                ->whereNull('company_id')
                ->update(['company_id' => (int) $fallbackCompanyId]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_folder_sequences');

        Schema::table('folder_locations', function (Blueprint $table) {
            $table->dropUnique('folder_locations_company_row_unique');
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'range_start', 'range_end']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropUnique('folders_company_sequence_unique');
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'sequence_number']);
        });
    }

    private function rowNameToIndex(string $rowName): int
    {
        $name = strtoupper($rowName);
        $index = 0;

        for ($i = 0, $len = strlen($name); $i < $len; $i++) {
            $char = $name[$i];

            if (! ctype_alpha($char)) {
                continue;
            }

            $index = ($index * 26) + (ord($char) - 64);
        }

        return $index;
    }
};
