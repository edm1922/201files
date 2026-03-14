<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Rack;
use App\Models\Row;
use App\Models\Column;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename tables in correct order to avoid collision
        Schema::rename('racks', 'columns');
        Schema::rename('cabinets', 'racks');
        Schema::rename('slots', 'folders');

        // 2. Create rows table
        Schema::create('rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 3. Add row_id to columns
        Schema::table('columns', function (Blueprint $table) {
            $table->dropForeign('racks_cabinet_id_foreign');
            $table->renameColumn('cabinet_id', 'rack_id');
            // Drop old unique constraint using exact index name
            $table->dropUnique('racks_cabinet_id_rack_code_unique');
            $table->renameColumn('rack_code', 'column_code');
            $table->foreignId('row_id')->nullable()->after('rack_id')->constrained()->cascadeOnDelete();
        });

        // Add rack foreign key back
        Schema::table('columns', function (Blueprint $table) {
            $table->foreign('rack_id')->references('id')->on('racks')->cascadeOnDelete();
        });

        // 4. Migrate Data: Extract Row from Column Code (e.g. "A1" -> Row "A", Column "1")
        $columns = DB::table('columns')->get();
        foreach ($columns as $column) {
            $code = $column->column_code;
            $rowName = preg_replace('/[^a-zA-Z]/', '', $code); // e.g. "A"
            $colNumber = preg_replace('/[^0-9]/', '', $code);  // e.g. "1"
            
            if (empty($rowName)) {
                $rowName = 'A'; // Defaults just in case
            }
            if (empty($colNumber)) {
                $colNumber = $code; // Leave it as is if there's no number
            }

            // Find or create Row
            $row = DB::table('rows')
                ->where('rack_id', $column->rack_id)
                ->where('name', $rowName)
                ->first();

            if (!$row) {
                $rowId = DB::table('rows')->insertGetId([
                    'rack_id' => $column->rack_id,
                    'name' => $rowName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $rowId = $row->id;
            }

            DB::table('columns')->where('id', $column->id)->update([
                'row_id' => $rowId,
                'column_code' => $colNumber,
            ]);
        }

        // 5. Update foreign keys in folders (formerly slots)
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign('slots_rack_id_foreign');
            $table->renameColumn('rack_id', 'column_id');
        });
        Schema::table('folders', function (Blueprint $table) {
             $table->foreign('column_id')->references('id')->on('columns')->cascadeOnDelete();
        });

        // 6. Update references to slot_id in employees and documents, departments
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'slot_id')) {
                $table->dropForeign(['slot_id']);
                $table->renameColumn('slot_id', 'folder_id');
                $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'slot_id')) {
                $table->dropForeign(['slot_id']);
                $table->renameColumn('slot_id', 'folder_id');
                $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
            }
        });

        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'slot_id')) {
                $table->dropForeign(['slot_id']);
                $table->renameColumn('slot_id', 'folder_id');
                $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Reverting this complex migration is tough and often not needed for simple changes if we go forward
        // However, a best effort could be made if necessary.
    }
};
