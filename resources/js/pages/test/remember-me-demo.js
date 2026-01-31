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
