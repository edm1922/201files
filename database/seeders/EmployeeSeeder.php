<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing employees before seeding to avoid duplicates
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $csvFile = base_path('temporary file/ALL_EMPLOYEE 1-2151.csv');
        if (! file_exists($csvFile)) {
            return;
        }

        $handle = fopen($csvFile, 'r');
        fgetcsv($handle); // Skip header

        // Starting folder ID
        $firstFolder = DB::table('folders')->orderBy('id')->first();
        $nextFolderId = $firstFolder ? $firstFolder->id : 1;

        $now = '2026-03-31 00:00:00';
        $seenBarcodes = [];
        $seenSystemIds = [];
        $idCounter = 1;

        while (($data = fgetcsv($handle)) !== false) {
            // Basic validation
            if (empty($data[0]) && empty($data[4])) {
                continue;
            }

            $barcode = trim($data[0] ?? '');
            $systemId = trim($data[1] ?? '');

            if ($barcode && in_array($barcode, $seenBarcodes)) {
                continue;
            }
            if ($systemId && in_array($systemId, $seenSystemIds)) {
                continue;
            }

            if (empty($systemId)) {
                $systemId = 'TEMP-'.uniqid();
            }

            $hiredDateRaw = trim($data[3] ?? '');
            $fullName = trim($data[4] ?? '');
            $statusRaw = trim($data[5] ?? 'active');

            // Parse Name
            $firstName = $middleName = $lastName = '';
            if ($fullName) {
                if (strpos($fullName, ',') !== false) {
                    $parts = explode(',', $fullName);
                    $lastName = trim($parts[0]);
                    if (isset($parts[1])) {
                        $rem = trim($parts[1]);
                        $nameParts = explode(' ', $rem);
                        if (count($nameParts) > 1) {
                            $middleName = array_pop($nameParts);
                            $firstName = implode(' ', $nameParts);
                        } else {
                            $firstName = $nameParts[0];
                        }
                    }
                } else {
                    $nameParts = explode(' ', $fullName);
                    $lastName = array_pop($nameParts);
                    $firstName = implode(' ', $nameParts);
                }
            }

            // Parse Date
            $hiredDate = null;
            if ($hiredDateRaw) {
                $time = strtotime($hiredDateRaw);
                if ($time) {
                    $hiredDate = date('Y-m-d', $time);
                }
            }

            $folderLocationId = (int) ceil($idCounter / 500);

            DB::table('employees')->insert([
                'system_id' => $systemId,
                'barcode_id' => $barcode,
                'first_name' => mb_convert_case((string) $firstName, MB_CASE_UPPER, 'UTF-8'),
                'middle_name' => mb_convert_case((string) $middleName, MB_CASE_UPPER, 'UTF-8'),
                'last_name' => mb_convert_case((string) $lastName, MB_CASE_UPPER, 'UTF-8'),
                'date_hired' => $hiredDate,
                'status' => in_array(strtolower($statusRaw), ['active', 'awol', 'resigned']) ? strtolower($statusRaw) : 'active',
                // 'atm_status' => 'not_applicable',  // UNCOMMENT IF YOU WANT TO ADD ATM STATUS
                // 'bank_type_id' => 1,  // UNCOMMENT IF YOU WANT TO ADD BANK TYPE
                'company_id' => 1,
                'folder_id' => $nextFolderId,
                'folder_location_id' => $folderLocationId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Mark folder occupied and sync company-sequence metadata for the new model
            $folder = DB::table('folders')->where('id', $nextFolderId)->first();
            $sequenceNumber = null;

            if ($folder && preg_match('/(\d+)$/', (string) $folder->folder_code, $matches) === 1) {
                $sequenceNumber = (int) $matches[1];
            }

            DB::table('folders')->where('id', $nextFolderId)->update([
                'is_available' => 0,
                'company_id' => 1,
                'sequence_number' => $sequenceNumber,
            ]);

            if ($barcode) {
                $seenBarcodes[] = $barcode;
            }
            if ($systemId) {
                $seenSystemIds[] = $systemId;
            }

            $idCounter++;
            $nextFolderId++;
        }

        fclose($handle);
    }
}
