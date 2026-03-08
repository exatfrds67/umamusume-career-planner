// Spirit Burst gauge component with modal
export default function spiritBurstGauge() {
    return {
        currentValue: 0,
        maxValue: 100,
        isActive: false,
        showModal: false,

        init() {
            this.updateGauge();
        },

        updateGauge() {
            const percentage = (this.currentValue / this.maxValue) * 100;
            const gaugeBar = this.$el.querySelector(".gauge-bar");
            if (gaugeBar) {
                gaugeBar.style.width = percentage + "%";
            }

            // Check if Spirit Burst is ready
            if (percentage >= 100 && !this.isActive) {
                this.isActive = true;
                this.$dispatch("spirit-burst-ready");
            }
        },

        setValue(value) {
            this.currentValue = Math.min(this.maxValue, Math.max(0, value));
            this.updateGauge();
        },

        addValue(amount) {
            this.setValue(this.currentValue + amount);
        },

        activate() {
            if (this.isActive) {
                this.showModal = true;
                this.$dispatch("spirit-burst-activated");
            }
        },

        closeModal() {
            this.showModal = false;
        },

        confirmActivation() {
            this.currentValue = 0;
            this.isActive = false;
            this.showModal = false;
            this.updateGauge();
            this.$dispatch("spirit-burst-confirmed");
        },

        getGaugeColor() {
            const percentage = (this.currentValue / this.maxValue) * 100;
            if (percentage >= 100)
                return "bg-gradient-to-r from-yellow-400 to-orange-500";
            if (percentage >= 75)
                return "bg-gradient-to-r from-green-400 to-yellow-400";
            if (percentage >= 50)
                return "bg-gradient-to-r from-blue-400 to-green-400";
            return "bg-gradient-to-r from-neutral-400 to-blue-400";
        },
    };
}
