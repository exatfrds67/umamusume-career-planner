/**
 * Character Form Validation Module
 * Provides real-time client-side validation for character creation and editing forms
 * with accessibility-compliant error display and user feedback
 */

// Validation configuration
const VALIDATION_CONFIG = {
    stats: {
        min: 0,
        max: 1200,
        step: 1,
    },
    aptitudes: {
        validGrades: ["G", "F", "E", "D", "C", "B", "A", "S", "SS"],
    },
    name: {
        maxLength: 100,
        minLength: 1,
    },
    energy: {
        min: 0,
        max: 100,
    },
    turn: {
        min: 1,
        max: 78,
    },
};

// Stat grade calculation
function calculateStatGrade(value) {
    const numValue = parseInt(value) || 0;

    if (numValue >= 1200) return { grade: "SS", color: "purple" };
    if (numValue >= 1100) return { grade: "S", color: "red" };
    if (numValue >= 1000) return { grade: "A+", color: "orange" };
    if (numValue >= 900) return { grade: "A", color: "yellow" };
    if (numValue >= 800) return { grade: "B+", color: "green" };
    if (numValue >= 700) return { grade: "B", color: "green" };
    if (numValue >= 600) return { grade: "C+", color: "blue" };
    if (numValue >= 500) return { grade: "C", color: "blue" };
    if (numValue >= 400) return { grade: "D+", color: "indigo" };
    if (numValue >= 300) return { grade: "D", color: "indigo" };
    if (numValue >= 200) return { grade: "E+", color: "gray" };
    if (numValue >= 100) return { grade: "E", color: "gray" };
    if (numValue >= 50) return { grade: "F", color: "gray" };

    return { grade: "G+", color: "gray" };
}

// Update stat grade display
function updateStatGrade(stat, value) {
    const gradeElement = document.getElementById(`stat_${stat}_grade`);
    if (!gradeElement) return;

    const { grade, color } = calculateStatGrade(value);

    const colorClasses = {
        purple: "bg-purple-100 text-purple-700",
        red: "bg-red-100 text-red-700",
        orange: "bg-orange-100 text-orange-700",
        yellow: "bg-yellow-100 text-yellow-700",
        green: "bg-green-100 text-green-700",
        blue: "bg-blue-100 text-blue-700",
        indigo: "bg-indigo-100 text-indigo-700",
        gray: "bg-gray-100 text-gray-700",
    };

    gradeElement.textContent = grade;
    gradeElement.className = `mt-2 inline-block px-2 py-1 ${colorClasses[color]} rounded text-xs font-semibold`;
}

// Validate stat value
function validateStat(input) {
    const value = parseInt(input.value);
    const { min, max } = VALIDATION_CONFIG.stats;
    const errors = [];

    if (isNaN(value)) {
        errors.push("Stat value must be a number");
    } else if (value < min) {
        errors.push(`Stat value must be at least ${min}`);
    } else if (value > max) {
        errors.push(`Stat value cannot exceed ${max}`);
    }

    return errors;
}

// Validate aptitude grade
function validateAptitude(select) {
    const value = select.value;
    const errors = [];

    if (!value) {
        errors.push("Please select an aptitude grade");
    } else if (!VALIDATION_CONFIG.aptitudes.validGrades.includes(value)) {
        errors.push("Invalid aptitude grade selected");
    }

    return errors;
}

// Validate character name
function validateName(input) {
    const value = input.value.trim();
    const { minLength, maxLength } = VALIDATION_CONFIG.name;
    const errors = [];

    if (value.length < minLength) {
        errors.push("Character name is required");
    } else if (value.length > maxLength) {
        errors.push(`Character name must not exceed ${maxLength} characters`);
    }

    return errors;
}

// Display validation error
function displayError(input, errors) {
    // Remove existing error message
    const existingError =
        input.parentElement.querySelector(".validation-error");
    if (existingError) {
        existingError.remove();
    }

    // Remove error styling
    input.classList.remove(
        "border-red-500",
        "focus:ring-red-500",
        "focus:border-red-500"
    );
    input.setAttribute("aria-invalid", "false");

    if (errors.length > 0) {
        // Add error styling
        input.classList.add(
            "border-red-500",
            "focus:ring-red-500",
            "focus:border-red-500"
        );
        input.setAttribute("aria-invalid", "true");

        // Create error message element
        const errorElement = document.createElement("p");
        errorElement.className =
            "validation-error mt-1 text-sm text-red-600 flex items-start";
        errorElement.setAttribute("role", "alert");
        errorElement.setAttribute("aria-live", "polite");

        // Add error icon
        const icon = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "svg"
        );
        icon.setAttribute("class", "w-4 h-4 mr-1 mt-0.5 flex-shrink-0");
        icon.setAttribute("fill", "currentColor");
        icon.setAttribute("viewBox", "0 0 20 20");
        icon.setAttribute("aria-hidden", "true");

        const path = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "path"
        );
        path.setAttribute("fill-rule", "evenodd");
        path.setAttribute(
            "d",
            "M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
        );
        path.setAttribute("clip-rule", "evenodd");

        icon.appendChild(path);
        errorElement.appendChild(icon);

        // Add error text
        const textSpan = document.createElement("span");
        textSpan.textContent = errors[0]; // Show first error
        errorElement.appendChild(textSpan);

        // Insert error message after input or after help text
        const helpText = input.parentElement.querySelector(
            ".text-gray-500, .text-xs"
        );
        if (helpText && helpText.nextSibling) {
            helpText.parentElement.insertBefore(
                errorElement,
                helpText.nextSibling
            );
        } else {
            input.parentElement.appendChild(errorElement);
        }
    }
}

