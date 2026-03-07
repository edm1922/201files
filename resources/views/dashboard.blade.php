<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 fw-bold">Dashboard</h2>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-0">Welcome back, <strong>{{ Auth::user()->name }}</strong>! You're logged in.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
