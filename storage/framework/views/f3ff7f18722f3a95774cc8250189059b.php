<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Notification Settings</h2>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            Choose what notifications you want to receive
        </p>

        <div class="space-y-4">
            <!-- Email Notifications -->
            <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Email Notifications</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Receive email updates about your account</p>
                </div>
                <div>
                    <input type="hidden" name="preferences[notifications][email]" value="0">
                    <input type="checkbox" name="preferences[notifications][email]" value="1"
                        <?php echo e(old('preferences.notifications.email', $user->preferences['notifications']['email'] ?? true) ? 'checked' : ''); ?>

                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>
            </div>

            <!-- Training Reminders -->
            <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Training Reminders</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Get reminded about optimal training times
                    </p>
                </div>
                <div>
                    <input type="hidden" name="preferences[notifications][training_reminders]" value="0">
                    <input type="checkbox" name="preferences[notifications][training_reminders]" value="1"
                        <?php echo e(old('preferences.notifications.training_reminders', $user->preferences['notifications']['training_reminders'] ?? true) ? 'checked' : ''); ?>

                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>
            </div>

            <!-- Race Alerts -->
            <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Race Alerts</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Notifications for upcoming races</p>
                </div>
                <div>
                    <input type="hidden" name="preferences[notifications][race_alerts]" value="0">
                    <input type="checkbox" name="preferences[notifications][race_alerts]" value="1"
                        <?php echo e(old('preferences.notifications.race_alerts', $user->preferences['notifications']['race_alerts'] ?? true) ? 'checked' : ''); ?>

                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>
            </div>

            <!-- AI Recommendations -->
            <div class="flex items-center justify-between py-3">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">AI Recommendations</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Get AI-powered strategy suggestions</p>
                </div>
                <div>
                    <input type="hidden" name="preferences[notifications][ai_recommendations]" value="0">
                    <input type="checkbox" name="preferences[notifications][ai_recommendations]" value="1"
                        <?php echo e(old('preferences.notifications.ai_recommendations', $user->preferences['notifications']['ai_recommendations'] ?? false) ? 'checked' : ''); ?>

                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/profile/partials/notifications-tab-content.blade.php ENDPATH**/ ?>