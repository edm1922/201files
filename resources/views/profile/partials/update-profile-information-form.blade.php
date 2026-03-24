<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">
            {{ __('Profile Information') }}
        </h2>
        <p class="text-sm text-muted mb-0">
            {{ __("Update your account's profile information and username.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="row g-3">
            <div class="col-md-3">
                <x-input-label for="first_name" :value="__('First Name')" class="fw-bold text-sm mb-1" />
                <x-text-input id="first_name" name="first_name" type="text" class="form-control field-input" :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div class="col-md-3">
                <x-input-label for="middle_name" :value="__('Middle Name')" class="fw-bold text-sm mb-1" />
                <x-text-input id="middle_name" name="middle_name" type="text" class="form-control field-input" :value="old('middle_name', $user->middle_name)" autocomplete="additional-name" />
                <x-input-error class="mt-2" :messages="$errors->get('middle_name')" />
            </div>

            <div class="col-md-3">
                <x-input-label for="last_name" :value="__('Last Name')" class="fw-bold text-sm mb-1" />
                <x-text-input id="last_name" name="last_name" type="text" class="form-control field-input" :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>

            <div class="col-md-3">
                <x-input-label for="suffix" :value="__('Suffix')" class="fw-bold text-sm mb-1" />
                <x-text-input id="suffix" name="suffix" type="text" class="form-control field-input" :value="old('suffix', $user->suffix)" autocomplete="honorific-suffix" />
                <x-input-error class="mt-2" :messages="$errors->get('suffix')" />
            </div>

            <div class="col-md-12">
                <x-input-label for="username" :value="__('Username')" class="fw-bold text-sm mb-1" />
                <x-text-input id="username" name="username" type="text" class="form-control field-input" :value="old('username', $user->username)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('username')" />
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-brand px-4 py-2 rounded-2 shadow-sm">
                {{ __('Save Changes') }}
            </button>
        </div>
    </form>
</section>
