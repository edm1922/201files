<?php

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use App\Notifications\DocumentExpiryAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config()->set('scout.driver', 'null');
    Document::disableSearchSyncing();
});

it('sends configured expiry reminders and avoids duplicate sends', function () {
    config()->set('document_expiry.reminder_days', [7]);
    config()->set('document_expiry.send_expired_alert', false);

    $department = Department::create([
        'name' => 'Reminder Department',
        'code' => 'RMD',
        'description' => 'Reminder Department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Expiry Type',
        'code' => 'EXP-TYPE',
        'has_expiry' => true,
    ]);

    $admin = User::factory()->admin()->create();
    $deptUser = User::factory()->encoder()->create();
    $uploader = User::factory()->encoder()->create();

    $deptUser->authorizedDepartments()->sync([$department->id]);

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'uploaded_by' => $uploader->id,
        'file_path' => 'documents/departments/'.$department->id.'/permit.pdf',
        'original_filename' => 'permit.pdf',
        'system_filename' => 'DEPT-'.$department->id.'_EXP-TYPE_20260405120000.pdf',
        'page_count' => 1,
        'file_size_bytes' => 100,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'expiry_date' => now()->addDays(7)->toDateString(),
    ]);

    Notification::fake();

    Artisan::call('documents:send-expiry-reminders');

    Notification::assertSentTo($admin, DocumentExpiryAlertNotification::class, function ($notification) use ($document) {
        return $notification->document->is($document) && $notification->daysBeforeExpiry === 7;
    });

    Notification::assertSentTo($deptUser, DocumentExpiryAlertNotification::class, function ($notification) use ($document) {
        return $notification->document->is($document) && $notification->daysBeforeExpiry === 7;
    });

    expect(DB::table('document_expiry_notifications')->count())->toBe(2);

    Artisan::call('documents:send-expiry-reminders');

    expect(DB::table('document_expiry_notifications')->count())->toBe(2);
});

it('catches up reminders missed while server was offline', function () {
    Carbon::setTestNow('2026-04-07 10:00:00');

    config()->set('document_expiry.reminder_days', [30, 14, 7, 1]);
    config()->set('document_expiry.send_expired_alert', false);

    $department = Department::create([
        'name' => 'Catch-up Department',
        'code' => 'CUD',
        'description' => 'Catch-up Department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Catch-up Type',
        'code' => 'CATCHUP',
        'has_expiry' => true,
    ]);

    $admin = User::factory()->admin()->create();
    $uploader = User::factory()->encoder()->create();

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'uploaded_by' => $uploader->id,
        'file_path' => 'documents/departments/'.$department->id.'/catchup.pdf',
        'original_filename' => 'catchup.pdf',
        'system_filename' => 'DEPT-'.$department->id.'_CATCHUP_20260407100000.pdf',
        'page_count' => 1,
        'file_size_bytes' => 100,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'expiry_date' => now()->addDays(6)->toDateString(),
    ]);

    Notification::fake();

    Artisan::call('documents:send-expiry-reminders');

    Notification::assertSentTo($admin, DocumentExpiryAlertNotification::class, function ($notification) use ($document) {
        return $notification->document->is($document) && $notification->daysBeforeExpiry === 7;
    });

    expect(DB::table('document_expiry_notifications')->where('days_before_expiry', 7)->count())->toBe(1);

    Carbon::setTestNow();
});

it('sends expired alerts once for already expired active documents', function () {
    config()->set('document_expiry.reminder_days', []);
    config()->set('document_expiry.send_expired_alert', true);

    $department = Department::create([
        'name' => 'Expired Alert Department',
        'code' => 'EAD',
        'description' => 'Expired Alert Department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Expired Type',
        'code' => 'EXP-ALERT',
        'has_expiry' => true,
    ]);

    $admin = User::factory()->admin()->create();
    $uploader = User::factory()->encoder()->create();

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'uploaded_by' => $uploader->id,
        'file_path' => 'documents/departments/'.$department->id.'/clearance.pdf',
        'original_filename' => 'clearance.pdf',
        'system_filename' => 'DEPT-'.$department->id.'_EXP-ALERT_20260405121000.pdf',
        'page_count' => 1,
        'file_size_bytes' => 120,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->subMonths(2)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
    ]);

    Notification::fake();

    Artisan::call('documents:send-expiry-reminders');

    Notification::assertSentTo($admin, DocumentExpiryAlertNotification::class, function ($notification) use ($document) {
        return $notification->document->is($document) && $notification->daysBeforeExpiry === null;
    });

    expect(DB::table('document_expiry_notifications')->count())->toBe(1);

    Artisan::call('documents:send-expiry-reminders');

    expect(DB::table('document_expiry_notifications')->count())->toBe(1);
});
