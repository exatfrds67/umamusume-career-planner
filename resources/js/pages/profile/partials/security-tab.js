// Profile security tab logic
window.pageData = window.pageData || {};

window.pageData.securityTab = {
    passwordStrength: 0,

    init() {
        console.log("Security tab initialized");
        this.setupPasswordStrengthCheck();
    },

    setupPasswordStrengthCheck() {
        const passwordInput = document.getElementById("new-password");
        if (passwordInput) {
            passwordInput.addEventListener("input", (e) => {
                this.checkPasswordStrength(e.target.value);
            });
        }
    },

    checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        this.passwordStrength = strength;
        this.updatePasswordStrengthUI(strength);
    },

    updatePasswordStrengthUI(strength) {
        const strengthBar = document.getElementById("password-strength-bar");
        if (strengthBar) {
            strengthBar.style.width = strength * 20 + "%";
            strengthBar.className =
                strength < 3
                    ? "bg-red-500"
                    : strength < 4
                      ? "bg-yellow-500"
                      : "bg-green-500";
        }
    },
};

/**
 * Alpine.js component for the delete account modal.
 * Handles modal visibility, confirmation input validation, and form submission.
 */
window.deleteAccountModal = function () {
    return {
        show: false,
        password: "",
        confirmation: "",
        confirmationError: false,

        get canSubmit() {
            const expectedName = this.$el.dataset.expectedName || "";
            return (
                this.password.length > 0 &&
                this.confirmation === expectedName
            );
        },

        openModal() {
            this.show = true;
            this.password = "";
            this.confirmation = "";
            this.confirmationError = false;
        },

        closeModal() {
            this.show = false;
            this.password = "";
            this.confirmation = "";
            this.confirmationError = false;
        },

        handleSubmit(event) {
            const expectedName = this.$el.dataset.expectedName || "";
            if (this.confirmation !== expectedName) {
                event.preventDefault();
                this.confirmationError = true;
                return;
            }
            this.confirmationError = false;
        },
    };
};
