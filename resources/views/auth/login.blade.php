<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — CDRRMO DMS</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <h1 class="text-2xl font-semibold text-gray-900 text-center mb-1">CDRRMO Document Management System</h1>
        <p class="text-sm text-gray-500 text-center mb-8">Sign in to continue</p>

        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base focus:border-amber-500 focus:ring-amber-500"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base focus:border-amber-500 focus:ring-amber-500"
                    >
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300">
                    Keep me signed in
                </label>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-medium py-2.5 text-base transition"
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
