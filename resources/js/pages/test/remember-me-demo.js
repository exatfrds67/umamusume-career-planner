// Remember me demo page logic
window.pageData = window.pageData || {};

window.pageData.rememberMeDemo = {
    rememberMe: false,

    init() {
        console.log("Remember me demo initialized");
        this.loadRememberMeState();
    },

    loadRememberMeState() {
        const saved = localStorage.getItem("rememberMe");
        if (saved) {
            this.rememberMe = JSON.parse(saved);
        }
    },

    toggleRememberMe() {
        this.rememberMe = !this.rememberMe;
        localStorage.setItem("rememberMe", JSON.stringify(this.rememberMe));
        console.log("Remember me:", this.rememberMe);
    },

    clearRememberMe() {
        this.rememberMe = false;
        localStorage.removeItem("rememberMe");
        console.log("Remember me cleared");
    },
};

/**
 * Update the remember-me checkbox status display.
 * Previously inline in test/remember-me-demo.blade.php
 */
window.updateRememberStatus = function () {
    const checkbox = document.getElementById("demo-remember");
    const statusText = document.getElementById("remember-status");
    const formValue = document.getElementById("form-value");

    const isChecked = checkbox.checked;

    statusText.innerHTML = `Checkbox is currently: <span class="font-semibold">${isChecked ? "checked" : "unchecked"}</span>`;
    formValue.textContent = isChecked ? "true" : "false";
};

/**
 * Simulate a login attempt with the remember-me option.
 * Previously inline in test/remember-me-demo.blade.php
 */
window.simulateLogin = function () {
    const checkbox = document.getElementById("demo-remember");
    const isChecked = checkbox.checked;

    alert(
        `Demo Login Simulation:\n\nEmail: demo@example.com\nPassword: [hidden]\nRemember Me: ${isChecked ? "YES" : "NO"}\n\nIn a real login, this would ${isChecked ? "create a persistent session that lasts for years" : "create a session that expires when the browser closes"}.`,
    );
};
