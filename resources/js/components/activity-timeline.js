// Activity timeline component (standalone demo)
export default function activityTimeline() {
    return {
        activities: [],
        loading: false,
        page: 1,
        hasMore: true,

        init() {
            this.loadActivities();
        },

        async loadActivities() {
            this.loading = true;
            try {
                const response = await fetch(
                    `/api/activities?page=${this.page}`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                    },
                );
                const data = await response.json();

                if (data.success) {
                    this.activities = [
                        ...this.activities,
                        ...data.data.activities,
                    ];
                    this.hasMore = data.data.has_more;
                }
            } catch (error) {
                console.error("Error loading activities:", error);
            } finally {
                this.loading = false;
            }
        },

        loadMore() {
            if (!this.loading && this.hasMore) {
                this.page++;
                this.loadActivities();
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + " " + date.toLocaleTimeString();
        },
    };
}
