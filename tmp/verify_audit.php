<?php

use App\Models\Employee;
use App\Services\AuditService;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Login a user for testing
\Illuminate\Support\Facades\Auth::loginUsingId(1);

// 1. Create a dummy employee
$employee = Employee::create([
    'system_id' => 'TEST-' . uniqid(),
    'first_name' => 'John',
    'last_name' => 'Doe',
    'status' => 'active',
    'company_id' => 1
]);

$empName = $employee->full_name;
echo "Created Employee: $empName\n";

// 2. Log update
AuditService::log('updated', 'Updated test employee', $employee);
echo "Logged 'updated' action.\n";

// 3. Log deletion
AuditService::log('deleted', "Permanently deleted employee (System ID: {$employee->system_id})", $employee);
echo "Logged 'deleted' action.\n";

// 4. Force Delete (Permanent)
$employee->forceDelete();
echo "Permanently deleted employee from database.\n";

// 5. Verify Logs
$logs = AuditLog::where('model_id', $employee->id)
    ->where('model_type', Employee::class)
    ->get();

echo "Verification Results:\n";
foreach ($logs as $log) {
    echo "- Action: {$log->action}\n";
    echo "  Target Name: {$log->target_name}\n";
    echo "  Resolved Name (Accessor): {$log->target_name}\n";
}

if ($logs->count() === 2 && $logs->every(fn($l) => $l->target_name === $empName)) {
    echo "\nSUCCESS: Audit logs persisted the target name after deletion.\n";
} else {
    echo "\nFAILURE: Audit logs missing target name.\n";
}
