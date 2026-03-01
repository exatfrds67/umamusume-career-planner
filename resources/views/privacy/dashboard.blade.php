<div>
    {{-- Status Message --}}
    @if($statusMessage)
        <div class="mb-6 p-4 rounded-lg {{ $statusType === 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200' : ($statusType === 'error' ? 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200' : 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200') }}">
            <p class="text-sm font-medium">{{ $statusMessage }}</p>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Privacy & Data Management</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Manage your data, consent preferences, and account deletion.
        </p>
    </div>

    <div class="space-y-8">
        {{-- Section: Consent Management --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Sharing & Consent</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Control how your data is used. You can grant or revoke consent at any time.
            </p>

            <div class="space-y-4">
                @foreach($consentTypes as $type)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg" wire:key="consent-{{ $type->value }}">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $type->label() }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $type->description() }}</p>
                        </div>
                        <button
                            wire:click="toggleConsent('{{ $type->value }}')"
                            type="button"
                            role="switch"
                            aria-checked="{{ ($consents[$type->value] ?? false) ? 'true' : 'false' }}"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 {{ ($consents[$type->value] ?? false) ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                        >
                            <span class="sr-only">Toggle {{ $type->label() }}</span>
                            <span
                                aria-hidden="true"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($consents[$type->value] ?? false) ? 'translate-x-5' : 'translate-x-0' }}"
                            ></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Data Export --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Your Data</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Download a complete copy of all your personal data in JSON format. This includes your profile, characters, careers, consent history, and preferences.
            </p>

            @if($exportReady)
                <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm text-green-800 dark:text-green-200">Your export is ready!</span>
                </div>
                <div class="flex gap-3">
                    <button wire:click="downloadExport" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Download Export
                    </button>
                    <button wire:click="exportData" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500">
                        Generate New Export
                    </button>
                </div>
            @else
                <button
                    wire:click="exportData"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="exportData">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="exportData">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    Export All Personal Data
                </button>
            @endif
        </div>

        {{-- Section: Account Deletion --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Deletion</h2>

            @if($activeDeletion)
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mb-4">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">Deletion Scheduled</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                Your account will be permanently deleted on
                                <strong>{{ $activeDeletion->grace_period_ends_at->format('F j, Y') }}</strong>.
                                All data will be irreversibly removed.
                            </p>
                            <button
                                wire:click="cancelDeletion"
                                class="mt-3 inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 text-red-700 dark:text-red-300 text-sm font-medium rounded border border-red-300 dark:border-red-600 hover:bg-red-50 dark:hover:bg-gray-600"
                            >
                                Cancel Deletion Request
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Requesting account deletion will start a 30-day grace period. During this time, you can cancel the request. After the grace period, all your data will be permanently and irreversibly deleted.
                </p>

                @if($showDeletionConfirm)
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-3">Are you sure?</h3>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                            This will schedule your account for permanent deletion. All characters, careers, training data, and personal information will be removed.
                        </p>
                        <div class="mb-4">
                            <label for="deletion-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Reason (optional)
                            </label>
                            <textarea
                                id="deletion-reason"
                                wire:model="deletionReason"
                                rows="2"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm p-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Tell us why you're leaving (optional)..."
                            ></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button
                                wire:click="requestDeletion"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-hidden focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                Yes, Delete My Account
                            </button>
                            <button
                                wire:click="cancelDeletionConfirm"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                @else
                    <button
                        wire:click="confirmDeletion"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-hidden focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Request Account Deletion
                    </button>
                @endif
            @endif

            {{-- Deletion History --}}
            @if($deletionHistory->isNotEmpty())
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Deletion Request History</h3>
                    <div class="space-y-2">
                        @foreach($deletionHistory as $request)
                            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400" wire:key="deletion-{{ $request->id }}">
                                <span>{{ $request->created_at->format('M j, Y') }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $request->status === \App\Enums\DeletionStatus::Cancelled ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' }}">
                                    {{ $request->status->label() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Section: Privacy Policy Link --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Privacy Policy</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Review our full privacy policy to understand how your data is collected, stored, and used.
            </p>
            <a href="{{ route('privacy.policy') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                Read Privacy Policy &rarr;
            </a>
        </div>
    </div>
</div>
