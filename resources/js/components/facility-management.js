// Facility management component (complex logic)
export default function facilityManagement() {
    return {
        facilities: [],
        selectedFacility: null,
        facilityLevels: {},
        loading: false,

        init() {
            this.loadFacilities();
        },

        async loadFacilities() {
            this.loading = true;
            try {
                const response = await fetch("/api/facilities", {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });
                const data = await response.json();

                if (data.success) {
                    this.facilities = data.data.facilities || [];
                    this.facilityLevels = data.data.levels || {};
                }
            } catch (error) {
                console.error("Error loading facilities:", error);
            } finally {
                this.loading = false;
            }
        },

        selectFacility(facilityId) {
            this.selectedFacility = facilityId;
        },

        async upgradeFacility(facilityId) {
            try {
                const response = await fetch(
                    `/api/facilities/${facilityId}/upgrade`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
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
                    this.facilityLevels[facilityId] = data.data.new_level;
                    this.loadFacilities(); // Reload to get updated data
                }
            } catch (error) {
                console.error("Error upgrading facility:", error);
                alert("Facility upgrade failed");
            }
        },

        getFacilityLevel(facilityId) {
            return this.facilityLevels[facilityId] || 1;
        },

        canUpgrade(facilityId) {
            const level = this.getFacilityLevel(facilityId);
            return level < 5; // Max level 5
        },
    };
}
