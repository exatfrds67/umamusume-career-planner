// Password visibility toggle functionality
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const eyeOpen = document.getElementById(inputId + "-eye-open");
    const eyeClosed = document.getElementById(inputId + "-eye-closed");

    if (!input || !eyeOpen || !eyeClosed) {
        console.error("Password toggle elements not found for:", inputId);
        return;
    }

    if (input.type === "password") {
        input.type = "text";
        eyeOpen.classList.add("hidden");
        eyeClosed.classList.remove("hidden");
        input.setAttribute("aria-describedby", inputId + "-visible");
    } else {
        input.type = "password";
        eyeOpen.classList.remove("hidden");
        eyeClosed.classList.add("hidden");
        input.removeAttribute("aria-describedby");
    }
}

// Add keyboard support for password toggle buttons
document.addEventListener("DOMContentLoaded", function () {
    const toggleButtons = document.querySelectorAll(
        '[onclick^="togglePasswordVisibility"]',
    );
    toggleButtons.forEach((button) => {
        button.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                button.click();
            }
        });
    });
});

// Export for global access
window.togglePasswordVisibility = togglePasswordVisibility;
