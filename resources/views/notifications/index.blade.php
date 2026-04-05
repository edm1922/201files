<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold">Notifications</h2>
            <small class="text-muted">Document expiry reminders and alerts</small>
        </div>
        @if ($notifications->count() > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Mark all as read</button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $url = $data['url'] ?? route('notifications.index');
                @endphp
                <div class="list-group-item py-3 {{ $isUnread ? 'bg-light' : '' }}">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">{{ $data['message'] ?? 'Notification' }}</div>
                            <div class="text-muted small mt-1">
                                {{ $notification->created_at?->diffForHumans() }}
                                @if (!empty($data['department_name']))
                                    • {{ $data['department_name'] }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <a href="{{ $url }}" class="btn btn-sm btn-outline-primary">Open</a>
                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Mark as read</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="list-group-item py-4 text-center text-muted">No notifications yet.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</x-app-layout>
