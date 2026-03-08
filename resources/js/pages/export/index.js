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
    const typeHint = document.getElementById("export-type-hint");

    // CSRF token helper
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
    }

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
            if (exportTypeInput) exportTypeInput.value = selectedType;
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
            if (exportFormatInput) exportFormatInput.value = selectedFormat;
        });
    });

    // Select JSON by default
    document.querySelector('.format-btn[data-format="json"]')?.click();

    // Update button states + hint visibility
    function updateButtons() {
        const hasType = selectedType !== null;
        if (previewBtn) previewBtn.disabled = !hasType;
        if (exportBtn) exportBtn.disabled = !hasType;
        if (typeHint) typeHint.classList.toggle("hidden", hasType);
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

    // --- Preview ---
    previewBtn?.addEventListener("click", async function () {
        if (!selectedType) return;

        previewBtn.disabled = true;
        previewBtn.innerHTML =
            '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...';

        try {
            const response = await fetch("/api/export/preview", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken(),
                },
                body: JSON.stringify({
                    export_type: selectedType,
                    filters: getFilters(),
                }),
            });

            const result = await response.json();
            if (result.success) {
                displayPreview(result.data);
                previewSection?.classList.remove("hidden");
                previewSection?.scrollIntoView({ behavior: "smooth", block: "start" });
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

    // --- Export ---
    exportBtn?.addEventListener("click", async function () {
        if (!selectedType) return;

        exportBtn.disabled = true;
        exportBtn.innerHTML =
            '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...';

        try {
            const response = await fetch("/api/export/generate", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken(),
                },
                body: JSON.stringify({
                    export_type: selectedType,
                    format: selectedFormat,
                    filters: getFilters(),
                }),
            });

            const result = await response.json();
            if (result.success && result.data?.download_url) {
                window.location.href = result.data.download_url;
                setTimeout(loadExportHistory, 1500);
            } else {
                alert(result.message || "Export generation failed");
            }
        } catch (error) {
            console.error("Export error:", error);
            alert("An error occurred during export");
        } finally {
            exportBtn.disabled = false;
            exportBtn.innerHTML =
                '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>Export Data';
        }
    });

    // --- Schedule Modal ---
    scheduleBtn?.addEventListener("click", function () {
        scheduleModal?.classList.remove("hidden");
        document.body.style.overflow = "hidden";
        scheduleModal?.querySelector("select, input")?.focus();
    });

    function closeScheduleModal() {
        scheduleModal?.classList.add("hidden");
        document.body.style.overflow = "";
        scheduleBtn?.focus();
    }

    cancelScheduleBtn?.addEventListener("click", closeScheduleModal);

    scheduleModal?.addEventListener("click", function (e) {
        if (e.target === scheduleModal) closeScheduleModal();
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && !scheduleModal?.classList.contains("hidden")) {
            closeScheduleModal();
        }
    });

    scheduleFrequency?.addEventListener("change", function () {
        if (dayOfWeekContainer) {
            dayOfWeekContainer.classList.toggle("hidden", this.value !== "weekly");
        }
    });

    scheduleForm?.addEventListener("submit", async function (e) {
        e.preventDefault();
        const submitBtn = this.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = "Scheduling...";

        try {
            const data = Object.fromEntries(new FormData(this).entries());
            const response = await fetch("/api/export/schedule", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken(),
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();
            if (result.success) {
                closeScheduleModal();
                scheduleForm.reset();
                loadScheduledExports();
            } else {
                alert(result.message || "Failed to schedule export");
            }
        } catch (error) {
            console.error("Schedule error:", error);
            alert("An error occurred while scheduling");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    // --- Template Cards ---
    const templateTypeMap = {
        career_summary: "career",
        training_log: "training_session",
        character_profile: "character",
        skill_inventory: "skill",
        support_deck: "support_card",
        full_export: "full_backup",
    };

    function applyTemplate(templateKey) {
        const type = templateTypeMap[templateKey];
        if (!type) return;

        const btn = document.querySelector(`.export-type-btn[data-type="${type}"]`);
        if (btn) {
            btn.click();
            btn.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }

    templateCards.forEach((card) => {
        card.addEventListener("click", function () {
            applyTemplate(this.dataset.template);
        });
    });

    // --- Display Preview ---
    function displayPreview(data) {
        const headersRow = document.getElementById("preview-headers");
        const body = document.getElementById("preview-body");
        const totalEl = document.getElementById("total-records");
        const typeEl = document.getElementById("preview-type");
        const formatEl = document.getElementById("preview-format");
        const moreEl = document.getElementById("preview-more");
        const totalPreviewEl = document.getElementById("preview-total");

        if (totalEl) totalEl.textContent = data.total ?? 0;
        if (typeEl) typeEl.textContent = (selectedType ?? "-").replace(/_/g, " ");
        if (formatEl) formatEl.textContent = (selectedFormat ?? "-").toUpperCase();

        if (!headersRow || !body) return;

        const records = data.records ?? [];

        if (records.length === 0) {
            headersRow.innerHTML = "";
            body.innerHTML =
                '<tr><td colspan="100%" class="px-4 py-8 text-center text-neutral-500 dark:text-neutral-400">No records to preview</td></tr>';
            return;
        }

        const headers = Object.keys(records[0]);
        headersRow.innerHTML = headers
            .map(
                (h) =>
                    `<th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">${h.replace(/_/g, " ")}</th>`,
            )
            .join("");

        body.innerHTML = records
            .slice(0, 10)
            .map(
                (row) =>
                    `<tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">${headers.map((h) => `<td class="px-4 py-3 text-sm text-neutral-900 dark:text-white whitespace-nowrap">${row[h] ?? "-"}</td>`).join("")}</tr>`,
            )
            .join("");

        if (data.total > 10 && moreEl && totalPreviewEl) {
            totalPreviewEl.textContent = data.total;
            moreEl.classList.remove("hidden");
        }
    }

    // --- Export History ---
    async function loadExportHistory() {
        const container = document.getElementById("export-history");
        if (!container) return;

        try {
            const response = await fetch("/api/export/history", {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken(),
                },
            });

            if (!response.ok) throw new Error("Failed to fetch");

            const result = await response.json();
            const history = result.data?.history ?? [];

            if (history.length === 0) {
                container.innerHTML =
                    '<p class="text-neutral-500 dark:text-neutral-400 text-center py-4">No export history yet.</p>';
                return;
            }

            container.innerHTML = history
                .slice(0, 10)
                .map(
                    (h) => `
                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                    <div>
                        <span class="text-sm font-medium text-neutral-900 dark:text-white capitalize">${(h.export_type ?? "-").replace(/_/g, " ")}</span>
                        <span class="ml-2 px-1.5 py-0.5 text-xs bg-neutral-200 dark:bg-neutral-600 text-neutral-600 dark:text-neutral-300 rounded uppercase">${h.format ?? ""}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">${h.created_at ?? ""}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full ${h.status === "completed" ? "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400" : "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400"}">${h.status ?? "-"}</span>
                        ${h.download_url ? `<a href="${h.download_url}" class="text-primary-600 dark:text-primary-400 text-xs hover:underline">Download</a>` : ""}
                    </div>
                </div>`,
                )
                .join("");
        } catch {
            container.innerHTML =
                '<p class="text-neutral-500 dark:text-neutral-400 text-center py-4">Could not load export history.</p>';
        }
    }

    // --- Scheduled Exports ---
    async function loadScheduledExports() {
        const container = document.getElementById("scheduled-exports");
        if (!container) return;

        try {
            const response = await fetch("/api/export/schedules", {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken(),
                },
            });

            if (!response.ok) throw new Error("Failed to fetch");

            const result = await response.json();
            const schedules = result.data?.schedules ?? [];

            if (schedules.length === 0) {
                container.innerHTML =
                    '<p class="text-neutral-500 dark:text-neutral-400 text-center py-4">No scheduled exports.</p>';
                return;
            }

            container.innerHTML = schedules
                .map(
                    (s) => `
                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                    <div>
                        <span class="text-sm font-medium text-neutral-900 dark:text-white capitalize">${(s.export_type ?? "-").replace(/_/g, " ")} &bull; ${(s.format ?? "").toUpperCase()}</span>
                        <span class="ml-2 text-xs text-neutral-500 dark:text-neutral-400">${s.frequency ?? ""} at ${s.time ?? ""}</span>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 text-xs focus:outline-none focus:underline delete-schedule" data-id="${s.schedule_id}" aria-label="Remove scheduled export">Remove</button>
                </div>`,
                )
                .join("");

            container.querySelectorAll(".delete-schedule").forEach((btn) => {
                btn.addEventListener("click", async function () {
                    if (!confirm("Remove this scheduled export?")) return;
                    await fetch(`/api/export/schedule/${this.dataset.id}`, {
                        method: "DELETE",
                        headers: { "X-CSRF-TOKEN": csrfToken() },
                    });
                    loadScheduledExports();
                });
            });
        } catch {
            // fail silently — section shows stale content
        }
    }

    // Initial data loads
    loadExportHistory();
    loadScheduledExports();
});
