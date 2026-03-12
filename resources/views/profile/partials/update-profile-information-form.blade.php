<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">
            {{ __('Profile Information') }}
        </h2>
        <p class="text-sm text-muted mb-0">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="row g-3">
            <div class="col-md-6">
                <x-input-label for="name" :value="__('Name')" class="fw-bold text-sm mb-1" />
                <x-text-input id="name" name="name" type="text" class="form-control field-input" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="col-md-6">
                <x-input-label for="email" :value="__('Email')" class="fw-bold text-sm mb-1" />
                <x-text-input id="email" name="email" type="email" class="form-control field-input" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-sm text-gray-800">
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification" class="btn btn-link p-0 text-sm align-baseline">{{ __('Click here to re-send the verification email.') }}</button>
                        </p>
                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-brand px-4 py-2 rounded-2 shadow-sm">
                {{ __('Save Changes') }}
            </button>
        </div>
    </form>
</section>
