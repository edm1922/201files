<x-app-layout>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <h5 class="text-uppercase text-muted mb-4" style="letter-spacing: 0.05em; font-weight: 600;">User Profile Management</h5>

                <div class="file-panel p-4 mb-4">
                    @php
                        $user = Auth::user();
                        $initials = collect(explode(' ', $user->name))
                            ->map(fn($n) => mb_substr($n, 0, 1))
                            ->take(2)
                            ->join('');
                    @endphp

                    <div class="profile-header border-bottom-0 mb-0 pb-0">
                        <div class="profile-avatar" style="width: 80px; height: 80px; font-size: 2.2rem; background: #e5e7eb; color: #374151;">
                            {{ strtoupper($initials) }}
                        </div>
                        <div class="profile-info">
                            <h1 class="text-uppercase fw-bold mb-1" style="font-size: 2.2rem; letter-spacing: -0.01em;">{{ $user->name }}</h1>
                            <div class="profile-meta">
                                <span>{{ $user->email }}</span>
                                <span class="dot"></span>
                                <span>Role: <span class="text-uppercase fw-bold">{{ $user->role ?? 'Admin' }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-4">
                    {{-- PROFILE INFORMATION --}}
                    <div class="profile-card shadow-sm border-0">
                        <div class="profile-card__body p-4">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    {{-- PASSWORD UPDATE --}}
                    <div class="profile-card shadow-sm border-0">
                        <div class="profile-card__body p-4">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
