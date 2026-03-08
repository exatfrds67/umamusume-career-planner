export default function quickActions(items) {
    return {
        items: items || [],
        isOpen: false,

        toggle() {
            this.isOpen ? this.close() : this.open();
        },

        open() {
            this.isOpen = true;
            this.$dispatch("fab-opened");
        },

        close() {
            this.isOpen = false;
            this.$dispatch("fab-closed");
        },

        executeAction(item) {
            this.close();
            this.$dispatch("action-selected", {
                label: item.label,
                action: item.action,
                icon: item.icon,
            });

            // Execute action if method name provided
            if (
                window[item.action] &&
                typeof window[item.action] === "function"
            ) {
                window[item.action]();
            }
        },

        getItemColor(item) {
            const colors = {
                training:
                    "bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700",
                race: "bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700",
                skill: "bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700",
                plan: "bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700",
                settings:
                    "bg-neutral-500 hover:bg-neutral-600 dark:bg-neutral-600 dark:hover:bg-neutral-700",
            };

            return (
                item.color ||
                colors[item.type] ||
                "bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700"
            );
        },
    };
}
