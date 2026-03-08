// Activity timeline component (event-driven timeline)
export default function activityTimeline(initialEvents = []) {
    const pageSize = 10;

    return {
        events: initialEvents,
        displayedEvents: initialEvents.slice(0, pageSize),
        loading: false,

        init() {},

        loadMore() {
            const nextCount = this.displayedEvents.length + pageSize;
            this.displayedEvents = this.events.slice(0, nextCount);
        },

        getEventColor(type) {
            const colors = {
                race: "bg-red-400",
                skill: "bg-purple-400",
                milestone: "bg-blue-400",
                achievement: "bg-yellow-400",
            };
            return colors[type] || "bg-neutral-400";
        },

        getEventIcon(type) {
            const icons = {
                race: "🏇",
                skill: "⭐",
                milestone: "🏆",
                achievement: "🎖️",
            };
            return icons[type] || "📌";
        },

        getTimeAgo(timestamp) {
            if (!timestamp) {
                return "";
            }
            const now = new Date();
            const then = new Date(timestamp);
            const diffMs = now - then;
            const diffMins = Math.floor(diffMs / 60000);

            if (diffMins < 1) {
                return "just now";
            }
            if (diffMins < 60) {
                return `${diffMins}m ago`;
            }
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) {
                return `${diffHours}h ago`;
            }
            const diffDays = Math.floor(diffHours / 24);
            if (diffDays < 30) {
                return `${diffDays}d ago`;
            }
            return then.toLocaleDateString();
        },

        formatDate(timestamp) {
            if (!timestamp) {
                return "";
            }
            const date = new Date(timestamp);
            return date.toLocaleDateString() + " " + date.toLocaleTimeString();
        },

        formatEventType(type) {
            if (!type) {
                return "Event";
            }
            return type.charAt(0).toUpperCase() + type.slice(1);
        },

        getEventBadgeStyle(type) {
            const styles = {
                race: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300",
                skill: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300",
                milestone:
                    "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300",
                achievement:
                    "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300",
            };
            return (
                styles[type] ||
                "bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300"
            );
        },

        formatMetadata(key, value) {
            if (typeof value === "number") {
                return value.toLocaleString();
            }
            if (typeof value === "boolean") {
                return value ? "Yes" : "No";
            }
            return String(value);
        },
    };
}
