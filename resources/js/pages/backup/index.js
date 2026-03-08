function initBackupPage() {
    const page = document.getElementById("backup-page");
    if (!page) {
        return;
    }

    const statusRegion = document.getElementById("backup-page-status");

    const getCsrfToken = () =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    const announce = (message, type = "info") => {
        if (statusRegion) {
            statusRegion.textContent = message;
        }

        window.dispatchEvent(
            new CustomEvent("toast", {
                detail: { message, type },
            }),
        );
    };

    const request = async (url, options = {}) => {
        const response = await fetch(url, {
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
                ...(options.headers || {}),
            },
            credentials: "same-origin",
            ...options,
        });

        const result = await response.json().catch(() => ({
            success: false,
            message: "Unexpected server response.",
        }));

        if (!response.ok || result.success === false) {
            const errorMessage =
                result.message || result.errors?.[0] || "Request failed.";
            throw new Error(errorMessage);
        }

        return result;
    };

    const withBusyState = async (button, label, callback) => {
        const originalLabel = button.innerHTML;
        button.disabled = true;
        button.classList.add("opacity-60", "cursor-not-allowed");
        button.textContent = label;

        try {
            await callback();
        } finally {
            button.disabled = false;
            button.classList.remove("opacity-60", "cursor-not-allowed");
            button.innerHTML = originalLabel;
        }
    };

    const createBackupButton = document.getElementById("create-backup-btn");
    if (createBackupButton) {
        createBackupButton.addEventListener("click", async () => {
            await withBusyState(
                createBackupButton,
                "Creating backup...",
                async () => {
                    const description =
                        window.prompt(
                            "Optional backup description:",
                            "Manual backup from Backup Manager",
                        ) || "Manual backup from Backup Manager";

                    const result = await request("/api/backup/create", {
                        method: "POST",
                        body: JSON.stringify({
                            type: "full",
                            compress: true,
                            encrypt: false,
                            description,
                        }),
                    });

                    announce(result.message || "Backup created successfully.", "success");
                    window.location.reload();
                },
            ).catch((error) => {
                announce(error.message, "error");
            });
        });
    }

    const scheduleBackupButton = document.getElementById("schedule-backup-btn");
    if (scheduleBackupButton) {
        scheduleBackupButton.addEventListener("click", async () => {
            await withBusyState(
                scheduleBackupButton,
                "Scheduling...",
                async () => {
                    const frequencyInput = (
                        window.prompt(
                            "Backup frequency: daily, weekly, or monthly",
                            "weekly",
                        ) || "weekly"
                    )
                        .trim()
                        .toLowerCase();

                    if (!["daily", "weekly", "monthly"].includes(frequencyInput)) {
                        announce("Schedule cancelled: invalid frequency.", "error");
                        return;
                    }

                    const timeInput = (
                        window.prompt("Backup time (HH:MM)", "02:00") || "02:00"
                    )
                        .trim();
                    const retentionInput = Number.parseInt(
                        window.prompt("Retention days", "30") || "30",
                        10,
                    );

                    const result = await request("/api/backup/schedule", {
                        method: "POST",
                        body: JSON.stringify({
                            frequency: frequencyInput,
                            time: timeInput,
                            type: "full",
                            compress: true,
                            encrypt: false,
                            retention_days: Number.isNaN(retentionInput)
                                ? 30
                                : retentionInput,
                        }),
                    });

                    announce(result.message || "Backup schedule created successfully.", "success");
                    window.location.reload();
                },
            ).catch((error) => {
                announce(error.message, "error");
            });
        });
    }

    const cleanupButton = document.getElementById("cleanup-btn");
    if (cleanupButton) {
        cleanupButton.addEventListener("click", async () => {
            const retentionInput = Number.parseInt(
                window.prompt("Delete backups older than how many days?", "30") || "30",
                10,
            );
            const retentionDays = Number.isNaN(retentionInput) ? 30 : retentionInput;

            if (
                !window.confirm(
                    `Delete backups older than ${retentionDays} day(s)? This cannot be undone.`,
                )
            ) {
                return;
            }

            await withBusyState(cleanupButton, "Cleaning up...", async () => {
                const result = await request("/api/backup/cleanup", {
                    method: "POST",
                    body: JSON.stringify({ retention_days: retentionDays }),
                });

                announce(result.message || "Backup cleanup completed.", "success");
                window.location.reload();
            }).catch((error) => {
                announce(error.message, "error");
            });
        });
    }

    page.querySelectorAll(".delete-btn").forEach((button) => {
        button.addEventListener("click", async () => {
            const backupId = button.dataset.backupId;
            if (!backupId) {
                return;
            }

            if (!window.confirm(`Delete backup ${backupId}? This cannot be undone.`)) {
                return;
            }

            await withBusyState(button, "Deleting...", async () => {
                const result = await request(`/api/backup/${backupId}`, {
                    method: "DELETE",
                });

                announce(result.message || "Backup deleted successfully.", "success");
                window.location.reload();
            }).catch((error) => {
                announce(error.message, "error");
            });
        });
    });

    page.querySelectorAll(".delete-schedule-btn").forEach((button) => {
        button.addEventListener("click", async () => {
            const scheduleId = button.dataset.scheduleId;
            if (!scheduleId) {
                return;
            }

            if (!window.confirm("Delete this backup schedule?")) {
                return;
            }

            await withBusyState(button, "Deleting...", async () => {
                const result = await request(`/api/backup/schedule/${scheduleId}`, {
                    method: "DELETE",
                });

                announce(result.message || "Schedule deleted successfully.", "success");
                window.location.reload();
            }).catch((error) => {
                announce(error.message, "error");
            });
        });
    });

    page.querySelectorAll(".restore-btn").forEach((button) => {
        button.addEventListener("click", async () => {
            const backupId = button.dataset.backupId;
            const encrypted = button.dataset.encrypted === "true";

            if (!backupId) {
                return;
            }

            await withBusyState(button, "Previewing...", async () => {
                let decryptionKey = null;
                if (encrypted) {
                    decryptionKey = window.prompt(
                        "This backup is encrypted. Enter the decryption key to preview the restore:",
                    );

                    if (!decryptionKey) {
                        announce("Restore cancelled: decryption key required.", "error");
                        return;
                    }
                }

                const preview = await request(`/api/backup/${backupId}/restore`, {
                    method: "POST",
                    body: JSON.stringify({
                        dry_run: true,
                        decryption_key: decryptionKey,
                    }),
                });

                const restoredCounts = Object.entries(
                    preview.data?.restored_counts || {},
                )
                    .filter(([, count]) => Number(count) > 0)
                    .map(([type, count]) => `${type}: ${count}`)
                    .join(", ");

                const summary = restoredCounts || "No changes were reported in the preview.";
                const shouldRestore = window.confirm(
                    `Restore preview for ${backupId} completed. ${summary}\n\nPress OK to perform the restore now.`,
                );

                if (!shouldRestore) {
                    announce("Restore preview completed. No data was changed.", "info");
                    return;
                }

                const result = await request(`/api/backup/${backupId}/restore`, {
                    method: "POST",
                    body: JSON.stringify({
                        dry_run: false,
                        decryption_key: decryptionKey,
                    }),
                });

                announce(result.message || "Restore completed successfully.", "success");
                window.location.reload();
            }).catch((error) => {
                announce(error.message, "error");
            });
        });
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initBackupPage);
} else {
    initBackupPage();
}