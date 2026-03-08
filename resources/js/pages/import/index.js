// Import page logic
document.addEventListener("DOMContentLoaded", function () {
    // State
    let selectedType = null;
    let previewData = null;
    let templates = {};

    // Elements
    const importTypeBtns = document.querySelectorAll(".import-type-btn");
    const importTypeInput = document.getElementById("import-type");
    const inputTabs = document.querySelectorAll(".input-tab");
    const pasteTab = document.getElementById("paste-tab");
    const fileTab = document.getElementById("file-tab");
    const pasteContent = document.getElementById("paste-content");
    const fileInput = document.getElementById("file-input");
    const fileInfo = document.getElementById("file-info");
    const fileName = document.getElementById("file-name");
    const clearFileBtn = document.getElementById("clear-file");
    const previewBtn = document.getElementById("preview-btn");
    const previewSection = document.getElementById("preview-section");
    const resultsSection = document.getElementById("results-section");
    const importBtn = document.getElementById("import-btn");
    const cancelBtn = document.getElementById("cancel-btn");
    const tryAgainBtn = document.getElementById("try-again-btn");
    const templateContent = document.getElementById("template-content");
    const templatePlaceholder = document.getElementById("template-placeholder");

    // Import type selection
    importTypeBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            importTypeBtns.forEach((b) => {
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
            importTypeInput.value = selectedType;
            updatePreviewButton();
            loadTemplates(selectedType);
        });
    });

    // Tab switching
    inputTabs.forEach((tab) => {
        tab.addEventListener("click", function () {
            inputTabs.forEach((t) => {
                t.classList.remove(
                    "border-primary-500",
                    "text-primary-600",
                    "dark:text-primary-400",
                );
                t.classList.add("border-transparent", "text-neutral-500");
                t.setAttribute("aria-selected", "false");
            });
            this.classList.remove("border-transparent", "text-neutral-500");
            this.classList.add(
                "border-primary-500",
                "text-primary-600",
                "dark:text-primary-400",
            );
            this.setAttribute("aria-selected", "true");

            const tabName = this.dataset.tab;
            if (tabName === "paste") {
                pasteTab.classList.remove("hidden");
                fileTab.classList.add("hidden");
            } else {
                pasteTab.classList.add("hidden");
                fileTab.classList.remove("hidden");
            }
            updatePreviewButton();
        });
    });

    // File input handling
    fileInput?.addEventListener("change", function () {
        if (this.files.length > 0) {
            const file = this.files[0];
            fileName.textContent = file.name;
            fileInfo.classList.remove("hidden");
            updatePreviewButton();
        }
    });

    // Clear file
    clearFileBtn?.addEventListener("click", function () {
        fileInput.value = "";
        fileInfo.classList.add("hidden");
        updatePreviewButton();
    });

    // Paste content change
    pasteContent?.addEventListener("input", function () {
        updatePreviewButton();
    });

    // Update preview button state
    function updatePreviewButton() {
        const hasType = selectedType !== null;
        const hasContent =
            pasteContent?.value.trim() !== "" || fileInput?.files.length > 0;
        if (previewBtn) previewBtn.disabled = !(hasType && hasContent);
    }

    // Preview button click
    previewBtn?.addEventListener("click", async function () {
        if (!selectedType) {
            alert("Please select an import type");
            return;
        }

        const formData = new FormData();
        formData.append("import_type", selectedType);

        if (fileInput?.files.length > 0) {
            formData.append("file", fileInput.files[0]);
        } else {
            formData.append("content", pasteContent.value);
        }

        previewBtn.disabled = true;
        previewBtn.innerHTML = `<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...`;

        try {
            const response = await fetch("/api/import/preview", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                previewData = result.data.preview;
                displayPreview(result.data);
                previewSection.classList.remove("hidden");
                resultsSection.classList.add("hidden");
            } else {
                alert(result.message || "Preview failed");
                if (result.errors) {
                    console.error("Preview errors:", result.errors);
                }
            }
        } catch (error) {
            console.error("Preview error:", error);
            alert("An error occurred during preview");
        } finally {
            previewBtn.disabled = false;
            previewBtn.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>Preview Import`;
        }
    });

    function displayPreview(data) {
        // Implementation for displaying preview data
        console.log("Preview data:", data);
    }

    function loadTemplates(type) {
        // Implementation for loading templates
        console.log("Loading templates for:", type);
    }
});
