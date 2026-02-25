<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Change Password</h2>

        <form action="{{ route('profile.password.change') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Current Password
                </label>
                <input type="password" name="current_password" id="current_password" required autocomplete="current-password"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    New Password
                </label>
                <input type="password" name="password" id="password" required autocomplete="new-password"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Confirm New Password
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
            </div>

            <!-- Update Password Button -->
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Active Sessions -->
<div class="mt-6 bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Active Sessions</h2>

        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="shrink-0">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Current Session</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ request()->userAgent() }} • Last active: Now
                        </p>
                    </div>
                </div>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                    Active
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Danger Zone -->
<div class="mt-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-lg"
    x-data="deleteAccountModal()" data-expected-name="{{ $user?->name ?? 'your username' }}">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-red-900 dark:text-red-200 mb-2">Danger Zone</h2>
        <p class="text-sm text-red-700 dark:text-red-300 mb-4">
            Once you delete your account, there is no going back. Please be certain.
        </p>

        <button type="button" @click="openModal()"
            class="btn bg-red-600 hover:bg-red-700 text-white border-red-600 hover:border-red-700">
            Delete Account
        </button>
    </div>

    <!-- Delete Account Modal -->
    <div x-show="show" x-cloak @click.away="closeModal()" @keydown.escape.window="closeModal()"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Account</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone</p>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    This will permanently delete your account and all associated data including:
                </p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside space-y-1 mb-4">
                    <li>All your characters and training data</li>
                    <li>Career runs and race history</li>
                    <li>Skills and support card configurations</li>
                    <li>Account settings and preferences</li>
                </ul>
                <p class="text-sm font-medium text-red-600 dark:text-red-400">
                    This action is irreversible and cannot be undone.
                </p>
            </div>

            <form action="{{ route('profile.destroy') }}" method="POST" class="space-y-4" @submit="handleSubmit">
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_password"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Confirm your password
                    </label>
                    <input type="password" name="password" id="delete_password" required x-model="password" autocomplete="current-password"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>

                <div>
                    <label for="delete_confirmation"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Type <strong class="text-red-600 dark:text-red-400">{{ $user?->name ?? 'your username' }}</strong> to confirm
                    </label>
                    <input type="text" name="confirmation" id="delete_confirmation" required x-model="confirmation"
                        placeholder="{{ $user?->name ?? 'your username' }}"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': confirmationError }">
                    <p x-show="confirmationError" x-cloak class="mt-1 text-sm text-red-600 dark:text-red-400">
                        Account name does not match. Please type <strong>{{ $user?->name ?? 'your username' }}</strong> exactly.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="closeModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" :disabled="!canSubmit"
                        :class="{ 'opacity-50 cursor-not-allowed': !canSubmit }"
                        class="btn bg-red-600 hover:bg-red-700 text-white border-red-600 hover:border-red-700">
                        Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
    @vite(['resources/js/pages/profile/partials/security-tab.js'])
@endonce
