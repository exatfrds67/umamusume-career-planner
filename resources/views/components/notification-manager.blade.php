{{-- Phase 6: Notification Manager Display Component --}}
<div
    x-data="notificationManager()"
    @notification-show="addNotification($event.detail)"
    class="fixed top-4 right-4 space-y-2 z-50 max-w-sm"
    role="region"
    aria-live="polite"
    aria-label="Notifications"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            :x-transition:leave-end="`opacity-0 ${notification.isExiting ? 'translate-x-full' : 'translate-y-2'}`"
            :class="getColor(notification.type)"
            class="flex items-start gap-3 p-4 border rounded-lg shadow-lg w-full"
        >
            <!-- Icon -->
            <div :class="getIconColor(notification.type)" class="flex-shrink-0 text-lg font-bold">
                <span x-text="getIcon(notification.type)"></span>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <p :class="getTextColor(notification.type)" class="text-sm font-medium" x-text="notification.message"></p>

                <!-- Action Button -->
                <template x-if="notification.action">
                    <button
                        @click="executeAction(notification)"
                        :class="getTextColor(notification.type)"
                        class="mt-1 text-xs font-medium hover:underline transition"
                        x-text="notification.action.label"
                    ></button>
                </template>
            </div>

            <!-- Close Button -->
            <template x-if="notification.dismissible">
                <button
                    @click="removeNotification(notification.id)"
                    :class="getTextColor(notification.type)"
                    class="flex-shrink-0 hover:opacity-70 transition"
                    aria-label="Dismiss notification"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </template>
        </div>
    </template>
</div>
