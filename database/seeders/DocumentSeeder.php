<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $encoder = User::where('role', 'encoder')->first();

        if (!$admin) {
            return;
        }

        $uploaderIds = [$admin->id];
        if ($encoder) {
            $uploaderIds[] = $encoder->id;
        }

        $departments = Department::all();
        $now = now();
        $documents = [];

        foreach ($departments as $dept) {
            $types = DocumentType::where('department_id', $dept->id)->get();

            foreach ($types as $type) {
                for ($i = 1; $i <= 3; $i++) {
                    $filename = strtolower(str_replace(' ', '_', $type->name)) . "_$i.pdf";
                    $systemFilename = uniqid('doc_', true) . '.pdf';

                    $documents[] = [
                        'department_id' => $dept->id,
                        'document_type_id' => $type->id,
                        'uploaded_by' => $uploaderIds[array_rand($uploaderIds)],
                        'file_path' => 'documents/' . $dept->code . '/' . $systemFilename,
                        'original_filename' => $filename,
                        'system_filename' => $systemFilename,
                        'page_count' => rand(1, 15),
                        'file_size_bytes' => rand(100000, 5000000),
                        'mime_type' => 'application/pdf',
                        'upload_mode' => 'standard',
                        'status' => 'active',
                        'date_received' => $now->copy()->subDays(rand(1, 365))->format('Y-m-d'),
                        'expiry_date' => $type->has_expiry ? $now->copy()->addMonths(rand(1, 24))->format('Y-m-d') : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($documents, 50) as $chunk) {
            DB::table('documents')->insert($chunk);
        }
    }
}
