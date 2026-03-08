<div class="space-y-6">
    {{-- Push Notification Toggle --}}
    <div x-data="pushNotificationManager()" class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Push Notifications</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Receive notifications even when the app is closed.
                </p>
            </div>
            <template x-if="supported">
                <button @click="toggle()" :disabled="loading"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                    :class="subscribed ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-600'"
                    role="switch" :aria-checked="subscribed.toString()">
                    <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="subscribed ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </template>
            <template x-if="!supported">
                <span class="text-sm text-neutral-400">Not supported</span>
            </template>
        </div>
        <p x-show="message" x-text="message" class="text-sm text-blue-600 dark:text-blue-400" x-cloak></p>
    </div>

    <hr class="border-neutral-200 dark:border-neutral-700">

    {{-- Notification Types --}}
    <div class="space-y-4">
        <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Notification Types</h3>

        {{-- Race Reminders --}}
        <div class="flex items-center justify-between">
            <div>
                <label for="race-reminders" class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Race Reminders</label>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Get reminded before scheduled races.</p>
            </div>
            <button wire:click="$toggle('raceReminders')" id="race-reminders"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                :class="@js($raceReminders) ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-600'"
                role="switch" aria-checked="{{ $raceReminders ? 'true' : 'false' }}">
                <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="@js($raceReminders) ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>

        {{-- Training Alerts --}}
        <div class="flex items-center justify-between">
            <div>
                <label for="training-alerts" class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Training Alerts</label>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Alerts for optimal training opportunities.</p>
            </div>
            <button wire:click="$toggle('trainingAlerts')" id="training-alerts"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                :class="@js($trainingAlerts) ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-600'"
                role="switch" aria-checked="{{ $trainingAlerts ? 'true' : 'false' }}">
                <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="@js($trainingAlerts) ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>

        {{-- Sync Notifications --}}
        <div class="flex items-center justify-between">
            <div>
                <label for="sync-notifications" class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Sync Notifications</label>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Get notified when offline changes sync.</p>
            </div>
            <button wire:click="$toggle('syncNotifications')" id="sync-notifications"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                :class="@js($syncNotifications) ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-600'"
                role="switch" aria-checked="{{ $syncNotifications ? 'true' : 'false' }}">
                <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="@js($syncNotifications) ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>
    </div>

    <hr class="border-neutral-200 dark:border-neutral-700">

    {{-- Quiet Hours --}}
    <div class="space-y-4">
        <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Quiet Hours</h3>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">
            No notifications will be sent during these hours.
        </p>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="quiet-start" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Start</label>
                <input wire:model="quietHoursStart" type="time" id="quiet-start"
                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
            </div>
            <div>
                <label for="quiet-end" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">End</label>
                <input wire:model="quietHoursEnd" type="time" id="quiet-end"
                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
            </div>
        </div>
    </div>

    <hr class="border-neutral-200 dark:border-neutral-700">

    {{-- Actions --}}
    <div class="flex items-center justify-between">
        <button wire:click="resetDefaults" type="button"
            class="text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 underline">
            Reset to Defaults
        </button>

        <div class="flex items-center gap-3">
            @if ($saved)
                <span class="text-sm text-green-600 dark:text-green-400" wire:transition>Saved!</span>
            @endif

            <button wire:click="savePreferences" type="button"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                <span wire:loading.remove wire:target="savePreferences">Save Preferences</span>
                <span wire:loading wire:target="savePreferences">Saving...</span>
            </button>
        </div>
    </div>
</div>
