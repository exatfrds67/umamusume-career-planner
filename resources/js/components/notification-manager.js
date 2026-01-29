/**
 * notificationManager - Alpine.js component for toast/notification display
 * 
 * Purpose: Centralized notification/toast queue management
 * 
 * Features:
 *   - Multiple notification types (success, error, warning, info)
 *   - Auto-dismiss with configurable duration
 *   - Queue management (max 5 visible)
 *   - Animation support
 *   - Accessibility (ARIA live regions)
 * 
 * Usage:
 *   <div x-data="notificationManager()">
 *       <div @notification-show="addNotification($event.detail)"></div>
 *   </div>
 * 
 *   // Dispatch notification
 *   window.dispatchEvent(new CustomEvent('notification-show', {
 *       detail: { message: 'Success!', type: 'success', duration: 3000 }
 *   }));
 */

export function notificationManager() {
    return {
        // State
        notifications: [],
        maxVisible: 5,

        // Add notification
        addNotification(config) {
            const {
                id = Math.random().toString(36).substr(2, 9),
                message = 'Notification',
                type = 'info', // success, error, warning, info
                duration = 3000,
                action = null, // {label, callback}
                dismissible = true,
            } = config;

            const notification = {
                id,
                message,
                type,
                duration,
                action,
                dismissible,
                isExiting: false,
            };

            // Add to queue
            this.notifications.unshift(notification);

            // Limit visible notifications
            if (this.notifications.length > this.maxVisible) {
                this.notifications.pop();
            }

            // Auto-dismiss
            if (duration > 0) {
                setTimeout(() => {
                    this.removeNotification(id);
                }, duration);
            }

            this.$dispatch('notification-added', { id });
        },

        // Remove notification
        removeNotification(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index >= 0) {
                this.notifications[index].isExiting = true;
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                    this.$dispatch('notification-removed', { id });
                }, 300);
            }
        },

        // Dismiss all
        dismissAll() {
            this.notifications.forEach(n => {
                this.removeNotification(n.id);
            });
        },

        // Execute notification action
        executeAction(notification) {
            if (notification.action?.callback) {
                notification.action.callback();
            }
            this.removeNotification(notification.id);
        },

        // Get notification icon
        getIcon(type) {
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ',
            };
            return icons[type] || icons.info;
        },

        // Get notification color
        getColor(type) {
            const colors = {
                success: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                error: 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                warning: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                info: 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
            };
            return colors[type] || colors.info;
        },

        // Get text color
        getTextColor(type) {
            const colors = {
                success: 'text-green-800 dark:text-green-200',
                error: 'text-red-800 dark:text-red-200',
                warning: 'text-yellow-800 dark:text-yellow-200',
                info: 'text-blue-800 dark:text-blue-200',
            };
            return colors[type] || colors.info;
        },

        // Get icon color
        getIconColor(type) {
            const colors = {
                success: 'text-green-600 dark:text-green-400',
                error: 'text-red-600 dark:text-red-400',
                warning: 'text-yellow-600 dark:text-yellow-400',
                info: 'text-blue-600 dark:text-blue-400',
            };
            return colors[type] || colors.info;
        },
    };
}
