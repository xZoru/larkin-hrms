<x-guest-layout>
    <div class="mb-8">
        <p class="mb-2 text-sm font-semibold uppercase tracking-widest text-slate-900" style="color:#111827;">Welcome back</p>
        <h2 class="text-3xl font-semibold tracking-tight text-slate-900" style="color:#111827;">Sign in to your account</h2>
        <p class="mt-2 text-sm text-slate-500">Enter your work email and password to continue.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Work email')" class="!text-slate-700 !font-medium" />
            <x-text-input id="email" class="block mt-2 w-full !rounded-lg !border-slate-300 !py-3 !shadow-none focus:!border-sky-600 focus:!ring-sky-600" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="!text-slate-700 !font-medium" />
</div>
            <x-text-input id="password" class="block mt-2 w-full !rounded-lg !border-slate-300 !py-3 !shadow-none focus:!border-sky-600 focus:!ring-sky-600" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" value="1" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-sky-800 focus:ring-2 focus:ring-sky-700 focus:ring-offset-1">
            <label for="remember_me" class="ms-2 cursor-pointer select-none text-sm text-slate-600">{{ __('Remember me') }}</label>
        </div>

        <button type="submit" style="display:block !important; visibility:visible !important; width:100%; min-height:48px; padding:12px 16px; border:0; border-radius:8px; background-color:#075985; color:#fff; font:600 14px Figtree, sans-serif; text-align:center; cursor:pointer; box-shadow:0 1px 2px rgba(15,23,42,.12);" class="login-submit">
            {{ __('Sign in') }}
        </button>
    </form>
</x-guest-layout>
