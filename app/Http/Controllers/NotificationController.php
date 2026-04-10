<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();

            AuditService::log('updated', 'Marked notification as read.', null, [
                'notification_id' => $notification->id,
                'notification_type' => $notification->type,
                'read_at' => optional($notification->read_at)?->toDateTimeString(),
            ]);
        }

        // If explicitly requested to stay on the same page (e.g., from the notifications list)
        if ($request->get('redirect') === 'back') {
            return back()->with('success', 'Notification marked as read.');
        }

        $data = $notification->data;
        if (isset($data['url_route'])) {
            return redirect()->route($data['url_route'], $data['url_params'] ?? []);
        }

        $targetUrl = data_get($data, 'url', route('notifications.index'));

        // Smart repair for old absolute URLs missing the project subdirectory
        if (str_contains((string)$targetUrl, 'department-documents')) {
            $query = parse_url($targetUrl, PHP_URL_QUERY);
            return redirect()->to(route('department-documents.index') . ($query ? '?' . $query : ''));
        }

        return redirect()->to($targetUrl);
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $unreadNotifications = $request->user()->unreadNotifications;
        $affectedCount = $unreadNotifications->count();

        if ($affectedCount > 0) {
            $unreadNotifications->markAsRead();

            AuditService::log('updated', 'Marked all unread notifications as read.', null, [
                'affected_count' => $affectedCount,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
