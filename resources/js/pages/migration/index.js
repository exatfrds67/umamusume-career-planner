// Migration page logic
document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;
    let currentBatchId = null;

    // Tab switching
    document.querySelectorAll(".tab-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const tabId = this.dataset.tab;

            // Update button styles
            document.querySelectorAll(".tab-btn").forEach((b) => {
                b.classList.remove(
                    "active",
                    "border-blue-500",
                    "text-blue-600",
                    "dark:text-blue-400",
                );
                b.classList.add("border-transparent", "text-gray-500");
            });
            this.classList.add(
                "active",
                "border-blue-500",
                "text-blue-600",
                "dark:text-blue-400",
            );
            this.classList.remove("border-transparent", "text-gray-500");

            // Show/hide content
            document.querySelectorAll(".tab-content").forEach((content) => {
                content.classList.add("hidden");
            });
            document.getElementById("tab-" + tabId)?.classList.remove("hidden");
        });
    });

    // Detect Format
    document
        .getElementById("detect-format-btn")
        ?.addEventListener("click", async function () {
            const content = document.getElementById("convert-content")?.value;
            if (!content?.trim()) {
                alert("Please enter content to detect format");
                return;
            }

            try {
                const response = await fetch("/api/migration/detect-format", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ content }),
                });

                const data = await response.json();
                if (data.success) {
                    document.getElementById("source-format").value =
                        data.data.format;
                    alert(
                        `Detected format: ${data.data.format} (${Math.round(data.data.confidence * 100)}% confidence)`,
                    );
                } else {
                    alert(
                        "Could not detect format: " +
                            (data.message || "Unknown error"),
                    );
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Error detecting format");
            }
        });

    // Convert Form
    document
        .getElementById("convert-form")
        ?.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = {
                content: document.getElementById("convert-content")?.value,
                source_format: document.getElementById("source-format")?.value,
                target_type: document.getElementById("target-type")?.value,
            };

            try {
                const response = await fetch("/api/migration/convert", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();
                const resultsDiv = document.getElementById("convert-results");
                const contentDiv = document.getElementById(
                    "convert-results-content",
                );

                resultsDiv?.classList.remove("hidden");

                if (data.success) {
                    contentDiv.innerHTML = `
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                        <p class="text-green-800 dark:text-green-200">Successfully converted ${data.data.statistics?.converted_records || 0} records</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-medium mb-2">Converted Data:</h4>
                        <pre class="text-sm overflow-auto max-h-96">${JSON.stringify(data.data.converted_data, null, 2)}</pre>
                    </div>
                `;
                } else {
                    contentDiv.innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p class="text-red-800 dark:text-red-200">Conversion failed: ${data.errors?.join(", ") || data.message}</p>
                    </div>
                `;
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Error converting data");
            }
        });
});
