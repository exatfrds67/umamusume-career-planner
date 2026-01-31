/**
 * OCR Results Page
 * Handles collapsible raw text display
 */

document.addEventListener("DOMContentLoaded", () => {
    // Toggle raw text visibility
    const toggleButton = document.getElementById("toggle-raw-text");
    const rawTextContent = document.getElementById("raw-text-content");

    if (toggleButton && rawTextContent) {
        toggleButton.addEventListener("click", function () {
            const isExpanded = this.getAttribute("aria-expanded") === "true";
            this.setAttribute("aria-expanded", !isExpanded);
            rawTextContent.classList.toggle("hidden");

            const icon = this.querySelector("svg");
            if (icon) {
                icon.classList.toggle("rotate-180");
            }
        });
    }
});
