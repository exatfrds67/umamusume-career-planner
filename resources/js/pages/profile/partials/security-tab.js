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
