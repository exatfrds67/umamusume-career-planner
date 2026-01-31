// Export page logic
document.addEventListener("DOMContentLoaded", function () {
    // State
    let selectedType = null;
    let selectedFormat = "json";

    // Elements
    const exportTypeBtns = document.querySelectorAll(".export-type-btn");
    const formatBtns = document.querySelectorAll(".format-btn");
    const exportTypeInput = document.getElementById("export-type");
    const exportFormatInput = document.getElementById("export-format");
    const previewBtn = document.getElementById("preview-btn");
    const exportBtn = document.getElementById("export-btn");
    const previewSection = document.getElementById("preview-section");
    const scheduleBtn = document.getElementById("schedule-btn");
    const scheduleModal = document.getElementById("schedule-modal");
    const cancelScheduleBtn = document.getElementById("cancel-schedule-btn");
    const scheduleForm = document.getElementById("schedule-form");
    const scheduleFrequency = document.getElementById("schedule-frequency");
    const dayOfWeekContainer = document.getElementById("day-of-week-container");
    const templateCards = document.querySelectorAll(".template-card");

    // Export type selection
    exportTypeBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            exportTypeBtns.forEach((b) => {
                b.classList.remove(
                    "border-primary-500",
                    "bg-primary-50",
                    "dark:bg-primary-900/20",
                );
                b.setAttribute("aria-pressed", "false");
            });
            this.classList.add(
                "border-primary-500",
                "bg-primary-50",
                "dark:bg-primary-900/20",
            );
            this.setAttribute("aria-pressed", "true");
            selectedType = this.dataset.type;
            exportTypeInput.value = selectedType;
            updateButtons();
        });
    });

    // Format selection
    formatBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            formatBtns.forEach((b) => {
                b.classList.remove(
                    "border-primary-500",
                    "bg-primary-50",
                    "dark:bg-primary-900/20",
                );
                b.setAttribute("aria-pressed", "false");
            });
            this.classList.add(
                "border-primary-500",
                "bg-primary-50",
                "dark:bg-primary-900/20",
            );
            this.setAttribute("aria-pressed", "true");
            selectedFormat = this.dataset.format;
            exportFormatInput.value = selectedFormat;
        });
    });

    // Select JSON by default
    document.querySelector('.format-btn[data-format="json"]')?.click();

    // Update button states
    function updateButtons() {
        const hasType = selectedType !== null;
        if (previewBtn) previewBtn.disabled = !hasType;
        if (exportBtn) exportBtn.disabled = !hasType;
    }

    // Get filters
    function getFilters() {
        const filters = {};
        const scenario = document.getElementById("filter-scenario")?.value;
        const status = document.getElementById("filter-status")?.value;
        const dateFrom = document.getElementById("filter-date-from")?.value;
        const dateTo = document.getElementById("filter-date-to")?.value;

        if (scenario) filters.scenario_type = scenario;
        if (status) filters.status = status;
        if (dateFrom) filters.date_from = dateFrom;
        if (dateTo) filters.date_to = dateTo;

        return filters;
    }

    // Preview button click
    previewBtn?.addEventListener("click", async function () {
        if (!selectedType) {
            alert("Please select an export type");
            return;
        }

        previewBtn.disabled = true;
        previewBtn.innerHTML =
            '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...';

        try {
            const response = await fetch("/api/export/preview", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: JSON.stringify({
                    export_type: selectedType,
                    filters: getFilters(),
                }),
            });

            const result = await response.json();
            if (result.success) {
                displayPreview(result.data);
                previewSection.classList.remove("hidden");
            } else {
                alert(result.message || "Preview failed");
            }
        } catch (error) {
            console.error("Preview error:", error);
            alert("An error occurred during preview");
        } finally {
            previewBtn.disabled = false;
            previewBtn.innerHTML =
                '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>Preview';
        }
    });

    function displayPreview(data) {
        console.log("Preview data:", data);
    }
});
