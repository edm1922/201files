<x-app-layout>
    @php
        $pageUnreadCount = $notifications->getCollection()->filter(fn ($n) => is_null($n->read_at))->count();
        $totalUnreadCount = auth()->user()->unreadNotifications()->count();
    @endphp

    @push('styles')
        <style>
            .notif-hero {
                background: linear-gradient(120deg, #f6f9fc 0%, #eaf2ff 50%, #f9f5ff 100%);
                border: 1px solid #dbe7ff;
                border-radius: 18px;
                padding: 1.25rem;
            }

            .notif-stat {
                border-radius: 12px;
                border: 1px solid #d9e4ff;
                background: #fff;
                padding: 0.75rem 0.9rem;
            }

            .notif-stat-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #5b6b88;
            }

            .notif-stat-value {
                font-size: 1.2rem;
                font-weight: 700;
                color: #16243b;
                line-height: 1;
            }

            .notif-list {
                border-radius: 16px;
                border: 1px solid #dce6f7;
                overflow: hidden;
                background: #fff;
            }

            .notif-item {
                border: 0;
                border-bottom: 1px solid #edf2fa;
                padding: 1rem;
                transition: background-color 0.2s ease;
            }

            .notif-item:hover {
                background: #f9fbff;
            }

            .notif-item:last-child {
                border-bottom: 0;
            }

            .notif-item-unread {
                background: linear-gradient(90deg, #eef5ff 0%, #f8fbff 100%);
            }

            .notif-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                margin-top: 0.45rem;
                flex-shrink: 0;
                background: #9bb0cc;
            }

            .notif-dot-unread {
                background: #2f6fed;
                box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.16);
            }

            .notif-chip {
                font-size: 0.72rem;
                font-weight: 600;
                padding: 0.25rem 0.55rem;
                border-radius: 999px;
                border: 1px solid #dbe3f1;
                color: #435470;
                background: #f8faff;
            }

            .notif-actions .btn {
                min-height: 40px;
                min-width: 40px;
            }

            @media (max-width: 767.98px) {
                .notif-item {
                    padding: 0.9rem;
                }

                .notif-actions {
                    width: 100%;
                }

                .notif-actions .btn,
                .notif-actions form {
                    width: 100%;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .notif-item {
                    transition: none;
                }
            }
        </style>
    @endpush

    <section class="notif-hero mb-4" aria-labelledby="notifications-heading">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            <div>
                <h1 id="notifications-heading" class="h4 mb-1 fw-bold text-dark">Notifications</h1>
                <p class="mb-0 text-muted">Stay updated with document expiry reminders and alerts.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="notif-stat">
                    <div class="notif-stat-label">Unread (All)</div>
                    <div class="notif-stat-value">{{ number_format($totalUnreadCount) }}</div>
                </div>
                <div class="notif-stat">
                    <div class="notif-stat-label">Unread (This Page)</div>
                    <div class="notif-stat-value">{{ number_format($pageUnreadCount) }}</div>
                </div>
                @if ($notifications->count() > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-flex align-items-center">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-check-double me-1" aria-hidden="true"></i>Mark all as read
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success mb-3" role="status" aria-live="polite">{{ session('success') }}</div>
    @endif

    <section class="notif-list" aria-label="Notification list">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $url = $data['url'] ?? route('notifications.index');
                $isExpired = (bool) ($data['is_expired'] ?? false);
            @endphp

            <article class="notif-item {{ $isUnread ? 'notif-item-unread' : '' }}">
                <div class="d-flex gap-3 align-items-start">
                    <div class="notif-dot {{ $isUnread ? 'notif-dot-unread' : '' }}" aria-hidden="true"></div>

                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="notif-chip">{{ $isExpired ? 'Expired' : 'Expiry Reminder' }}</span>
                            @if (!empty($data['department_name']))
                                <span class="notif-chip">{{ $data['department_name'] }}</span>
                            @endif
                            @if ($isUnread)
                                <span class="badge text-bg-primary">New</span>
                            @endif
                        </div>

                        <h2 class="h6 mb-1 fw-semibold text-dark">{{ $data['message'] ?? 'Notification' }}</h2>
                        <div class="small text-muted">
                            <i class="far fa-clock me-1" aria-hidden="true"></i>{{ $notification->created_at?->diffForHumans() }}
                            @if (!empty($data['expiry_date']))
                                <span class="ms-2"><i class="far fa-calendar-alt me-1" aria-hidden="true"></i>Expiry: {{ \Carbon\Carbon::parse($data['expiry_date'])->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="notif-actions d-flex flex-column flex-md-row align-items-stretch gap-2">
                        <a href="{{ $url }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-up-right-from-square me-1" aria-hidden="true"></i>Open
                        </a>
                        @if ($isUnread)
                            <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1" aria-hidden="true"></i>Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="py-5 text-center px-3">
                <div class="mb-2"><i class="far fa-bell-slash fa-2x text-muted" aria-hidden="true"></i></div>
                <h2 class="h6 mb-1 text-dark">No notifications yet</h2>
                <p class="text-muted mb-0">You will see expiry reminders and document alerts here.</p>
            </div>
        @endforelse
    </section>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</x-app-layout>
