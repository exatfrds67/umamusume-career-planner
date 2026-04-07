/**
 * OCR Upload Manager
 * Handles drag-and-drop file upload, progress tracking, and batch processing
 * for OCR screenshot processing.
 *
 * Routes previously injected via inline <script> in ocr/upload.blade.php
 */

const ROUTES = {
    status: "/api/ocr/status",
    upload: "/api/ocr/upload",
};

class OCRUploadManager {
    constructor() {
        this.uploadQueue = [];
        this.processingQueue = [];
        this.results = [];
        this.maxFileSize = 10 * 1024 * 1024; // 10MB default
        this.allowedFormats = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/webp",
        ];

        this.init();
    }

    init() {
        this.checkOCRStatus();
        this.setupEventListeners();
    }

    /**
     * Check OCR system status
     */
    async checkOCRStatus() {
        try {
            const response = await fetch(ROUTES.status, {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                },
            });

            const data = await response.json();

            if (data.success) {
                this.updateStatusCard(data.data);
                this.maxFileSize = data.data.max_file_size || this.maxFileSize;
                this.allowedFormats =
                    data.data.allowed_formats?.map((f) => `image/${f}`) ||
                    this.allowedFormats;

                // Update UI
                const maxFileSizeEl = document.getElementById("max-file-size");
                if (maxFileSizeEl) {
                    maxFileSizeEl.textContent = `${(this.maxFileSize / 1048576).toFixed(0)} MB`;
                }
            } else {
                this.showError(data.message || "OCR system is not available");
            }
        } catch (error) {
            console.error("Failed to check OCR status:", error);
            this.showError("Failed to connect to OCR system");
        }
    }

    /**
     * Update status card UI
     */
    updateStatusCard(status) {
        const statusCard = document.getElementById("ocr-status-card");
        const statusIndicator = document.getElementById("ocr-status-indicator");

        if (!statusCard || !statusIndicator) return;

        if (status.ocr_available && status.image_processing_available) {
            statusIndicator.innerHTML = `
                <div class="flex items-center text-green-600 dark:text-green-400">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">Online</span>
                </div>
            `;
            const statusText = statusCard.querySelector("p");
            if (statusText) {
                statusText.textContent =
                    "OCR system is ready to process screenshots";
            }
        } else {
            statusIndicator.innerHTML = `
                <div class="flex items-center text-red-600 dark:text-red-400">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">Offline</span>
                </div>
            `;
            const statusText = statusCard.querySelector("p");
            if (statusText) {
                statusText.textContent = "OCR system is currently unavailable";
            }
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        const dropZone = document.getElementById("drop-zone");
        const fileInput = document.getElementById("file-input");
        const browseButton = document.getElementById("browse-button");

        if (!dropZone || !fileInput) return;

        // Drag and drop
        dropZone.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropZone.classList.add(
                "border-primary-500",
                "bg-primary-50",
                "dark:bg-primary-900/20",
            );
        });

        dropZone.addEventListener("dragleave", (e) => {
            e.preventDefault();
            dropZone.classList.remove(
                "border-primary-500",
                "bg-primary-50",
                "dark:bg-primary-900/20",
            );
        });

        dropZone.addEventListener("drop", (e) => {
            e.preventDefault();
            dropZone.classList.remove(
                "border-primary-500",
                "bg-primary-50",
                "dark:bg-primary-900/20",
            );

            const files = Array.from(e.dataTransfer.files);
            this.handleFiles(files);
        });

        // Click to browse
        dropZone.addEventListener("click", () => fileInput.click());

        if (browseButton) {
            browseButton.addEventListener("click", (e) => {
                e.stopPropagation();
                fileInput.click();
            });
        }

        // File input change
        fileInput.addEventListener("change", (e) => {
            const files = Array.from(e.target.files);
            this.handleFiles(files);
            fileInput.value = ""; // Reset input
        });

        // Keyboard accessibility
        dropZone.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                fileInput.click();
            }
        });

        // Process all button
        const processAllButton = document.getElementById("process-all-button");
        if (processAllButton) {
            processAllButton.addEventListener("click", () => {
                this.processAllFiles();
            });
        }

        // Clear queue button
        const clearQueueButton = document.getElementById("clear-queue-button");
        if (clearQueueButton) {
            clearQueueButton.addEventListener("click", () => {
                this.clearQueue();
            });
        }
    }

    /**
     * Handle selected files
     */
    handleFiles(files) {
        const validFiles = files.filter((file) => this.validateFile(file));

        if (validFiles.length === 0) {
            this.showError("No valid files selected");
            return;
        }

        validFiles.forEach((file) => {
            const fileId = this.generateFileId();
            this.uploadQueue.push({
                id: fileId,
                file: file,
                status: "queued",
                progress: 0,
                result: null,
            });
            this.addFileToQueue(fileId, file);
        });

        this.showQueue();
        this.enableProcessButton();
    }

    /**
     * Validate file
     */
    validateFile(file) {
        // Check file type
        if (!this.allowedFormats.includes(file.type)) {
            this.showError(
                `Invalid file type: ${file.name}. Allowed: JPG, PNG, WEBP`,
            );
            return false;
        }

        // Check file size
        if (file.size > this.maxFileSize) {
            this.showError(
                `File too large: ${file.name}. Max size: ${(this.maxFileSize / 1048576).toFixed(0)}MB`,
            );
            return false;
        }

        return true;
    }

    /**
     * Add file to queue UI
     */
    addFileToQueue(fileId, file) {
        const queueItems = document.getElementById("queue-items");
        if (!queueItems) return;

        const fileSize = (file.size / 1024).toFixed(1);

        const itemHTML = `
            <div id="queue-item-${fileId}" class="bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center flex-1 min-w-0">
                        <svg class="w-8 h-8 text-neutral-400 dark:text-neutral-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">${file.name}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">${fileSize} KB</p>
                        </div>
                    </div>
                    <div class="flex items-center ml-4">
                        <span id="status-${fileId}" class="px-2 py-1 text-xs font-medium bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded">
                            Queued
                        </span>
                        <button type="button" 
                                data-file-id="${fileId}"
                                class="remove-queue-item ml-2 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 focus:outline-none"
                                aria-label="Remove from queue">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="progress-${fileId}" class="hidden">
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                        <div id="progress-bar-${fileId}" class="bg-primary-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="progress-text-${fileId}" class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">Processing...</p>
                </div>
            </div>
        `;

        queueItems.insertAdjacentHTML("beforeend", itemHTML);

        // Attach remove handler
        const removeButton = document.querySelector(
            `[data-file-id="${fileId}"]`,
        );
        if (removeButton) {
            removeButton.addEventListener("click", () =>
                this.removeFromQueue(fileId),
            );
        }
    }

    /**
     * Remove file from queue
     */
    removeFromQueue(fileId) {
        this.uploadQueue = this.uploadQueue.filter(
            (item) => item.id !== fileId,
        );
        document.getElementById(`queue-item-${fileId}`)?.remove();

        if (this.uploadQueue.length === 0) {
            this.hideQueue();
        }
    }

    /**
     * Process all files in queue
     */
    async processAllFiles() {
        const characterId = document.getElementById("character-select")?.value;

        if (!characterId) {
            this.showError("Please select a character first");
            return;
        }

        this.disableProcessButton();

        for (const item of this.uploadQueue) {
            if (item.status === "queued") {
                await this.processFile(item, characterId);
            }
        }

        this.showResults();
    }

    /**
     * Process single file
     */
    async processFile(item, characterId) {
        const { id, file } = item;

        // Update status
        this.updateFileStatus(id, "processing", "Processing...");
        this.showProgress(id);

        try {
            const formData = new FormData();
            formData.append("screenshot", file);
            formData.append("character_id", characterId);

            const response = await fetch(ROUTES.upload, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                this.updateFileStatus(id, "completed", "Completed");
                this.updateProgress(id, 100, "Processing complete");
                item.result = data.data;
                item.status = "completed";
                this.addResult(item, data.data);
            } else {
                this.updateFileStatus(id, "failed", "Failed");
                this.updateProgress(id, 0, data.message || "Processing failed");
                item.status = "failed";
            }
        } catch (error) {
            console.error("Upload failed:", error);
            this.updateFileStatus(id, "failed", "Error");
            this.updateProgress(id, 0, "Upload error");
            item.status = "failed";
        }
    }

    /**
     * Update file status in UI
     */
    updateFileStatus(fileId, status, text) {
        const statusEl = document.getElementById(`status-${fileId}`);
        if (!statusEl) return;

        const statusClasses = {
            queued: "bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300",
            processing:
                "bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300",
            completed:
                "bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300",
            failed: "bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300",
        };

        statusEl.className = `px-2 py-1 text-xs font-medium rounded ${statusClasses[status] || statusClasses.queued}`;
        statusEl.textContent = text;
    }

    /**
     * Show progress bar
     */
    showProgress(fileId) {
        const progressEl = document.getElementById(`progress-${fileId}`);
        if (progressEl) {
            progressEl.classList.remove("hidden");
        }
    }

    /**
     * Update progress bar
     */
    updateProgress(fileId, percent, text) {
        const progressBar = document.getElementById(`progress-bar-${fileId}`);
        const progressText = document.getElementById(`progress-text-${fileId}`);

        if (progressBar) {
            progressBar.style.width = `${percent}%`;
        }

        if (progressText) {
            progressText.textContent = text;
        }
    }

    /**
     * Add result to results section
     */
    addResult(item, data) {
        const resultsContainer = document.getElementById("results-container");
        if (!resultsContainer) return;

        const confidenceClass =
            data.confidence >= 0.7
                ? "text-green-600"
                : data.confidence >= 0.5
                  ? "text-yellow-600"
                  : "text-red-600";

        const resultHTML = `
            <div class="bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1">${item.file.name}</h4>
                        <div class="flex items-center gap-4 text-xs text-neutral-600 dark:text-neutral-400">
                            <span>Screen Type: <strong>${data.screen_type || "Unknown"}</strong></span>
                            <span class="${confidenceClass}">Confidence: <strong>${(data.confidence * 100).toFixed(1)}%</strong></span>
                        </div>
                    </div>
                    <a href="/ocr/results/${data.extraction_id}" 
                       class="px-3 py-1 text-sm bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                        View Details
                    </a>
                </div>
            </div>
        `;

        resultsContainer.insertAdjacentHTML("beforeend", resultHTML);
    }

    /**
     * Show/hide UI elements
     */
    showQueue() {
        document.getElementById("upload-queue")?.classList.remove("hidden");
    }

    hideQueue() {
        document.getElementById("upload-queue")?.classList.add("hidden");
    }

    showResults() {
        document
            .getElementById("processing-results")
            ?.classList.remove("hidden");
    }

    enableProcessButton() {
        const button = document.getElementById("process-all-button");
        if (button) {
            button.disabled = false;
        }
    }

    disableProcessButton() {
        const button = document.getElementById("process-all-button");
        if (button) {
            button.disabled = true;
        }
    }

    clearQueue() {
        if (confirm("Are you sure you want to clear the upload queue?")) {
            this.uploadQueue = [];
            const queueItems = document.getElementById("queue-items");
            if (queueItems) {
                queueItems.innerHTML = "";
            }
            this.hideQueue();
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        window.dispatchEvent(
            new CustomEvent("toast", {
                detail: {
                    type: "error",
                    message: message,
                },
            }),
        );
    }

    /**
     * Generate unique file ID
     */
    generateFileId() {
        return `file_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }
}

// Initialize on page load
let ocrManager;
document.addEventListener("DOMContentLoaded", () => {
    ocrManager = new OCRUploadManager();
});

// Export for potential external use
export { OCRUploadManager };
