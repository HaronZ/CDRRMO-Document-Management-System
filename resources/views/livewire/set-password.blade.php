<div class="min-h-screen bg-gradient-to-b from-amber-50 via-gray-50 to-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-amber-600 flex items-center justify-center shadow-sm mb-4">
                <x-heroicon-o-key class="w-8 h-8 text-white" />
            </div>
            <h1 class="text-xl font-semibold text-gray-900 text-center">Set your password</h1>
            <p class="text-sm text-gray-500 mt-1 text-center">Welcome to the CDRRMO Document Management System</p>
        </div>

        <div class="bg-white shadow-xl shadow-gray-200/60 border border-gray-100 rounded-2xl p-7">
            @if ($invalid)
                <div class="flex items-start gap-2.5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0 mt-0.5" />
                    <span>This link has expired or was already used. Please ask your administrator to resend your welcome email.</span>
                </div>
            @else
                <form wire:submit="setPassword" class="space-y-5">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            autofocus
                            autocomplete="new-password"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition"
                        >
                        <p class="text-xs text-gray-400 mt-1">At least 8 characters.</p>
                        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="passwordConfirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                        <input
                            id="passwordConfirmation"
                            type="password"
                            wire:model="passwordConfirmation"
                            autocomplete="new-password"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition"
                        >
                        @error('passwordConfirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold py-2.5 text-base shadow-sm shadow-amber-600/20 transition"
                    >
                        Set password and log in
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
