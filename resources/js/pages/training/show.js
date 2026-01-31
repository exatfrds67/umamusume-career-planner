// Training show page logic
window.pageData = window.pageData || {};

window.pageData.trainingShow = {
    selectedFacility: null,
    trainingResults: null,

    init() {
        console.log("Training show page initialized");
    },

    selectFacility(facilityId) {
        this.selectedFacility = facilityId;
        console.log("Selected facility:", facilityId);
    },

    async executeTrain() {
        if (!this.selectedFacility) {
            alert("Please select a training facility");
            return;
        }

        try {
            const response = await fetch("/api/training/execute", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                },
                body: JSON.stringify({
                    facility_id: this.selectedFacility,
                }),
            });

            const data = await response.json();
            if (data.success) {
                this.trainingResults = data.data;
                this.displayResults();
            }
        } catch (error) {
            console.error("Error executing training:", error);
            alert("Training execution failed");
        }
    },

    displayResults() {
        console.log("Training results:", this.trainingResults);
    },
};
