<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — CDRRMO DMS</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-b from-amber-50 via-gray-50 to-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-amber-600 flex items-center justify-center shadow-sm mb-4">
                <x-heroicon-o-shield-check class="w-8 h-8 text-white" />
            </div>
            <h1 class="text-xl font-semibold text-gray-900 text-center">CDRRMO Document Management System</h1>
            <p class="text-sm text-gray-500 mt-1">Sign in to continue</p>
        </div>

        <div class="bg-white shadow-xl shadow-gray-200/60 border border-gray-100 rounded-2xl p-7">
            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2.5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0 mt-0.5" />
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <div class="relative">
                        <x-heroicon-o-envelope class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="you@cdrrmo.gov.ph"
                            class="block w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2.5 text-base placeholder:text-gray-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <x-heroicon-o-lock-closed class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="block w-full rounded-lg border border-gray-300 pl-10 pr-10 py-2.5 text-base focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition"
                        >
                        <button
                            type="button"
                            onclick="const i=document.getElementById('password'); const shown=i.type==='text'; i.type=shown?'password':'text'; document.getElementById('eye-open').classList.toggle('hidden', !shown); document.getElementById('eye-closed').classList.toggle('hidden', shown);"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            aria-label="Show password"
                        >
                            <x-heroicon-o-eye id="eye-open" class="w-5 h-5" />
                            <x-heroicon-o-eye-slash id="eye-closed" class="w-5 h-5 hidden" />
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600 select-none">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500/30">
                    Keep me signed in
                </label>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold py-2.5 text-base shadow-sm shadow-amber-600/20 transition"
                >
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-xs text-gray-400 text-center mt-6">
            Don't have an account? Ask your administrator to create one for you.
        </p>
    </div>
</body>
</html>
