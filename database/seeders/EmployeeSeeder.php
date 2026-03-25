<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $csvFile = base_path('temporary file/EMPLOYEE - 1.csv');
        if (!file_exists($csvFile)) {
            return;
        }

        $handle = fopen($csvFile, 'r');
        fgetcsv($handle); // Skip header

        // Determine the starting folder ID
        $firstFolder = DB::table('folders')->orderBy('id')->first();
        $nextFolderId = $firstFolder ? $firstFolder->id : 1;
        $folderLocationId = 1; // Default to first location
        
        $id = 1;
        $now = '2026-03-16 08:33:00';
        $seenBarcodes = [];
        $seenSystemIds = [];
        
        while (($data = fgetcsv($handle)) !== false) {
            if ((empty($data[0]) || trim($data[0]) == '') && (empty($data[4]) || trim($data[4]) == '')) {
                continue;
            }

            $barcode = trim($data[0] ?? '');
            $systemId = trim($data[1] ?? '');
            
            if ($barcode && in_array($barcode, $seenBarcodes)) continue;
            if ($systemId && in_array($systemId, $seenSystemIds)) continue;

            if (empty($systemId)) {
                $systemId = 'TEMP-' . uniqid();
            }

            $hiredDateRaw = trim($data[3] ?? '');
            $fullName = trim($data[4] ?? '');
            $statusRaw = trim($data[5] ?? '');
            
            $validStatuses = ['active', 'awol', 'resigned'];
            $status = in_array(strtolower($statusRaw), $validStatuses) ? strtolower($statusRaw) : 'active';

            if (!$barcode && !$systemId && !$fullName) continue;

            // Name Parsing: Last name, First name and Middle name
            $firstName = $middleName = $lastName = $suffix = '';
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

            $hiredDate = null;
            if ($hiredDateRaw) {
                if (is_numeric($hiredDateRaw)) {
                    $unixDate = ($hiredDateRaw - 25569) * 86400;
                    $hiredDate = date('Y-m-d', $unixDate);
                } else {
                    $dateObj = strtotime($hiredDateRaw);
                    $hiredDate = $dateObj ? date('Y-m-d', $dateObj) : null;
                }
            }

            DB::table('employees')->insert([
                'id' => $id,
                'system_id' => $systemId,
                'barcode_id' => $barcode,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'suffix' => $suffix,
                'date_hired' => $hiredDate,
                'status' => $status,
                'archive_date' => null,
                'company_id' => 1,
                'folder_id' => $nextFolderId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'folder_location_id' => $folderLocationId,
            ]);

            // Mark the assigned folder as unavailable
            DB::table('folders')->where('id', $nextFolderId)->update(['is_available' => 0]);

            $seenBarcodes[] = $barcode;
            $seenSystemIds[] = $systemId;
            $id++;
            $nextFolderId++; // Move to next folder sequentially
        }
        
        fclose($handle);
    }
}