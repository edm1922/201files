<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the new flat table if not exists
        if (!Schema::hasTable('folder_locations')) {
            Schema::create('folder_locations', function (Blueprint $table) {
                $table->id();
                $table->string('row_name'); // e.g. "A"
                $table->string('column_code'); // e.g. "1"
                $table->string('folder_code')->unique(); // e.g. "CSC-HR-0001"
                $table->boolean('is_available')->default(true);
                $table->timestamps();
            });
        }

        // 2. Migrate Data only if legacy tables still exist
        $idMapping = []; // mapping old folders.id to new folder_locations.id
        if (Schema::hasTable('folders') && Schema::hasTable('columns') && Schema::hasTable('rows')) {
            // Check if folder_locations is already populated (it might be if it partially ran)
            if (DB::table('folder_locations')->count() == 0) {
                $legacyFolders = DB::table('folders')
                    ->join('columns', 'folders.column_id', '=', 'columns.id')
                    ->join('rows', 'columns.row_id', '=', 'rows.id')
                    ->select(
                        'folders.id as old_id',
                        'rows.name as row_name',
                        'columns.column_code',
                        'folders.folder_code',
                        'folders.is_available',
                        'folders.created_at',
                        'folders.updated_at'
                    )
                    ->get();

                foreach ($legacyFolders as $lf) {
                    $newId = DB::table('folder_locations')->insertGetId([
                        'row_name' => $lf->row_name,
                        'column_code' => $lf->column_code,
                        'folder_code' => $lf->folder_code,
                        'is_available' => $lf->is_available,
                        'created_at' => $lf->created_at,
                        'updated_at' => $lf->updated_at,
                    ]);
                    $idMapping[$lf->old_id] = $newId;
                }
            } else {
                // If folder_locations is populated, we need to reconstruct idMapping to update foreign keys
                // This assumes folder_code is unique across the transition
                $legacyFolders = DB::table('folders')->get();
                foreach ($legacyFolders as $lf) {
                    $match = DB::table('folder_locations')->where('folder_code', $lf->folder_code)->first();
                    if ($match) {
                        $idMapping[$lf->id] = $match->id;
                    }
                }
            }
        }

        // 3. Update dependent tables defensivly
        $targetTables = ['employees', 'documents', 'departments'];
        foreach ($targetTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'folder_location_id')) {
                    $table->unsignedBigInteger('folder_location_id')->nullable();
                    // Positioning after folder_id or just at the end
                    if (Schema::hasColumn($tableName, 'folder_id')) {
                        // Laravel doesn't support 'after' in Schema::table inside closure for all DBs easily if just added, 
                        // but it works for MySQL. We'll just leave it.
                    }
                }
            });

            // If old column exists, migrate data
            if (Schema::hasColumn($tableName, 'folder_id') && !empty($idMapping)) {
                foreach ($idMapping as $oldId => $newId) {
                    DB::table($tableName)->where('folder_id', $oldId)->update(['folder_location_id' => $newId]);
                }
            }

            // Clean up old column and add foreign key
            if (Schema::hasColumn($tableName, 'folder_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    try {
                        // Attempt to drop with known names
                        $table->dropForeign($tableName . '_folder_id_foreign');
                    } catch (\Exception $e) {
                         try {
                            $table->dropForeign(['folder_id']);
                         } catch (\Exception $e2) {}
                    }
                    try {
                        $table->dropColumn('folder_id');
                    } catch (\Exception $e) {}
                });
            }
            
            // Check if foreign key exists using raw SQL
            $fkExists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ? 
                AND TABLE_CATALOG = current_database()
            ", [$tableName, $tableName . '_folder_location_id_foreign']);

            if (empty($fkExists)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('folder_location_id')->references('id')->on('folder_locations')->nullOnDelete();
                });
            }
        }

        // 4. Drop legacy tables
        Schema::dropIfExists('folders');
        Schema::dropIfExists('columns');
        Schema::dropIfExists('rows');
        Schema::dropIfExists('racks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversion is complex and not requested for immediate rollback safety
    }
};
