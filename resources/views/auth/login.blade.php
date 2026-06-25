<x-guest-layout>
    <x-auth-session-status class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg px-3 py-2" :status="session('status')" />

    @if($errors->any())
    <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
        {{ $errors->first() }}
    </div>
    @endif

    @php
        $centralDomains = config('tenancy.central_domains', []);
        $h = request()->getHost();
        $isAdminDomain = false;
        foreach ($centralDomains as $cd) {
            if ($h === $cd || str_ends_with($h, '.' . $cd)) { $isAdminDomain = true; break; }
        }
    @endphp
    @if($isAdminDomain)
    <p class="text-center text-sm font-semibold text-slate-500 mb-5">Admin Login</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-medium text-slate-600 mb-1">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('email') border-red-400 @enderror">
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-slate-600 mb-1">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('password') border-red-400 @enderror">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" id="remember_me" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
                Remember me
            </label>
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-xs text-green-600 hover:underline">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
            Sign In
        </button>
    </form>

    @if(!$isAdminDomain && Route::has('register'))
    <p class="text-center text-xs text-slate-400 mt-5">
        New staff member?
        <a href="{{ route('register') }}" class="text-green-600 font-medium hover:underline">Create account</a>
    </p>
    @endif
</x-guest-layout>
