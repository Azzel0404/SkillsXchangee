<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username or Email -->
        <div class="form-group">
            <label for="login" class="form-label">Username or Email</label>
            <input id="login" class="form-input" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="checkbox-group">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">{{ __('Remember me') }}</label>
        </div>

        <div class="form-footer">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="btn-primary">
                {{ __('LOG IN') }}
            </button>
        </div>
    </form>
</x-guest-layout>
