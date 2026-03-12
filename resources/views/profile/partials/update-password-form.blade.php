<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">
            {{ __('Update Password') }}
        </h2>
        <p class="text-sm text-muted mb-0">
            {{ __('Update your password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div class="row g-3">
            <div class="col-md-4">
                <x-input-label for="update_password_current_password" :value="__('Current Password')" class="fw-bold text-sm mb-1" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="form-control field-input" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div class="col-md-4">
                <x-input-label for="update_password_password" :value="__('New Password')" class="fw-bold text-sm mb-1" />
                <x-text-input id="update_password_password" name="password" type="password" class="form-control field-input" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div class="col-md-4">
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="fw-bold text-sm mb-1" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control field-input" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-brand px-4 py-2 rounded-2 shadow-sm">
                {{ __('Update Password') }}
            </button>
        </div>
    </form>
</section>