// Display success feedback
function displaySuccess(input) {
    // Remove error styling
    input.classList.remove(
        "border-red-500",
        "focus:ring-red-500",
        "focus:border-red-500"
    );
    input.classList.add(
        "border-green-500",
        "focus:ring-green-500",
        "focus:border-green-500"
    );
    input.setAttribute("aria-invalid", "false");

    // Remove success styling after a short delay
    setTimeout(() => {
        input.classList.remove(
            "border-green-500",
            "focus:ring-green-500",
            "focus:border-green-500"
        );
    }, 1500);
}

// Validate field on blur
function validateField(input) {
    let errors = [];

    if (input.name.startsWith("stats[")) {
        errors = validateStat(input);
        // Update grade display
        const statName = input.name.match(/stats\[(\w+)\]/)[1];
        updateStatGrade(statName, input.value);
    } else if (input.name.startsWith("aptitudes[")) {
        errors = validateAptitude(input);
    } else if (input.name === "name") {
        errors = validateName(input);
    } else if (input.name === "energy_level") {
        const value = parseInt(input.value);
        if (
            isNaN(value) ||
            value < VALIDATION_CONFIG.energy.min ||
            value > VALIDATION_CONFIG.energy.max
        ) {
            errors.push(
                `Energy level must be between ${VALIDATION_CONFIG.energy.min} and ${VALIDATION_CONFIG.energy.max}`
            );
        }
    } else if (input.name === "current_turn") {
        const value = parseInt(input.value);
        if (
            isNaN(value) ||
            value < VALIDATION_CONFIG.turn.min ||
            value > VALIDATION_CONFIG.turn.max
        ) {
            errors.push(
                `Turn must be between ${VALIDATION_CONFIG.turn.min} and ${VALIDATION_CONFIG.turn.max}`
            );
        }
    }

    displayError(input, errors);

    if (errors.length === 0 && input.value) {
        displaySuccess(input);
    }

    return errors.length === 0;
}

// Validate entire form
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll(
        'input[type="number"], input[type="text"], select[required]'
    );

    inputs.forEach((input) => {
        if (!validateField(input)) {
            isValid = false;
        }
    });

    return isValid;
}

// Show success notification
function showSuccessNotification(message) {
    const notification = document.createElement("div");
    notification.className =
        "fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg flex items-center z-50 animate-slide-in";
    notification.setAttribute("role", "alert");
    notification.setAttribute("aria-live", "polite");

    notification.innerHTML = `
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="font-medium">${message}</span>
        <button type="button" class="ml-4 text-green-700 hover:text-green-900 focus:outline-none" onclick="this.parentElement.remove()" aria-label="Close notification">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    `;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.classList.add("animate-slide-out");
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Initialize validation
function initializeValidation() {
    const form = document.getElementById("character-form");
    if (!form) return;

    // Initialize stat grades
    const stats = ["speed", "stamina", "power", "guts", "wit"];
    stats.forEach((stat) => {
        const input = document.getElementById(`stat_${stat}`);
        if (input) {
            updateStatGrade(stat, input.value);

            // Add real-time validation
            input.addEventListener("input", (e) => {
                updateStatGrade(stat, e.target.value);
            });

            input.addEventListener("blur", (e) => {
                validateField(e.target);
            });
        }
    });

    // Add validation to all form inputs
    const inputs = form.querySelectorAll(
        'input[type="number"], input[type="text"], select[required]'
    );
    inputs.forEach((input) => {
        input.addEventListener("blur", (e) => {
            validateField(e.target);
        });

        // Clear error on focus
        input.addEventListener("focus", (e) => {
            const existingError =
                e.target.parentElement.querySelector(".validation-error");
            if (existingError) {
                existingError.remove();
            }
        });
    });

    // Form submission validation
    form.addEventListener("submit", (e) => {
        if (!validateForm(form)) {
            e.preventDefault();

            // Focus first invalid field
            const firstInvalid = form.querySelector('[aria-invalid="true"]');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }

            // Show error summary
            const errorSummary = document.createElement("div");
            errorSummary.className =
                "fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg z-50";
            errorSummary.setAttribute("role", "alert");
            errorSummary.innerHTML = `
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Please correct the errors in the form</p>
                        <p class="text-sm mt-1">Check the highlighted fields for validation errors</p>
                    </div>
                    <button type="button" class="ml-4 text-red-700 hover:text-red-900" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            `;
            document.body.appendChild(errorSummary);

            setTimeout(() => errorSummary.remove(), 5000);

            return false;
        }

        // Disable submit button to prevent double submission
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            const originalText = submitButton.textContent;
            submitButton.textContent = submitButton.textContent.includes(
                "Create"
            )
                ? "Creating..."
                : "Saving...";

            // Re-enable after 3 seconds in case of error
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }, 3000);
        }
    });
}

// Initialize on DOM ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeValidation);
} else {
    initializeValidation();
}

// Export functions for use in inline scripts
window.updateStatGrade = updateStatGrade;
window.showSuccessNotification = showSuccessNotification;
