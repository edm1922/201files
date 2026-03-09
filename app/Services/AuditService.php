<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action to the audit trail.
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?int $documentId = null,
        ?array $changes = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'document_id' => $documentId,
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'changes' => $changes,
        ]);
    }

    /**
     * Log a document-related action.
     */
    public static function logDocument(string $action, int $documentId, ?string $description = null, ?array $changes = null): AuditLog
    {
        return self::log($action, $description, $documentId, $changes);
    }

    /**
     * Log user login.
     */
    public static function logLogin(): AuditLog
    {
        return self::log('login', 'User logged in');
    }

    /**
     * Log user logout.
     */
    public static function logLogout(): AuditLog
    {
        return self::log('logout', 'User logged out');
    }
}
