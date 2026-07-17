<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentFolder;
use Illuminate\Database\Seeder;

class DocumentFolderSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();

        foreach ($departments as $dept) {
            $prefix = config('brand.folder_prefix') . '-' . $dept->code;

            $folders = [
                ['name' => 'General', 'parent_id' => null],
                ['name' => 'Incoming', 'parent_id' => null],
                ['name' => 'Outgoing', 'parent_id' => null],
                ['name' => 'Archived', 'parent_id' => null],
            ];

            foreach ($folders as $i => $folder) {
                DocumentFolder::create([
                    'department_id' => $dept->id,
                    'parent_id' => $folder['parent_id'],
                    'name' => $folder['name'],
                    'folder_code' => sprintf('%s-%04d', $prefix, $i + 1),
                ]);
            }
        }
    }
}
