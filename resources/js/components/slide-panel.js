export default function slidePanel() {
    return {
        isOpen: false,
        closeOnBackdrop: true,
        position: "right",

        init() {
            // Watch for external open triggers
            this.$watch("isOpen", (value) => {
                if (!value) {
                    document.body.style.overflow = "auto";
                }
            });
        },

        open() {
            this.isOpen = true;
            // Prevent body scroll when panel open
            document.body.style.overflow = "hidden";
            // Focus on close button after animation
            this.$nextTick(() => {
                const closeBtn = this.$el.querySelector(
                    'button[aria-label="Close panel"]',
                );
                if (closeBtn) closeBtn.focus();
            });
            this.$dispatch("panel-opened");
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = "auto";
            this.$dispatch("panel-closed");
        },

        toggle() {
            this.isOpen ? this.close() : this.open();
        },

        handleBackdropClick() {
            if (this.closeOnBackdrop) {
                this.close();
            }
        },
    };
}
