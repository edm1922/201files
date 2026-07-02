<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $hr = Department::where('code', 'HR')->first();
        $fin = Department::where('code', 'FIN')->first();
        $acct = Department::where('code', 'ACCT')->first();
        $cda = Department::where('code', 'CDA')->first();
        $bh = Department::where('code', 'BH')->first();
        $legal = Department::where('code', 'LEGAL')->first();

        $documentTypes = [
            ['department_id' => $hr?->id, 'name' => 'Employment Contract', 'code' => 'EMP-CONTRACT', 'has_expiry' => true],
            ['department_id' => $hr?->id, 'name' => 'Government ID', 'code' => 'GOV-ID', 'has_expiry' => true],
            ['department_id' => $hr?->id, 'name' => 'Clearance Form', 'code' => 'CLEARANCE', 'has_expiry' => false],
            ['department_id' => $hr?->id, 'name' => 'Training Certificate', 'code' => 'TRAINING-CERT', 'has_expiry' => true],
            ['department_id' => $fin?->id, 'name' => 'Business Permit', 'code' => 'BIZPERMIT', 'has_expiry' => true],
            ['department_id' => $fin?->id, 'name' => 'Tax Return', 'code' => 'TAX-RETURN', 'has_expiry' => true],
            ['department_id' => $fin?->id, 'name' => 'Financial Statement', 'code' => 'FIN-STATEMENT', 'has_expiry' => false],
            ['department_id' => $fin?->id, 'name' => 'Insurance Policy', 'code' => 'INSURANCE', 'has_expiry' => true],
            ['department_id' => $acct?->id, 'name' => 'Invoice', 'code' => 'INVOICE', 'has_expiry' => false],
            ['department_id' => $acct?->id, 'name' => 'Purchase Order', 'code' => 'PO', 'has_expiry' => false],
            ['department_id' => $acct?->id, 'name' => 'Official Receipt', 'code' => 'OR', 'has_expiry' => false],
            ['department_id' => $cda?->id, 'name' => 'CDA Report', 'code' => 'CDA-REPORT', 'has_expiry' => true],
            ['department_id' => $cda?->id, 'name' => 'Compliance Document', 'code' => 'COMPLIANCE', 'has_expiry' => true],
            ['department_id' => $bh?->id, 'name' => 'Sales Report', 'code' => 'SALES-REPORT', 'has_expiry' => false],
            ['department_id' => $bh?->id, 'name' => 'Inventory Report', 'code' => 'INV-REPORT', 'has_expiry' => false],
            ['department_id' => $bh?->id, 'name' => 'Delivery Receipt', 'code' => 'DR', 'has_expiry' => false],
            ['department_id' => $legal?->id, 'name' => 'Contract', 'code' => 'CONTRACT', 'has_expiry' => true],
            ['department_id' => $legal?->id, 'name' => 'Memorandum of Agreement', 'code' => 'MOA', 'has_expiry' => true],
            ['department_id' => $legal?->id, 'name' => 'NDA', 'code' => 'NDA', 'has_expiry' => true],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::create($type);
        }
    }
}
