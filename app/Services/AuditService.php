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
        $model = null,
        ?array $changes = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a document-related action.
     */
    public static function logDocument(string $action, int $documentId, ?string $description = null, ?array $changes = null): AuditLog
    {
        $document = \App\Models\Document::find($documentId);
        return self::log($action, $description, $document, $changes);
    }

    /**
     * Log user login.
     */
    public static function logLogin(): AuditLog
    {
        return self::log('login', 'User logged in', Auth::user());
    }

    /**
     * Log user logout.
     */
    public static function logLogout(): AuditLog
    {
        return self::log('logout', 'User logged out', Auth::user());
    }
}
