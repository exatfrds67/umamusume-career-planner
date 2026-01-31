/**
 * Character Edit Page
 * Handles stat validation and enforcement (max 1200)
 */

// Stat validation function
function enforceStatMax(input) {
    const max = 1200;
    const errorId = input.id.includes("goal_") ? null : input.id + "_error";
    const errorEl = errorId ? document.getElementById(errorId) : null;

    if (parseInt(input.value) > max) {
        input.value = max;
        if (errorEl) {
            errorEl.classList.remove("hidden");
            setTimeout(() => errorEl.classList.add("hidden"), 2000);
        }
    }

    if (parseInt(input.value) < 0) {
        input.value = 0;
    }
}

// Make function globally available for inline event handlers
window.enforceStatMax = enforceStatMax;

// Form submission validation
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("character-form");

    if (form) {
        form.addEventListener("submit", function (e) {
            const statInputs = document.querySelectorAll(".stat-input");
            let hasError = false;

            statInputs.forEach((input) => {
                if (parseInt(input.value) > 1200) {
                    input.value = 1200;
                    hasError = true;
                }
            });

            // Form still submits with corrected values
            // No need to prevent submission
        });
    }
});
