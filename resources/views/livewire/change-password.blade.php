<div class="min-h-screen bg-gray-50">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900">Change Password</h1>
            <a href="{{ route('my-files.index') }}" wire:navigate class="text-sm text-amber-700 hover:text-amber-800 font-medium">
                ← Back to My Files
            </a>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 py-8">
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            @if ($justUpdated)
                <div class="mb-5 flex items-start gap-2.5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    <x-heroicon-o-check-circle class="w-5 h-5 shrink-0 mt-0.5" />
                    <span>Your password has been updated.</span>
                </div>
            @endif

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1.5">Current password</label>
                    <input
                        id="currentPassword"
                        type="password"
                        wire:model="currentPassword"
                        autocomplete="current-password"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition"
                    >
                    @error('currentPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
                    <input
                        id="newPassword"
                        type="password"
                        wire:model="newPassword"
                        autocomplete="new-password"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition"
                    >
                    <p class="text-xs text-gray-400 mt-1">At least 8 characters.</p>
                    @error('newPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="newPasswordConfirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password</label>
                    <input
                        id="newPasswordConfirmation"
                        type="password"
                        wire:model="newPasswordConfirmation"
                        autocomplete="new-password"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition"
                    >
                    @error('newPasswordConfirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold py-2.5 text-base shadow-sm shadow-amber-600/20 transition"
                >
                    Update password
                </button>
            </form>
        </div>
    </main>
</div>
